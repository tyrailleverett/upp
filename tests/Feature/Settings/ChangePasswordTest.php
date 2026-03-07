<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('can change the password', function (): void {
    $user = User::factory()->create(['password' => 'old-password']);

    $response = $this->actingAs($user)->put(route('settings.password.update'), [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Password changed successfully.');
    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('rejects an incorrect current password', function (): void {
    $user = User::factory()->create(['password' => 'correct-password']);

    $response = $this->actingAs($user)->put(route('settings.password.update'), [
        'current_password' => 'wrong-password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertSessionHasErrors(['current_password']);
});

it('requires password confirmation', function (): void {
    $user = User::factory()->create(['password' => 'old-password']);

    $response = $this->actingAs($user)->put(route('settings.password.update'), [
        'current_password' => 'old-password',
        'password' => 'new-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});

it('validates required fields', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('settings.password.update'), []);

    $response->assertSessionHasErrors(['current_password', 'password']);
});

it('forbids OAuth-only users from changing password', function (): void {
    $user = User::factory()->create(['password' => null]);

    $response = $this->actingAs($user)->put(route('settings.password.update'), [
        'current_password' => 'anything',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertForbidden();
});

it('prevents guests from changing password', function (): void {
    $response = $this->put(route('settings.password.update'), [
        'current_password' => 'password',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('login'));
});
