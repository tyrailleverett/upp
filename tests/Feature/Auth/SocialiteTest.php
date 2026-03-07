<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

function mockSocialiteUser(array $overrides = []): SocialiteUser
{
    $defaults = [
        'id' => '123456',
        'name' => 'Test User',
        'email' => 'test@example.com',
        'avatar' => 'https://example.com/avatar.jpg',
        'token' => 'test-token',
        'refreshToken' => 'test-refresh-token',
        'expiresIn' => 3600,
    ];

    $data = array_merge($defaults, $overrides);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn($data['id']);
    $socialiteUser->shouldReceive('getName')->andReturn($data['name']);
    $socialiteUser->shouldReceive('getEmail')->andReturn($data['email']);
    $socialiteUser->shouldReceive('getAvatar')->andReturn($data['avatar']);
    $socialiteUser->token = $data['token'];
    $socialiteUser->refreshToken = $data['refreshToken'];
    $socialiteUser->expiresIn = $data['expiresIn'];

    return $socialiteUser;
}

it('redirects to the socialite provider', function (): void {
    $response = $this->get(route('socialite.redirect', ['provider' => 'google']));

    $response->assertRedirect();
    expect($response->getTargetUrl())->toContain('accounts.google.com');
});

it('creates a new user from socialite callback', function (): void {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $response = $this->get(route('socialite.callback', ['provider' => 'google']));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    $this->assertDatabaseHas('social_accounts', [
        'provider' => 'google',
        'provider_id' => '123456',
    ]);
});

it('links an existing user by email on socialite callback', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $socialiteUser = mockSocialiteUser(['email' => 'test@example.com']);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $response = $this->get(route('socialite.callback', ['provider' => 'google']));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->socialAccounts)->toHaveCount(1);
});

it('logs in a returning socialite user and refreshes tokens', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);
    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_id' => '123456',
        'token' => 'old-token',
    ]);

    $socialiteUser = mockSocialiteUser(['token' => 'new-token']);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $response = $this->get(route('socialite.callback', ['provider' => 'google']));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
    expect($user->socialAccounts->first()->fresh()->token)->toBe('new-token');
});

it('links a socialite account to an already authenticated user email', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $socialiteUser = mockSocialiteUser(['email' => 'test@example.com']);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $response = $this->actingAs($user)->get(route('socialite.callback', ['provider' => 'google']));

    $response->assertRedirect(route('dashboard'));
    expect($user->refresh()->socialAccounts)->toHaveCount(1);
});

it('redirects an authenticated user back to the previous page after connecting', function (): void {
    $user = User::factory()->create(['email' => 'test@example.com']);
    $socialiteUser = mockSocialiteUser(['email' => 'test@example.com']);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $this->actingAs($user)
        ->from('/dashboard/settings/connected-accounts')
        ->get(route('socialite.redirect', ['provider' => 'google']));

    $response = $this->actingAs($user)
        ->get(route('socialite.callback', ['provider' => 'google']));

    $response->assertRedirect('/dashboard/settings/connected-accounts');
});

it('returns 404 for an invalid socialite provider', function (): void {
    $response = $this->get(route('socialite.redirect', ['provider' => 'invalid']));

    $response->assertNotFound();
});

it('marks a new socialite user as email verified', function (): void {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $this->get(route('socialite.callback', ['provider' => 'google']));

    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('creates a socialite user without a password', function (): void {
    $socialiteUser = mockSocialiteUser();

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn(Mockery::mock()->shouldReceive('user')->andReturn($socialiteUser)->getMock());

    $this->get(route('socialite.callback', ['provider' => 'google']));

    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasPassword())->toBeFalse();
});
