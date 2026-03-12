<?php

declare(strict_types=1);

use App\Models\Site;
use App\Models\User;

it('allows a user to create a site and view it', function (): void {
    $user = User::factory()->create([
        'email' => 'site-browser-create@example.com',
        'password' => 'password',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit('/dashboard/sites');

    $page->click('Create Site')
        ->fill('input#name', 'Browser Site')
        ->fill('input#slug', 'browser-site')
        ->fill('textarea#description', 'Managed from a browser test.')
        ->click('button[type="submit"]')
        ->assertPathBeginsWith('/dashboard/sites/browser-site')
        ->assertSee('Browser Site')
        ->assertSee('Components');
});

it('allows a user to edit a site', function (): void {
    $user = User::factory()->create([
        'email' => 'site-browser-edit@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'name' => 'Original Site',
        'slug' => 'original-site',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.edit', $site, false));

    $page->clear('input#name')
        ->fill('input#name', 'Updated Site')
        ->click('Save changes')
        ->assertSee('Saved.')
        ->assertValue('input#name', 'Updated Site');
});

it('allows a user to add a component and change its status', function (): void {
    $user = User::factory()->create([
        'email' => 'site-browser-component@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'name' => 'Component Site',
        'slug' => 'component-site',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.show', $site, false));

    $page->click('Add Component')
        ->fill('input#name', 'API')
        ->fill('textarea#description', 'Primary public API.')
        ->fill('input#group', 'Core Services')
        ->fill('input#sort_order', '1')
        ->click('button[type="submit"]')
        ->assertSee('API')
        ->assertSee('Operational')
        ->click('button[role="combobox"]')
        ->click('Major Outage')
        ->assertSee('Major Outage');
});
