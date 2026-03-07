<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('can login with valid credentials', function (): void {
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

it('cannot login with invalid credentials', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

it('requires email and password to login', function (): void {
    $response = $this->post(route('login.store'), []);

    $response->assertSessionHasErrors(['email', 'password']);
});

it('throttles login attempts', function (): void {
    User::factory()->create(['email' => 'test@example.com']);

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);
    }

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(429);
});

it('prevents authenticated users from accessing login', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
});

it('can login with remember me', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertCookie(Auth::guard()->getRecallerName());
});
