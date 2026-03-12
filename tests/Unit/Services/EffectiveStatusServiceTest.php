<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use App\Services\EffectiveStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('returns base status when component is not operational', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::MajorOutage]);
    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $service = new EffectiveStatusService();
    $status = $service->resolveComponentStatus($component);

    expect($status)->toBe(ComponentStatus::MajorOutage);
});

it('returns operational when no active maintenance exists', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $service = new EffectiveStatusService();
    $status = $service->resolveComponentStatus($component);

    expect($status)->toBe(ComponentStatus::Operational);
});

it('returns under_maintenance when component is operational and has active maintenance', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);
    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $service = new EffectiveStatusService();
    $status = $service->resolveComponentStatus($component);

    expect($status)->toBe(ComponentStatus::UnderMaintenance);
});

it('returns degraded_performance even with active maintenance', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::DegradedPerformance]);
    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $service = new EffectiveStatusService();
    $status = $service->resolveComponentStatus($component);

    expect($status)->toBe(ComponentStatus::DegradedPerformance);
});

it('returns partial_outage even with active maintenance', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::PartialOutage]);
    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $service = new EffectiveStatusService();
    $status = $service->resolveComponentStatus($component);

    expect($status)->toBe(ComponentStatus::PartialOutage);
});

it('returns major_outage even with active maintenance', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::MajorOutage]);
    MaintenanceWindow::factory()->active()->for($site)->create()->components()->attach([$component->id]);

    $service = new EffectiveStatusService();
    $status = $service->resolveComponentStatus($component);

    expect($status)->toBe(ComponentStatus::MajorOutage);
});

it('resolves all component statuses for a site', function (): void {
    $site = Site::factory()->create();
    $operational = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);
    $degraded = Component::factory()->for($site)->create(['status' => ComponentStatus::DegradedPerformance]);
    $inMaintenance = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    MaintenanceWindow::factory()->active()->for($site)->create()
        ->components()->attach([$inMaintenance->id]);

    $service = new EffectiveStatusService();
    $statuses = $service->resolveAllComponentStatuses($site);

    expect($statuses[$operational->id])->toBe(ComponentStatus::Operational);
    expect($statuses[$degraded->id])->toBe(ComponentStatus::DegradedPerformance);
    expect($statuses[$inMaintenance->id])->toBe(ComponentStatus::UnderMaintenance);
});

it('returns operational for overall status when site has no components', function (): void {
    $site = Site::factory()->create();

    $service = new EffectiveStatusService();
    $status = $service->resolveOverallSiteStatus($site);

    expect($status)->toBe(ComponentStatus::Operational);
});

it('returns worst case status for overall site status', function (): void {
    $site = Site::factory()->create();
    Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);
    Component::factory()->for($site)->create(['status' => ComponentStatus::DegradedPerformance]);
    Component::factory()->for($site)->create(['status' => ComponentStatus::MajorOutage]);

    $service = new EffectiveStatusService();
    $status = $service->resolveOverallSiteStatus($site);

    expect($status)->toBe(ComponentStatus::MajorOutage);
});
