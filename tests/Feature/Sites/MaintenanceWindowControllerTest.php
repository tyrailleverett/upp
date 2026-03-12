<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('displays maintenance windows index', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('sites.maintenance.index', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/maintenance/index')
        ->has('site')
        ->has('active')
        ->has('upcoming')
        ->has('completed')
    );
});

it('separates active, upcoming, and completed windows', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    MaintenanceWindow::factory()->active()->for($site)->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create();
    MaintenanceWindow::factory()->completed()->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.maintenance.index', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('active', 1)
        ->has('upcoming', 2)
        ->has('completed', 1)
    );
});

it('renders create maintenance window page', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    Component::factory()->count(3)->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.maintenance.create', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/maintenance/create')
        ->has('site')
        ->has('components', 3)
    );
});

it('creates a maintenance window with valid data', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $scheduledAt = now()->addDay()->format('Y-m-d H:i:s');
    $endsAt = now()->addDay()->addHours(2)->format('Y-m-d H:i:s');

    $response = $this->actingAs($user)->post(route('sites.maintenance.store', $site), [
        'title' => 'Database Upgrade',
        'description' => 'Upgrading database to v15.',
        'scheduled_at' => $scheduledAt,
        'ends_at' => $endsAt,
        'component_ids' => [$component->id],
    ]);

    $response->assertRedirect(route('sites.maintenance.index', $site));

    assertDatabaseHas('maintenance_windows', [
        'site_id' => $site->id,
        'title' => 'Database Upgrade',
    ]);
});

it('attaches components to maintenance window', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $components = Component::factory()->count(2)->for($site)->create();

    $this->actingAs($user)->post(route('sites.maintenance.store', $site), [
        'title' => 'Database Upgrade',
        'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        'component_ids' => $components->pluck('id')->all(),
    ]);

    $window = MaintenanceWindow::first();

    expect($window->components)->toHaveCount(2);
});

it('rejects maintenance window with end before start', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.maintenance.store', $site), [
        'title' => 'Bad Window',
        'scheduled_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'component_ids' => [$component->id],
    ]);

    $response->assertSessionHasErrors('ends_at');
});

it('rejects maintenance window with past start time', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.maintenance.store', $site), [
        'title' => 'Past Window',
        'scheduled_at' => now()->subDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'component_ids' => [$component->id],
    ]);

    $response->assertSessionHasErrors('scheduled_at');
});

it('displays maintenance window show page', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $window = MaintenanceWindow::factory()->upcoming()->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.maintenance.show', [$site, $window]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/maintenance/show')
        ->has('site')
        ->has('maintenanceWindow')
    );
});

it('updates a maintenance window', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();
    $window = MaintenanceWindow::factory()->upcoming()->for($site)->create();
    $window->components()->attach([$component->id]);

    $response = $this->actingAs($user)->put(route('sites.maintenance.update', [$site, $window]), [
        'title' => 'Updated Title',
        'scheduled_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDays(2)->addHours(3)->format('Y-m-d H:i:s'),
        'component_ids' => [$component->id],
    ]);

    $response->assertRedirect();

    assertDatabaseHas('maintenance_windows', [
        'id' => $window->id,
        'title' => 'Updated Title',
    ]);
});

it('deletes a maintenance window', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $window = MaintenanceWindow::factory()->upcoming()->for($site)->create();

    $response = $this->actingAs($user)->delete(route('sites.maintenance.destroy', [$site, $window]));

    $response->assertRedirect(route('sites.maintenance.index', $site));

    assertDatabaseMissing('maintenance_windows', ['id' => $window->id]);
});

it('completes a maintenance window manually', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $window = MaintenanceWindow::factory()->active()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.maintenance.complete', [$site, $window]));

    $response->assertRedirect();

    $window->refresh();
    expect($window->completed_at)->not->toBeNull();
});

it('prevents managing maintenance windows of another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();
    $window = MaintenanceWindow::factory()->upcoming()->for($site)->create();

    $this->actingAs($user)
        ->get(route('sites.maintenance.index', $site))
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('sites.maintenance.destroy', [$site, $window]))
        ->assertForbidden();
});
