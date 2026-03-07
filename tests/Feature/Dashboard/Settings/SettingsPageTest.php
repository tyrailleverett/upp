<?php

declare(strict_types=1);

use App\Models\User;

it('redirects guests to the login page', function (): void {
    $response = $this->get(route('settings'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users to the verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('settings'));

    $response->assertRedirect(route('verification.notice'));
});

it('redirects to the profile settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings'));

    $response->assertRedirect(route('settings.profile'));
});
