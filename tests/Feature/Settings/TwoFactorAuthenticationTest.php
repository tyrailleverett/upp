<?php

declare(strict_types=1);

use App\Models\User;

it('can render the security settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.security'));

    $response->assertOk();
    $response->assertInertia(
        fn ($page) => $page->component('dashboard/settings/security')
    );
});

it('can enable two-factor authentication', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('two-factor.enable'));

    $response->assertRedirect();
    expect($user->fresh()->two_factor_secret)->not->toBeNull();
});

it('can confirm two-factor authentication', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('two-factor.enable'));

    $code = generateValidTwoFactorCode($user->fresh());

    $response = $this->actingAs($user)->post(route('two-factor.confirm'), [
        'code' => $code,
    ]);

    $response->assertRedirect();
    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull();
});

it('rejects an invalid confirmation code', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('two-factor.enable'));

    $response = $this->actingAs($user)->post(route('two-factor.confirm'), [
        'code' => '000000',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors();
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('can retrieve recovery codes after confirmation', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('two-factor.enable'));

    $code = generateValidTwoFactorCode($user->fresh());
    $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $code]);

    $response = $this->actingAs($user)
        ->getJson(route('two-factor.recovery-codes'));

    $response->assertOk();
    $response->assertJsonCount(8);
});

it('can regenerate recovery codes', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('two-factor.enable'));

    $code = generateValidTwoFactorCode($user->fresh());
    $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $code]);

    $originalCodes = $this->actingAs($user)
        ->getJson(route('two-factor.recovery-codes'))
        ->json();

    $this->actingAs($user)->post(route('two-factor.regenerate-recovery-codes'));

    $newCodes = $this->actingAs($user)
        ->getJson(route('two-factor.recovery-codes'))
        ->json();

    expect($newCodes)->not->toEqual($originalCodes);
});

it('can disable two-factor authentication', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('two-factor.enable'));

    $code = generateValidTwoFactorCode($user->fresh());
    $this->actingAs($user)->post(route('two-factor.confirm'), ['code' => $code]);

    $response = $this->actingAs($user)->delete(route('two-factor.disable'));

    $response->assertRedirect();
    expect($user->fresh()->two_factor_secret)->toBeNull();
    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

it('prevents guests from accessing security settings', function (): void {
    $response = $this->get(route('settings.security'));

    $response->assertRedirect(route('login'));
});

/**
 * Generate a valid TOTP code for the user's two-factor secret.
 */
function generateValidTwoFactorCode(User $user): string
{
    $google2fa = app(PragmaRX\Google2FA\Google2FA::class);

    return $google2fa->getCurrentOtp(
        decrypt($user->two_factor_secret)
    );
}
