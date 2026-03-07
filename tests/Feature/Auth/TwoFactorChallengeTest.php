<?php

declare(strict_types=1);

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

it('completes login with valid totp code', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $twoFactor = enableTwoFactorForUser($user);

    $validCode = app(Google2FA::class)->getCurrentOtp($twoFactor['secret']);

    $response = $this->withSession([
        'login.id' => $user->id,
        'login.remember' => false,
    ])->post('/two-factor-challenge', [
        'code' => $validCode,
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('completes login with valid recovery code', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $twoFactor = enableTwoFactorForUser($user);

    $response = $this->withSession([
        'login.id' => $user->id,
        'login.remember' => false,
    ])->post('/two-factor-challenge', [
        'recovery_code' => $twoFactor['recovery_codes'][0],
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid totp code', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    enableTwoFactorForUser($user);

    $response = $this->withSession([
        'login.id' => $user->id,
        'login.remember' => false,
    ])->post('/two-factor-challenge', [
        'code' => '000000',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHasErrors(['code']);
    $this->assertGuest();
});

it('rejects invalid recovery code', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    enableTwoFactorForUser($user);

    $response = $this->withSession([
        'login.id' => $user->id,
        'login.remember' => false,
    ])->post('/two-factor-challenge', [
        'recovery_code' => 'invalid-recovery-code',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHasErrors(['recovery_code']);
    $this->assertGuest();
});

it('redirects if no login.id in session', function (): void {
    $response = $this->get('/two-factor-challenge');

    $response->assertRedirect(route('dashboard'));
});
