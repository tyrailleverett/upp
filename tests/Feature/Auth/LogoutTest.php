<?php

declare(strict_types=1);

use App\Models\User;

it('can logout an authenticated user', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});

it('prevents guests from logging out', function (): void {
    $response = $this->post(route('logout'));

    $response->assertRedirect(route('login'));
});
