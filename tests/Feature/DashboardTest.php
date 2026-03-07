<?php

declare(strict_types=1);

use App\Models\User;

it('redirects guests to the login page', function (): void {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users to the verification notice', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertRedirect(route('verification.notice'));
});

it('allows authenticated users to access the dashboard', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('dashboard/index'));
});
