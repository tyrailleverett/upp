<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\Site;
use Pest\Browser\Playwright\Playwright;

beforeEach(function (): void {
    config(['app.domain' => 'statuskit.test']);
});

it('displays the overall status banner', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'status-banner-test']);
    Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    Playwright::setHost('status-banner-test.statuskit.test');

    $page = visit('/');

    $page->assertSee('All Systems Operational');
});

it('shows component statuses in the grid', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'component-grid-test']);

    Component::factory()->for($site)->create([
        'name' => 'API Gateway',
        'status' => ComponentStatus::Operational,
    ]);

    Component::factory()->for($site)->create([
        'name' => 'Database',
        'status' => ComponentStatus::MajorOutage,
    ]);

    Playwright::setHost('component-grid-test.statuskit.test');

    $page = visit('/');

    $page->assertSee('API Gateway')
        ->assertSee('Operational')
        ->assertSee('Database')
        ->assertSee('Major Outage');
});

it('shows active incidents prominently', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'incident-banner-test']);

    Incident::factory()->for($site)->create([
        'title' => 'Payment Service Disruption',
        'status' => IncidentStatus::Investigating,
    ]);

    Playwright::setHost('incident-banner-test.statuskit.test');

    $page = visit('/');

    $page->assertSee('Payment Service Disruption');
});
