<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Fortify;

it('allows users without 2fa to access dashboard', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();
});

it('allows users with confirmed 2fa to access dashboard', function (): void {
    $user = User::factory()->create();

    enableTwoFactorForUser($user);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->not->toBeNull();
});

it('cancels unconfirmed 2fa when navigating to dashboard', function (): void {
    $user = User::factory()->create();

    enableUnconfirmedTwoFactor($user);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();
});

it('cancels unconfirmed 2fa when navigating to settings pages', function (string $route): void {
    $user = User::factory()->create();

    enableUnconfirmedTwoFactor($user);

    $this->actingAs($user)
        ->get(route($route))
        ->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();
})->with([
    'profile' => 'settings.profile',
    'appearance' => 'settings.appearance',
    'support' => 'settings.support',
]);

it('preserves unconfirmed 2fa on security settings page', function (): void {
    $user = User::factory()->create();

    enableUnconfirmedTwoFactor($user);

    $this->actingAs($user)
        ->get(route('settings.security'))
        ->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull();
});

it('does not affect guest users', function (): void {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

/**
 * Enable two-factor authentication without confirming it.
 */
function enableUnconfirmedTwoFactor(User $user): void
{
    $provider = app(Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
    $encrypter = Fortify::currentEncrypter();

    $user->forceFill([
        'two_factor_secret' => $encrypter->encrypt($provider->generateSecretKey()),
        'two_factor_recovery_codes' => $encrypter->encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();
}
