<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentDailyUptime;
use App\Models\ComponentStatusLog;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use App\Services\UptimeCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('computes 100% uptime when component was operational all day', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday();
    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::Operational,
        'created_at' => $date->copy()->startOfDay()->addHour(),
    ]);

    $service = new UptimeCalculationService();
    $result = $service->computeForDate($component, $date);

    expect($result->uptime_percentage)->toEqual(100.0);
});

it('computes correct uptime with status changes during the day', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday();
    $dayStart = $date->copy()->startOfDay();

    // Operational first half, then major outage second half
    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::Operational,
        'created_at' => $dayStart->copy()->addMinutes(1),
    ]);
    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::MajorOutage,
        'created_at' => $dayStart->copy()->addHours(12),
    ]);

    $service = new UptimeCalculationService();
    $result = $service->computeForDate($component, $date);

    expect($result->uptime_percentage)->toBeLessThan(100.0);
    expect($result->uptime_percentage)->toBeGreaterThan(0.0);
    expect($result->minutes_operational)->toBeGreaterThan(0);
});

it('excludes maintenance window time from denominator', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday();
    $dayStart = $date->copy()->startOfDay();

    // 60-minute maintenance window
    $window = MaintenanceWindow::factory()->for($site)->create([
        'scheduled_at' => $dayStart->copy()->addHours(2),
        'ends_at' => $dayStart->copy()->addHours(3),
        'completed_at' => $dayStart->copy()->addHours(3),
    ]);
    $window->components()->attach([$component->id]);

    $service = new UptimeCalculationService();
    $result = $service->computeForDate($component, $date);

    expect($result->minutes_excluded_for_maintenance)->toBe(60);
    expect($result->uptime_percentage)->toEqual(100.0);
});

it('returns 100% when entire day was maintenance', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday();

    $window = MaintenanceWindow::factory()->for($site)->create([
        'scheduled_at' => $date->copy()->startOfDay(),
        'ends_at' => $date->copy()->endOfDay(),
        'completed_at' => $date->copy()->endOfDay(),
    ]);
    $window->components()->attach([$component->id]);

    $service = new UptimeCalculationService();
    $result = $service->computeForDate($component, $date);

    expect($result->uptime_percentage)->toEqual(100.0);
});

it('uses previous day status when no logs exist for the day', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::MajorOutage]);

    $date = Carbon::yesterday();

    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::MajorOutage,
        'created_at' => $date->copy()->subDay(),
    ]);

    $service = new UptimeCalculationService();
    $result = $service->computeForDate($component, $date);

    expect($result->uptime_percentage)->toEqual(0.0);
    expect($result->minutes_operational)->toBe(0);
});

it('handles multiple status changes in a single day', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday();
    $dayStart = $date->copy()->startOfDay();

    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::Operational,
        'created_at' => $dayStart->copy()->addMinutes(1),
    ]);
    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::MajorOutage,
        'created_at' => $dayStart->copy()->addHours(6),
    ]);
    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::Operational,
        'created_at' => $dayStart->copy()->addHours(12),
    ]);
    ComponentStatusLog::factory()->for($component)->create([
        'status' => ComponentStatus::DegradedPerformance,
        'created_at' => $dayStart->copy()->addHours(18),
    ]);

    $service = new UptimeCalculationService();
    $result = $service->computeForDate($component, $date);

    expect($result)->toBeInstanceOf(ComponentDailyUptime::class);
    expect($result->uptime_percentage)->toBeLessThan(100.0);
    expect($result->uptime_percentage)->toBeGreaterThan(0.0);
});
