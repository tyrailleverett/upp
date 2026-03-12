<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has no updated_at column', function (): void {
    expect(Schema::hasColumn('incident_updates', 'updated_at'))->toBeFalse();
});

it('casts status to IncidentStatus enum', function (): void {
    $update = IncidentUpdate::factory()->create(['status' => IncidentStatus::Identified]);

    expect($update->status)->toBeInstanceOf(IncidentStatus::class);
    expect($update->status)->toBe(IncidentStatus::Identified);
});

it('belongs to an incident', function (): void {
    $update = IncidentUpdate::factory()->create();

    expect($update->incident)->toBeInstanceOf(Incident::class);
});
