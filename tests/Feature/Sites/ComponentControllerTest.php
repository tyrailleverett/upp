<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('renders create component page', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('sites.components.create', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/components/create')
        ->has('site')
    );
});

it('creates a component with valid data', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('sites.components.store', $site), [
        'name' => 'API',
        'description' => 'The main API.',
        'sort_order' => 0,
    ]);

    $response->assertRedirect(route('sites.show', $site));
    assertDatabaseHas('components', [
        'site_id' => $site->id,
        'name' => 'API',
    ]);
});

it('logs initial status when creating a component', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $this->actingAs($user)->post(route('sites.components.store', $site), [
        'name' => 'API',
        'sort_order' => 0,
    ]);

    $component = $site->components()->where('name', 'API')->first();

    expect($component->statusLogs()->count())->toBe(1);
});

it('rejects duplicate component names within a site', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    Component::factory()->for($site)->create(['name' => 'API']);

    $response = $this->actingAs($user)->post(route('sites.components.store', $site), [
        'name' => 'API',
        'sort_order' => 1,
    ]);

    $response->assertSessionHasErrors(['name']);
});

it('allows same component name across different sites', function (): void {
    $user = User::factory()->create();
    $site1 = Site::factory()->for($user)->create();
    $site2 = Site::factory()->for($user)->create();
    Component::factory()->for($site1)->create(['name' => 'API']);

    $response = $this->actingAs($user)->post(route('sites.components.store', $site2), [
        'name' => 'API',
        'sort_order' => 0,
    ]);

    $response->assertRedirect(route('sites.show', $site2));
    assertDatabaseHas('components', ['site_id' => $site2->id, 'name' => 'API']);
});

it('renders edit component page', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.components.edit', [$site, $component]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/components/edit')
        ->has('site')
        ->has('component')
    );
});

it('updates a component', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create(['name' => 'Old Name']);

    $response = $this->actingAs($user)->put(route('sites.components.update', [$site, $component]), [
        'name' => 'New Name',
        'sort_order' => 0,
    ]);

    $response->assertRedirect();
    assertDatabaseHas('components', ['id' => $component->id, 'name' => 'New Name']);
});

it('deletes a component', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->delete(route('sites.components.destroy', [$site, $component]));

    $response->assertRedirect(route('sites.show', $site));
    assertDatabaseMissing('components', ['id' => $component->id]);
});

it('prevents managing components of another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->get(route('sites.components.create', $site));

    $response->assertForbidden();
});

it('returns not found when a component does not belong to the site route', function (): void {
    $user = User::factory()->create();
    $ownedSite = Site::factory()->for($user)->create();
    $otherSite = Site::factory()->for($user)->create();
    $component = Component::factory()->for($otherSite)->create();

    $response = $this->actingAs($user)->get(route('sites.components.edit', [$ownedSite, $component]));

    $response->assertNotFound();
});
