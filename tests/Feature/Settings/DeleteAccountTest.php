<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;

it('can soft delete an account with email confirmation', function (): void {
    $user = User::factory()->create(['password' => 'secret-password']);

    $response = $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => $user->email,
        'confirm' => true,
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('inertia.flash_data.success', 'Account deleted successfully.');
    $this->assertGuest();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

it('allows OAuth-only users to delete with email confirmation', function (): void {
    $user = User::factory()->create(['password' => null, 'email' => 'oauth@example.com']);

    $response = $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => 'oauth@example.com',
        'confirm' => true,
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('inertia.flash_data.success', 'Account deleted successfully.');
    $this->assertGuest();
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

it('rejects an incorrect email', function (): void {
    $user = User::factory()->create(['email' => 'real@example.com']);

    $response = $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => 'wrong@example.com',
        'confirm' => true,
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertAuthenticated();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});

it('requires the confirmation checkbox', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => $user->email,
    ]);

    $response->assertSessionHasErrors(['confirm']);
    $this->assertAuthenticated();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});

it('rejects deletion when confirmation checkbox is false', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => $user->email,
        'confirm' => false,
    ]);

    $response->assertSessionHasErrors(['confirm']);
    $this->assertAuthenticated();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});

it('requires email to delete account', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'confirm' => true,
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertAuthenticated();
    $this->assertNotSoftDeleted('users', ['id' => $user->id]);
});

it('allows re-registration with a soft-deleted email', function (): void {
    $user = User::factory()->create([
        'email' => 'reuse@example.com',
        'password' => 'secret-password',
    ]);

    $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => 'reuse@example.com',
        'confirm' => true,
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'New User',
        'email' => 'reuse@example.com',
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertDatabaseCount('users', 2);
});

it('preserves social accounts on soft delete', function (): void {
    $user = User::factory()->create(['password' => null]);
    SocialAccount::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->delete(route('settings.account.destroy'), [
        'email' => $user->email,
        'confirm' => true,
    ]);

    $this->assertSoftDeleted('users', ['id' => $user->id]);
    $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id]);
});

it('prevents guests from deleting an account', function (): void {
    $response = $this->delete(route('settings.account.destroy'));

    $response->assertRedirect(route('login'));
});
