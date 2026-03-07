<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

it('can resend pending email verification', function (): void {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    PendingUserEmail::create([
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => 'test-token',
    ]);

    $response = $this->actingAs($user)->post(route('settings.email.resend'));

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Verification email resent.');
    Mail::assertQueued(VerifyNewEmail::class);
});

it('returns 404 when resending with no pending email', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('settings.email.resend'));

    $response->assertNotFound();
});

it('prevents guests from resending verification email', function (): void {
    $response = $this->post(route('settings.email.resend'));

    $response->assertRedirect(route('login'));
});

it('rate limits the resend endpoint', function (): void {
    $user = User::factory()->create(['email' => 'old@example.com']);

    for ($i = 0; $i < 6; $i++) {
        PendingUserEmail::create([
            'user_type' => $user->getMorphClass(),
            'user_id' => $user->id,
            'email' => 'new@example.com',
            'token' => "test-token-{$i}",
        ]);

        $this->actingAs($user)->post(route('settings.email.resend'));
    }

    PendingUserEmail::create([
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => 'test-token-final',
    ]);

    $response = $this->actingAs($user)->post(route('settings.email.resend'));

    $response->assertTooManyRequests();
});

it('can cancel a pending email change', function (): void {
    $user = User::factory()->create(['email' => 'old@example.com']);

    PendingUserEmail::create([
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => 'test-token',
    ]);

    $response = $this->actingAs($user)->delete(route('settings.email.destroy'));

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Pending email change cancelled.');
    $this->assertDatabaseMissing('pending_user_emails', ['user_id' => $user->id]);
    expect($user->fresh()->email)->toBe('old@example.com');
});

it('handles cancellation with no pending email gracefully', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('settings.email.destroy'));

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Pending email change cancelled.');
});

it('prevents guests from cancelling pending email', function (): void {
    $response = $this->delete(route('settings.email.destroy'));

    $response->assertRedirect(route('login'));
});
