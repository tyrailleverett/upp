<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentStatusLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has no updated_at column', function (): void {
    expect(ComponentStatusLog::UPDATED_AT)->toBeNull();
});

it('casts status to ComponentStatus enum', function (): void {
    $log = ComponentStatusLog::factory()->create(['status' => ComponentStatus::Operational]);

    expect($log->status)->toBeInstanceOf(ComponentStatus::class);
    expect($log->status)->toBe(ComponentStatus::Operational);
});

it('belongs to a component', function (): void {
    $log = ComponentStatusLog::factory()->create();

    expect($log->component)->toBeInstanceOf(Component::class);
});
