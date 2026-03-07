<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('can register a new user', function (): void {
    Event::fake([Registered::class]);

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

it('dispatches the registered event', function (): void {
    Event::fake([Registered::class]);

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    Event::assertDispatched(Registered::class);
});

it('requires a name, email, and password to register', function (): void {
    $response = $this->post(route('register.store'), []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
});

it('requires a unique email to register', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'taken@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('rejects disposable email addresses', function (): void {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@mailinator.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('prevents authenticated users from registering', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'new@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
});
