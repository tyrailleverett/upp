<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Events\ComponentStatusChanged;
use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('broadcasts on the correct channel', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $event = new ComponentStatusChanged($component, ComponentStatus::MajorOutage);

    $channels = $event->broadcastOn();
    $channelNames = array_map(fn ($ch) => $ch->name, $channels);

    expect($channelNames)->toContain('site.acme');
});

it('includes correct data in broadcast payload', function (): void {
    $site = Site::factory()->published()->create(['slug' => 'acme']);
    $component = Component::factory()->for($site)->create([
        'name' => 'API',
        'status' => ComponentStatus::Operational,
    ]);

    $window = MaintenanceWindow::factory()->active()->for($site)->create();
    $window->components()->attach([$component->id]);

    $event = new ComponentStatusChanged($component, ComponentStatus::MajorOutage);

    $data = $event->broadcastWith();

    expect($data['component_id'])->toBe($component->id);
    expect($data['name'])->toBe('API');
    expect($data['status'])->toBe(ComponentStatus::UnderMaintenance->value);
    expect($data['previous_status'])->toBe(ComponentStatus::MajorOutage->value);
});
