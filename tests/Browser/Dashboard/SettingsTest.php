<?php

declare(strict_types=1);

use App\Models\User;

it('loads the settings profile page without JavaScript errors', function (): void {
    $user = User::factory()->create([
        'email' => 'settings@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard/settings/profile');

    $page->assertNoJavaScriptErrors()
        ->assertSee('Settings')
        ->assertSee('Profile')
        ->assertSee('Profile Information');
});

it('navigates between settings tabs', function (): void {
    $user = User::factory()->create([
        'email' => 'tabs@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard/settings/profile');

    $page->assertSee('Profile Information')
        ->click('a[href="/dashboard/settings/security"]')
        ->assertSee('Change Password')
        ->click('a[href="/dashboard/settings/appearance"]')
        ->assertSee('Theme')
        ->click('a[href="/dashboard/settings/support"]')
        ->assertSee('Submit a Ticket');
});

it('loads the appearance page and shows theme options', function (): void {
    $user = User::factory()->create([
        'email' => 'appearance@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard/settings/appearance');

    $page->wait(1)
        ->assertNoJavaScriptErrors()
        ->assertSee('Light')
        ->assertSee('Dark')
        ->assertSee('System');
});

it('navigates to settings from the user menu', function (): void {
    $user = User::factory()->create([
        'email' => 'nav-user@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard');

    $page->click('button[data-slot="dropdown-menu-trigger"]')
        ->click('a[href="/dashboard/settings/profile"]')
        ->assertPathIs('/dashboard/settings/profile')
        ->assertSee('Profile Information');
});

it('shows only one success message after submitting a support ticket', function (): void {
    $user = User::factory()->create([
        'email' => 'support-success@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard/settings/support');

    $page->fill('title', 'Browser ticket')
        ->fill('description', 'Checking success message UX.')
        ->click('topic')
        ->wait(0.25)
        ->click('[data-slot="select-item"]:has-text("General")')
        ->pressAndWaitFor('Submit ticket')
        ->assertSee('Support ticket submitted.')
        ->assertDontSee('Ticket submitted.');
});
