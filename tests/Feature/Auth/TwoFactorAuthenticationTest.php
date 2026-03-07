<?php

declare(strict_types=1);

use App\Models\User;

it('redirects to two-factor challenge when user has 2fa enabled', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    enableTwoFactorForUser($user);

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/two-factor-challenge');
    $this->assertGuest();
});

it('logs in normally when 2fa is not enabled', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('stores remember preference in session during 2fa redirect', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    enableTwoFactorForUser($user);

    $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    expect(session('login.id'))->toBe($user->id)
        ->and(session('login.remember'))->toBeTrue();
});

it('does not set login.id on failed login', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    expect(session()->has('login.id'))->toBeFalse();
});
