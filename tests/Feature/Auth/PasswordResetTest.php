<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('can send a password reset link', function (): void {
    Notification::fake();

    $user = User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post(route('password.email'), [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success');
    Notification::assertSentTo($user, ResetPassword::class);
});

it('returns an error when sending a reset link to a non-existent email', function (): void {
    $response = $this->post(route('password.email'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['email']);
});

it('can reset a password with a valid token', function (): void {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create(['email' => 'test@example.com']);
    $token = Password::createToken($user);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('inertia.flash_data.success');
    Event::assertDispatched(PasswordReset::class);
});

it('cannot reset a password with an invalid token', function (): void {
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => 'test@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['email']);
});

it('validates the password reset request', function (): void {
    $response = $this->post(route('password.update'), []);

    $response->assertSessionHasErrors(['token', 'email', 'password']);
});

it('throttles password reset link requests', function (): void {
    User::factory()->create(['email' => 'test@example.com']);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('password.email'), [
            'email' => 'test@example.com',
        ]);
    }

    $response = $this->post(route('password.email'), [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(429);
});

it('can view the forgot password page', function (): void {
    $response = $this->get(route('password.request'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('auth/forgot-password'));
});

it('can view the reset password page', function (): void {
    $response = $this->get(route('password.reset', ['token' => 'test-token']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('auth/reset-password')
        ->where('token', 'test-token')
    );
});
