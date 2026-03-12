<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has correct fillable attributes', function (): void {
    $incident = new Incident();

    expect($incident->getFillable())->toBe([
        'site_id',
        'title',
        'status',
        'postmortem',
        'resolved_at',
    ]);
});

it('casts status to IncidentStatus enum', function (): void {
    $incident = Incident::factory()->create(['status' => IncidentStatus::Investigating]);

    expect($incident->status)->toBeInstanceOf(IncidentStatus::class);
    expect($incident->status)->toBe(IncidentStatus::Investigating);
});

it('casts resolved_at to datetime', function (): void {
    $incident = Incident::factory()->resolved()->create();

    expect($incident->resolved_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('belongs to a site', function (): void {
    $incident = Incident::factory()->create();

    expect($incident->site)->toBeInstanceOf(Site::class);
});

it('has many updates', function (): void {
    $incident = Incident::factory()->create();
    IncidentUpdate::factory()->count(3)->for($incident)->create();

    expect($incident->updates)->toHaveCount(3);
    expect($incident->updates->first())->toBeInstanceOf(IncidentUpdate::class);
});

it('belongs to many components', function (): void {
    $incident = Incident::factory()->create();
    $components = Component::factory()->count(2)->for($incident->site)->create();
    $incident->components()->attach($components->pluck('id'));

    expect($incident->components)->toHaveCount(2);
    expect($incident->components->first())->toBeInstanceOf(Component::class);
});

it('scopes to open incidents', function (): void {
    $site = Site::factory()->create();
    Incident::factory()->for($site)->create(['status' => IncidentStatus::Investigating]);
    Incident::factory()->for($site)->create(['status' => IncidentStatus::Identified]);
    Incident::factory()->resolved()->for($site)->create();

    $open = Incident::query()->open()->get();

    expect($open)->toHaveCount(2);
    expect($open->pluck('status')->map->value->all())->not->toContain('resolved');
});

it('scopes to resolved incidents', function (): void {
    $site = Site::factory()->create();
    Incident::factory()->for($site)->create(['status' => IncidentStatus::Investigating]);
    Incident::factory()->resolved()->for($site)->create();
    Incident::factory()->resolved()->for($site)->create();

    $resolved = Incident::query()->resolved()->get();

    expect($resolved)->toHaveCount(2);
});

it('reports isResolved correctly', function (): void {
    $open = Incident::factory()->create(['status' => IncidentStatus::Investigating]);
    $resolved = Incident::factory()->resolved()->create();

    expect($open->isResolved())->toBeFalse();
    expect($resolved->isResolved())->toBeTrue();
});

it('returns latest update', function (): void {
    $incident = Incident::factory()->create();
    $first = IncidentUpdate::factory()->for($incident)->create(['created_at' => now()->subMinutes(10)]);
    $latest = IncidentUpdate::factory()->for($incident)->create(['created_at' => now()]);

    $result = $incident->latestUpdate();

    expect($result)->toBeInstanceOf(IncidentUpdate::class);
    expect($result->id)->toBe($latest->id);
});
