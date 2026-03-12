<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\SiteVisibility;
use App\Models\Component;
use App\Models\Incident;
use App\Models\MaintenanceWindow;
use App\Models\Site;

beforeEach(function (): void {
    config(['app.domain' => 'statuskit.test']);
});

it('renders the status page for a published site', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);

    $response = $this->get('http://acme.statuskit.test/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('status-page/index'));
});

it('returns 404 for a draft site', function (): void {
    Site::factory()->create(['slug' => 'draft-site', 'visibility' => SiteVisibility::Draft]);

    $response = $this->get('http://draft-site.statuskit.test/');

    $response->assertNotFound();
});

it('renders suspended page for a suspended site', function (): void {
    Site::factory()->suspended()->create(['slug' => 'suspended-site']);

    $response = $this->get('http://suspended-site.statuskit.test/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('status-page/suspended'));
});

it('returns 404 for a non-existent subdomain', function (): void {
    $response = $this->get('http://nonexistent.statuskit.test/');

    $response->assertNotFound();
});

it('displays effective component statuses', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $response = $this->get('http://acme.statuskit.test/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('status-page/index')
        ->has('effective_statuses')
        ->where("effective_statuses.{$component->id}", ComponentStatus::UnderMaintenance->value)
    );
});

it('shows active incidents on the status page', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    Incident::factory()->for($site)->create(['title' => 'API Outage', 'status' => 'investigating']);

    $response = $this->get('http://acme.statuskit.test/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('status-page/index')
        ->has('open_incidents', 1)
        ->where('open_incidents.0.title', 'API Outage')
    );
});

it('includes recent resolved incidents in incident history', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $incident = Incident::factory()->resolved()->for($site)->create(['title' => 'Recovered API Outage']);

    $response = $this->get('http://acme.statuskit.test/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('status-page/index')
        ->has('incident_history', 1)
        ->where('incident_history.0.title', $incident->title)
    );
});

it('shows upcoming maintenance on the status page', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    MaintenanceWindow::factory()->upcoming()->for($site)->create(['title' => 'DB Migration']);

    $response = $this->get('http://acme.statuskit.test/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('status-page/index')
        ->has('upcoming_maintenance', 1)
        ->where('upcoming_maintenance.0.title', 'DB Migration')
    );
});
