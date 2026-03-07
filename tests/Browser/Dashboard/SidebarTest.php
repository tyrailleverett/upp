<?php

declare(strict_types=1);

use App\Models\User;

it('persists the sidebar collapsed state across page refreshes', function (): void {
    $user = User::factory()->create([
        'email' => 'sidebar@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard');

    $page->assertDataAttribute('[data-slot="sidebar"]:not([data-mobile])', 'state', 'expanded')
        ->click('[data-slot="sidebar-trigger"]')
        ->wait(0.5)
        ->assertDataAttribute('[data-slot="sidebar"]:not([data-mobile])', 'state', 'collapsed');

    $page->script('window.location.reload()');
    $page->waitFor('[data-slot="sidebar"]:not([data-mobile])');

    $page->assertDataAttribute('[data-slot="sidebar"]:not([data-mobile])', 'state', 'collapsed');
});

it('persists the sidebar expanded state across page refreshes', function (): void {
    $user = User::factory()->create([
        'email' => 'sidebar-expand@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard');

    $page->assertDataAttribute('[data-slot="sidebar"]:not([data-mobile])', 'state', 'expanded');

    // Collapse, then re-expand
    $page->click('[data-slot="sidebar-trigger"]')
        ->wait(0.5)
        ->click('[data-slot="sidebar-trigger"]')
        ->wait(0.5)
        ->assertDataAttribute('[data-slot="sidebar"]:not([data-mobile])', 'state', 'expanded');

    $page->script('window.location.reload()');
    $page->waitFor('[data-slot="sidebar"]:not([data-mobile])');

    $page->assertDataAttribute('[data-slot="sidebar"]:not([data-mobile])', 'state', 'expanded');
});
