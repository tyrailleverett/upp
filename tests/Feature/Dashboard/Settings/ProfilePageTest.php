<?php

declare(strict_types=1);

use App\Models\User;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

it('redirects guests to the login page', function (): void {
    $response = $this->get(route('settings.profile'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users to the verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('settings.profile'));

    $response->assertRedirect(route('verification.notice'));
});

it('renders the profile settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.profile'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/profile')
        ->where('pendingEmail', null)
    );
});

it('includes the pending email when one exists', function (): void {
    $user = User::factory()->create();

    PendingUserEmail::create([
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->id,
        'email' => 'newemail@example.com',
        'token' => 'test-token',
    ]);

    $response = $this->actingAs($user)->get(route('settings.profile'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('dashboard/settings/profile')
        ->where('pendingEmail', 'newemail@example.com')
    );
});
