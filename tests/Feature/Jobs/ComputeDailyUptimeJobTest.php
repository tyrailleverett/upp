<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Jobs\ComputeDailyUptimeJob;
use App\Models\Component;
use App\Models\ComponentDailyUptime;
use App\Models\Site;
use Carbon\Carbon;

it('computes daily uptime for all site components', function (): void {
    $site = Site::factory()->published()->create();
    Component::factory()->for($site)->count(3)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday()->toDateString();

    (new ComputeDailyUptimeJob($site->id, $date))->handle(
        app(App\Services\UptimeCalculationService::class)
    );

    expect(ComponentDailyUptime::query()->count())->toBe(3);
});

it('upserts existing records for the same date', function (): void {
    $site = Site::factory()->published()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday()->toDateString();

    // Run twice
    $job = new ComputeDailyUptimeJob($site->id, $date);
    $service = app(App\Services\UptimeCalculationService::class);
    $job->handle($service);
    $job->handle($service);

    expect(ComponentDailyUptime::query()->where('component_id', $component->id)->count())->toBe(1);
});

it('handles components with no status logs', function (): void {
    $site = Site::factory()->published()->create();
    Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $date = Carbon::yesterday()->toDateString();

    (new ComputeDailyUptimeJob($site->id, $date))->handle(
        app(App\Services\UptimeCalculationService::class)
    );

    $record = ComponentDailyUptime::query()->first();

    expect($record)->not->toBeNull();
    expect($record->uptime_percentage)->toEqual(100.0);
});
