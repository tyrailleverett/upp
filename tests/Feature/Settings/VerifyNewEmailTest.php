<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

it('verifies a new email and flashes success', function (): void {
    $user = User::factory()->create(['email' => 'old@example.com']);

    PendingUserEmail::create([
        'user_type' => $user->getMorphClass(),
        'user_id' => $user->id,
        'email' => 'new@example.com',
        'token' => 'test-token',
    ]);

    $url = URL::signedRoute('settings.email.verify', ['token' => 'test-token']);

    $response = $this->actingAs($user)->get($url);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('inertia.flash_data.success', 'Email verified successfully.');
    expect($user->fresh()->email)->toBe('new@example.com');
});

it('returns 404 for an invalid token', function (): void {
    $user = User::factory()->create();

    $url = URL::signedRoute('settings.email.verify', ['token' => 'invalid-token']);

    $response = $this->actingAs($user)->get($url);

    $response->assertNotFound();
});

it('prevents guests from verifying a new email', function (): void {
    $url = URL::signedRoute('settings.email.verify', ['token' => 'test-token']);

    $response = $this->get($url);

    $response->assertRedirect(route('login'));
});

it('rejects unsigned requests', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.email.verify', ['token' => 'test-token']));

    $response->assertForbidden();
});
