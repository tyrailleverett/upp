<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;

it('can enable 2fa', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/user/two-factor-authentication');

    $response->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_secret)->not->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

it('can confirm 2fa with valid code', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/user/two-factor-authentication');

    $user->refresh();

    $decryptedSecret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
    $validCode = app(Google2FA::class)->getCurrentOtp($decryptedSecret);

    $response = $this->actingAs($user)->postJson('/user/confirmed-two-factor-authentication', [
        'code' => $validCode,
    ]);

    $response->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_confirmed_at)->not->toBeNull();
});

it('can disable 2fa', function (): void {
    $user = User::factory()->create();

    enableTwoFactorForUser($user);

    $response = $this->actingAs($user)->deleteJson('/user/two-factor-authentication');

    $response->assertSuccessful();

    $user->refresh();

    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_confirmed_at)->toBeNull();
});

it('can retrieve recovery codes', function (): void {
    $user = User::factory()->create();

    enableTwoFactorForUser($user);

    $response = $this->actingAs($user)->getJson('/user/two-factor-recovery-codes');

    $response->assertSuccessful();
    expect($response->json())->toHaveCount(8);
});

it('can retrieve qr code', function (): void {
    $user = User::factory()->create();

    enableTwoFactorForUser($user);

    $response = $this->actingAs($user)->getJson('/user/two-factor-qr-code');

    $response->assertSuccessful();
    expect($response->json('svg'))->toBeString();
});

it('can retrieve setup key', function (): void {
    $user = User::factory()->create();

    $twoFactor = enableTwoFactorForUser($user);

    $response = $this->actingAs($user)->getJson('/user/two-factor-secret-key');

    $response->assertSuccessful();
    expect($response->json('secretKey'))->toBe($twoFactor['secret']);
});

it('can regenerate recovery codes', function (): void {
    $user = User::factory()->create();

    $original = enableTwoFactorForUser($user);

    $response = $this->actingAs($user)->postJson('/user/two-factor-recovery-codes');

    $response->assertSuccessful();

    $newCodes = $this->actingAs($user)->getJson('/user/two-factor-recovery-codes');

    expect($newCodes->json())->toHaveCount(8)
        ->and($newCodes->json())->not->toBe($original['recovery_codes']);
});

it('requires auth to manage 2fa', function (): void {
    $this->post('/user/two-factor-authentication')->assertRedirect(route('login'));
    $this->delete('/user/two-factor-authentication')->assertRedirect(route('login'));
    $this->getJson('/user/two-factor-qr-code')->assertUnauthorized();
    $this->getJson('/user/two-factor-recovery-codes')->assertUnauthorized();
});
