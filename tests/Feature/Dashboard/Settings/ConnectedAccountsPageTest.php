<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;

it('redirects guests to the login page', function (): void {
    $response = $this->get(route('settings.connected-accounts'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users to the verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertRedirect(route('verification.notice'));
});

it('renders the connected accounts settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/connected-accounts')
        ->has('connectedAccounts')
        ->has('availableProviders')
        ->has('canDisconnect')
    );
});

it('includes connected social accounts in the page props', function (): void {
    $user = User::factory()->create();
    SocialAccount::factory()->count(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/connected-accounts')
        ->has('connectedAccounts', 2)
    );
});

it('lists available providers', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('availableProviders', 1)
        ->where('availableProviders.0.value', 'google')
        ->where('availableProviders.0.label', 'Google')
    );
});

it('sets canDisconnect to true when user has a password', function (): void {
    $user = User::factory()->create(['password' => 'secret-password']);
    SocialAccount::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('canDisconnect', true)
    );
});

it('sets canDisconnect to true when oauth user has multiple social accounts', function (): void {
    $user = User::factory()->create(['password' => null]);
    SocialAccount::factory()->count(2)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('canDisconnect', true)
    );
});

it('sets canDisconnect to false when oauth user has only one social account', function (): void {
    $user = User::factory()->create(['password' => null]);
    SocialAccount::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('canDisconnect', false)
    );
});

it('does not expose tokens in the response', function (): void {
    $user = User::factory()->create();
    SocialAccount::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('settings.connected-accounts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('connectedAccounts.0', fn ($account) => $account
            ->has('id')
            ->has('provider')
            ->has('email')
            ->has('created_at')
            ->missing('token')
            ->missing('refresh_token')
        )
    );
});
