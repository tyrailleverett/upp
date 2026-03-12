<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\MaintenanceWindow;
use App\Models\Site;

it('returns site status with all components', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    Component::factory()->for($site)->count(3)->create();

    $response = $this->getJson(route('api.sites.status', ['slug' => 'acme']));

    $response->assertOk();
    $response->assertJsonPath('data.slug', 'acme');
    $response->assertJsonCount(3, 'data.components');
});

it('returns effective statuses not base statuses', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);
    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $response = $this->getJson(route('api.sites.status', ['slug' => 'acme']));

    $response->assertOk();
    // Overall status should reflect the maintenance
    $response->assertJsonPath('data.overall_status', ComponentStatus::UnderMaintenance->value);
});

it('returns 404 for unpublished sites', function (): void {
    Site::factory()->create(['slug' => 'draft-site']);

    $response = $this->getJson(route('api.sites.status', ['slug' => 'draft-site']));

    $response->assertNotFound();
});

it('lists incidents with updates', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $incident = Incident::factory()->for($site)->create(['status' => 'investigating']);
    IncidentUpdate::factory()->for($incident)->create(['message' => 'We are looking into this.']);

    $response = $this->getJson(route('api.sites.incidents', ['slug' => 'acme']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.updates.0.message', 'We are looking into this.');
});

it('shows a single incident with full timeline', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $incident = Incident::factory()->for($site)->create(['title' => 'API Down']);
    IncidentUpdate::factory()->for($incident)->count(2)->create();

    $response = $this->getJson(route('api.sites.incidents.show', [
        'slug' => 'acme',
        'incident' => $incident->id,
    ]));

    $response->assertOk();
    $response->assertJsonPath('data.title', 'API Down');
    $response->assertJsonCount(2, 'data.updates');
});

it('lists upcoming maintenance windows', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    MaintenanceWindow::factory()->upcoming()->for($site)->create(['title' => 'DB Upgrade']);

    $response = $this->getJson(route('api.sites.maintenance', ['slug' => 'acme']));

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.title', 'DB Upgrade');
});

it('applies rate limiting', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);

    for ($attempt = 0; $attempt < 60; $attempt++) {
        $this->getJson(route('api.sites.status', ['slug' => $site->slug]))
            ->assertOk();
    }

    $this->getJson(route('api.sites.status', ['slug' => $site->slug]))
        ->assertTooManyRequests();
});

it('returns polling payload fields for the status page fallback', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);
    $incident = Incident::factory()->for($site)->create(['title' => 'API Down']);
    IncidentUpdate::factory()->for($incident)->create(['message' => 'Investigating']);
    MaintenanceWindow::factory()->upcoming()->for($site)->create(['title' => 'DB Upgrade']);
    MaintenanceWindow::factory()->active()->for($site)->create(['title' => 'Cache Flush']);

    $response = $this->getJson(route('api.sites.status', ['slug' => 'acme']));

    $response->assertOk();
    $response->assertJsonPath('data.components.0.id', $component->id);
    $response->assertJsonPath("effective_statuses.{$component->id}", ComponentStatus::Operational->value);
    $response->assertJsonPath('open_incidents.0.title', 'API Down');
    $response->assertJsonPath('incident_history.0.title', 'API Down');
    $response->assertJsonPath('upcoming_maintenance.0.title', 'DB Upgrade');
    $response->assertJsonPath('active_maintenance.0.title', 'Cache Flush');
});

it('paginates incidents list', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    Incident::factory()->for($site)->count(25)->create();

    $response = $this->getJson(route('api.sites.incidents', ['slug' => 'acme']));

    $response->assertOk();
    $response->assertJsonPath('meta.per_page', 20);
    $response->assertJsonPath('meta.total', 25);
});
