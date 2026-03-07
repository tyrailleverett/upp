<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;

it('allows a user with a password to disconnect a social account', function (): void {
    $user = User::factory()->create(['password' => 'secret-password']);
    $socialAccount = SocialAccount::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('settings.social-accounts.destroy', $socialAccount));

    $response->assertRedirect();
    $response->assertSessionHas('inertia.flash_data.success', 'Account disconnected successfully.');
    $this->assertDatabaseMissing('social_accounts', ['id' => $socialAccount->id]);
});

it('allows an oauth user with multiple accounts to disconnect one', function (): void {
    $user = User::factory()->create(['password' => null]);
    $socialAccount1 = SocialAccount::factory()->create(['user_id' => $user->id]);
    $socialAccount2 = SocialAccount::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('settings.social-accounts.destroy', $socialAccount1));

    $response->assertRedirect();
    $this->assertDatabaseMissing('social_accounts', ['id' => $socialAccount1->id]);
    $this->assertDatabaseHas('social_accounts', ['id' => $socialAccount2->id]);
});

it('blocks disconnecting when it is the last social account and no password', function (): void {
    $user = User::factory()->create(['password' => null]);
    $socialAccount = SocialAccount::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete(route('settings.social-accounts.destroy', $socialAccount));

    $response->assertForbidden();
    $this->assertDatabaseHas('social_accounts', ['id' => $socialAccount->id]);
});

it('blocks disconnecting another user social account', function (): void {
    $user = User::factory()->create(['password' => 'secret-password']);
    $otherUser = User::factory()->create();
    $socialAccount = SocialAccount::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->delete(route('settings.social-accounts.destroy', $socialAccount));

    $response->assertForbidden();
    $this->assertDatabaseHas('social_accounts', ['id' => $socialAccount->id]);
});

it('redirects guests to the login page', function (): void {
    $socialAccount = SocialAccount::factory()->create();

    $response = $this->delete(route('settings.social-accounts.destroy', $socialAccount));

    $response->assertRedirect(route('login'));
});

it('returns 404 for a nonexistent social account', function (): void {
    $user = User::factory()->create(['password' => 'secret-password']);

    $response = $this->actingAs($user)->delete(route('settings.social-accounts.destroy', 99999));

    $response->assertNotFound();
});
