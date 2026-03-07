<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;

it('can update the user name', function (): void {
    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => 'New Name',
        'email' => $user->email,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Profile updated successfully.');
    expect($user->fresh()->name)->toBe('New Name');
});

it('sends a verification email when the email changes', function (): void {
    Mail::fake();

    $user = User::factory()->create(['email' => 'old@example.com']);

    $response = $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => $user->name,
        'email' => 'new@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Profile updated successfully.');
    expect($user->fresh()->email)->toBe('old@example.com');
    $this->assertDatabaseHas('pending_user_emails', ['email' => 'new@example.com']);
    Mail::assertQueued(VerifyNewEmail::class);
});

it('does not send a verification email when the email is unchanged', function (): void {
    Mail::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => 'Updated Name',
        'email' => $user->email,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseMissing('pending_user_emails', ['email' => $user->email]);
    Mail::assertNotQueued(VerifyNewEmail::class);
});

it('validates required fields', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('settings.profile.update'), []);

    $response->assertSessionHasErrors(['name', 'email']);
});

it('validates unique email', function (): void {
    $existingUser = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => $user->name,
        'email' => 'taken@example.com',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('allows the user to keep their current email', function (): void {
    $user = User::factory()->create(['email' => 'mine@example.com']);

    $response = $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => $user->name,
        'email' => 'mine@example.com',
    ]);

    $response->assertRedirect();
    $response->assertSessionDoesntHaveErrors();
});

it('rejects disposable email addresses', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => $user->name,
        'email' => 'test@mailinator.com',
    ]);

    $response->assertSessionHasErrors(['email']);
});

it('prevents guests from updating a profile', function (): void {
    $response = $this->put(route('settings.profile.update'), [
        'name' => 'Hacker',
        'email' => 'hacker@example.com',
    ]);

    $response->assertRedirect(route('login'));
});
