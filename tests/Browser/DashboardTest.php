<?php

declare(strict_types=1);

use App\Models\User;

it('redirects unauthenticated users to the login page', function (): void {
    $page = visit('/dashboard');

    $page->assertSee('Welcome to')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Log in');
});

it('shows the dashboard for authenticated users', function (): void {
    $user = User::factory()->create([
        'email' => 'dashboard@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');
});
