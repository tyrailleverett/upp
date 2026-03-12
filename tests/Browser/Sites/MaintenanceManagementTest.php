<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use App\Models\User;

it('allows scheduling a maintenance window', function (): void {
    $user = User::factory()->create([
        'email' => 'maintenance-browser-create@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'name' => 'Maintenance Site',
        'slug' => 'maintenance-site',
    ]);

    Component::factory()->for($site)->create(['name' => 'API Server']);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.maintenance.create', $site, false));

    $scheduledAt = now()->addDay()->format('Y-m-d\TH:i');
    $endsAt = now()->addDay()->addHours(2)->format('Y-m-d\TH:i');

    $page->assertNoJavaScriptErrors()
        ->assertSee('API Server')
        ->fill('input#title', 'Planned DB Upgrade')
        ->fill('input#scheduled_at', $scheduledAt)
        ->fill('input#ends_at', $endsAt)
        ->click('API Server')
        ->click('button[type="submit"]')
        ->assertSee('Planned DB Upgrade')
        ->assertSee('Scheduled');
});

it('allows completing a maintenance window early', function (): void {
    $user = User::factory()->create([
        'email' => 'maintenance-browser-complete@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'slug' => 'maintenance-complete-site',
    ]);

    $window = MaintenanceWindow::factory()->active()->for($site)->create([
        'title' => 'Active Maintenance',
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.maintenance.index', $site, false));

    $page->assertNoJavaScriptErrors()
        ->assertSee('Active Maintenance')
        ->assertSee('Active')
        ->click('Complete Now')
        ->assertSee('Completed');
});
