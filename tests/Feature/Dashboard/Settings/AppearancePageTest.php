<?php

declare(strict_types=1);

use App\Models\User;

it('redirects guests to the login page', function (): void {
    $response = $this->get(route('settings.appearance'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users to the verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('settings.appearance'));

    $response->assertRedirect(route('verification.notice'));
});

it('renders the appearance settings page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.appearance'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('dashboard/settings/appearance'));
});

it('includes an inline theme initialization script to prevent flash of white', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('settings.appearance'));

    $response->assertOk();
    $response->assertSee("localStorage.getItem('theme')", false);
    $response->assertSee("document.documentElement.classList.add('dark')", false);
});
