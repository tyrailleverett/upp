<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentDailyUptime;
use App\Models\ComponentStatusLog;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has correct fillable attributes', function (): void {
    $component = new Component();

    expect($component->getFillable())->toBe([
        'site_id',
        'name',
        'description',
        'group',
        'status',
        'sort_order',
    ]);
});

it('casts status to ComponentStatus enum', function (): void {
    $component = Component::factory()->create(['status' => ComponentStatus::Operational]);

    expect($component->status)->toBeInstanceOf(ComponentStatus::class);
    expect($component->status)->toBe(ComponentStatus::Operational);
});

it('belongs to a site', function (): void {
    $component = Component::factory()->create();

    expect($component->site)->toBeInstanceOf(Site::class);
});

it('has many status logs', function (): void {
    $component = Component::factory()->create();
    ComponentStatusLog::factory()->count(3)->for($component)->create();

    expect($component->statusLogs)->toHaveCount(3);
    expect($component->statusLogs->first())->toBeInstanceOf(ComponentStatusLog::class);
});

it('has many daily uptimes', function (): void {
    $component = Component::factory()->create();
    ComponentDailyUptime::factory()->count(2)->for($component)->create();

    expect($component->dailyUptimes)->toHaveCount(2);
    expect($component->dailyUptimes->first())->toBeInstanceOf(ComponentDailyUptime::class);
});

it('orders by sort_order then name in ordered scope', function (): void {
    $site = Site::factory()->create();
    $c3 = Component::factory()->for($site)->create(['name' => 'Zebra', 'sort_order' => 2]);
    $c1 = Component::factory()->for($site)->create(['name' => 'Alpha', 'sort_order' => 0]);
    $c2 = Component::factory()->for($site)->create(['name' => 'Beta', 'sort_order' => 1]);

    $ordered = $site->components()->ordered()->get();

    expect($ordered->pluck('id')->all())->toBe([$c1->id, $c2->id, $c3->id]);
});

it('filters by group in inGroup scope', function (): void {
    $site = Site::factory()->create();
    Component::factory()->for($site)->inGroup('Core Services')->create();
    Component::factory()->for($site)->inGroup('Infrastructure')->create();

    $coreServices = $site->components()->inGroup('Core Services')->get();

    expect($coreServices)->toHaveCount(1);
    expect($coreServices->first()->group)->toBe('Core Services');
});

it('logs status change to component_status_logs table', function (): void {
    $component = Component::factory()->create(['status' => ComponentStatus::Operational]);

    $component->logStatusChange();

    expect(ComponentStatusLog::query()->where('component_id', $component->id)->count())->toBe(1);
    expect(ComponentStatusLog::query()->where('component_id', $component->id)->first()->status)->toBe(ComponentStatus::Operational);
});
