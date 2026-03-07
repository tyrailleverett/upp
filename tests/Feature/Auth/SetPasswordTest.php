<?php

declare(strict_types=1);

use App\Models\User;

it('allows a social-only user to set a password', function (): void {
    $user = User::factory()->create(['password' => null]);

    $response = $this->actingAs($user)->post(route('password.set'), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Password set successfully.');
    expect($user->fresh()->hasPassword())->toBeTrue();
});

it('prevents a user with a password from setting a new one', function (): void {
    $user = User::factory()->create(['password' => 'existing-password']);

    $response = $this->actingAs($user)->post(route('password.set'), [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertForbidden();
});

it('validates the password confirmation when setting a password', function (): void {
    $user = User::factory()->create(['password' => null]);

    $response = $this->actingAs($user)->post(route('password.set'), [
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('requires a password to set a password', function (): void {
    $user = User::factory()->create(['password' => null]);

    $response = $this->actingAs($user)->post(route('password.set'), []);

    $response->assertSessionHasErrors(['password']);
});
