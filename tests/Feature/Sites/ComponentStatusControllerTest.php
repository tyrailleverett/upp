<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('updates component status', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $response = $this->actingAs($user)->put(
        route('sites.components.status.update', [$site, $component]),
        ['status' => ComponentStatus::MajorOutage->value]
    );

    $response->assertRedirect();
    assertDatabaseHas('components', [
        'id' => $component->id,
        'status' => ComponentStatus::MajorOutage->value,
    ]);
});

it('logs status change when updating', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    $this->actingAs($user)->put(
        route('sites.components.status.update', [$site, $component]),
        ['status' => ComponentStatus::DegradedPerformance->value]
    );

    $component->refresh();

    assertDatabaseHas('component_status_logs', [
        'component_id' => $component->id,
        'status' => ComponentStatus::DegradedPerformance->value,
    ]);
});

it('rejects invalid status values', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->put(
        route('sites.components.status.update', [$site, $component]),
        ['status' => 'not_a_valid_status']
    );

    $response->assertSessionHasErrors(['status']);
});

it('rejects setting component status to under maintenance directly', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->put(
        route('sites.components.status.update', [$site, $component]),
        ['status' => ComponentStatus::UnderMaintenance->value]
    );

    $response->assertSessionHasErrors(['status']);
});

it('prevents updating status of another users component', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->put(
        route('sites.components.status.update', [$site, $component]),
        ['status' => ComponentStatus::MajorOutage->value]
    );

    $response->assertForbidden();
});

it('returns not found when updating a component through the wrong site route', function (): void {
    $user = User::factory()->create();
    $ownedSite = Site::factory()->for($user)->create();
    $otherSite = Site::factory()->for($user)->create();
    $component = Component::factory()->for($otherSite)->create();

    $response = $this->actingAs($user)->put(
        route('sites.components.status.update', [$ownedSite, $component]),
        ['status' => ComponentStatus::MajorOutage->value]
    );

    $response->assertNotFound();
});
