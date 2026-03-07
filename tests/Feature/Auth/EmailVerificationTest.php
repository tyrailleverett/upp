<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('redirects verified users from the verification notice', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('verification.notice'));

    $response->assertRedirect(route('dashboard'));
});

it('can verify an email address', function (): void {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertRedirect(route('dashboard'));
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects invalid verification hash', function (): void {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => 'invalid-hash'],
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    $response->assertForbidden();
    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('can resend the verification email', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Verification link sent.');
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('throttles verification email resends', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    for ($i = 0; $i < 6; $i++) {
        $this->actingAs($user)->post(route('verification.send'));
    }

    $response = $this->actingAs($user)->post(route('verification.send'));

    $response->assertStatus(429);
});
