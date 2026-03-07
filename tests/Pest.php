<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Browser');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Enable and confirm two-factor authentication for a user.
 *
 * @return array{secret: string, recovery_codes: list<string>}
 */
function enableTwoFactorForUser(App\Models\User $user): array
{
    $provider = app(Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
    $encrypter = Laravel\Fortify\Fortify::currentEncrypter();

    $secret = $provider->generateSecretKey();

    $recoveryCodes = collect(range(1, 8))
        ->map(fn () => Laravel\Fortify\RecoveryCode::generate())
        ->all();

    $user->forceFill([
        'two_factor_secret' => $encrypter->encrypt($secret),
        'two_factor_recovery_codes' => $encrypter->encrypt(json_encode($recoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ])->save();

    return ['secret' => $secret, 'recovery_codes' => $recoveryCodes];
}
