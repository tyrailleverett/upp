<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\IncidentUpdate;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('displays incidents index for a site', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    Incident::factory()->count(3)->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.incidents.index', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/incidents/index')
        ->has('site')
        ->has('incidents', 3)
    );
});

it('only shows incidents for the specified site', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $otherSite = Site::factory()->for($user)->create();
    Incident::factory()->count(2)->for($site)->create();
    Incident::factory()->count(3)->for($otherSite)->create();

    $response = $this->actingAs($user)->get(route('sites.incidents.index', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('incidents', 2)
    );
});

it('renders create incident page with site components', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    Component::factory()->count(2)->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.incidents.create', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/incidents/create')
        ->has('site')
        ->has('components', 2)
    );
});

it('creates an incident with valid data', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.store', $site), [
        'title' => 'API Outage',
        'status' => IncidentStatus::Investigating->value,
        'message' => 'We are investigating an issue with the API.',
        'component_ids' => [$component->id],
    ]);

    $response->assertRedirect();
    assertDatabaseHas('incidents', [
        'site_id' => $site->id,
        'title' => 'API Outage',
        'status' => 'investigating',
    ]);
});

it('attaches components to incident on creation', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $this->actingAs($user)->post(route('sites.incidents.store', $site), [
        'title' => 'DB Degradation',
        'status' => IncidentStatus::Identified->value,
        'message' => 'Database is experiencing degraded performance.',
        'component_ids' => [$component->id],
    ]);

    $incident = Incident::query()->where('title', 'DB Degradation')->first();

    expect($incident->components()->count())->toBe(1);
    expect($incident->components()->first()->id)->toBe($component->id);
});

it('creates initial incident update on creation', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $component = Component::factory()->for($site)->create();

    $this->actingAs($user)->post(route('sites.incidents.store', $site), [
        'title' => 'Network Issue',
        'status' => IncidentStatus::Investigating->value,
        'message' => 'We are investigating a network issue.',
        'component_ids' => [$component->id],
    ]);

    $incident = Incident::query()->where('title', 'Network Issue')->first();

    expect($incident->updates()->count())->toBe(1);
    expect($incident->updates()->first()->message)->toBe('We are investigating a network issue.');
});

it('rejects incident without components', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.store', $site), [
        'title' => 'Missing Components',
        'status' => IncidentStatus::Investigating->value,
        'message' => 'Some message.',
        'component_ids' => [],
    ]);

    $response->assertSessionHasErrors(['component_ids']);
});

it('rejects components from another site', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $otherSite = Site::factory()->for($user)->create();
    $foreignComponent = Component::factory()->for($otherSite)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.store', $site), [
        'title' => 'Foreign Component Incident',
        'status' => IncidentStatus::Investigating->value,
        'message' => 'Some message.',
        'component_ids' => [$foreignComponent->id],
    ]);

    $response->assertSessionHasErrors(['component_ids.0']);
});

it('displays incident show page with timeline', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $response = $this->actingAs($user)->get(route('sites.incidents.show', [$site, $incident]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/incidents/show')
        ->has('site')
        ->has('incident')
        ->has('siteComponents')
    );
});

it('loads incident updates in reverse chronological order on the index page', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    IncidentUpdate::factory()->for($incident)->create([
        'status' => IncidentStatus::Investigating,
        'message' => 'Initial update',
        'created_at' => now()->subMinutes(10),
    ]);

    IncidentUpdate::factory()->for($incident)->create([
        'status' => IncidentStatus::Monitoring,
        'message' => 'Latest update',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('sites.incidents.index', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('incidents.0.updates.0.message', 'Latest update')
    );
});

it('returns not found when an incident does not belong to the site route parameter', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $otherSite = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($otherSite)->create();

    $response = $this->actingAs($user)->get(route('sites.incidents.show', [$site, $incident]));

    $response->assertNotFound();
});

it('updates an incident title and components', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create(['title' => 'Old Title']);
    $component = Component::factory()->for($site)->create();

    $response = $this->actingAs($user)->put(route('sites.incidents.update', [$site, $incident]), [
        'title' => 'New Title',
        'component_ids' => [$component->id],
    ]);

    $response->assertRedirect();
    assertDatabaseHas('incidents', ['id' => $incident->id, 'title' => 'New Title']);
});

it('deletes an incident', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $response = $this->actingAs($user)->delete(route('sites.incidents.destroy', [$site, $incident]));

    $response->assertRedirect(route('sites.incidents.index', $site));
    assertDatabaseMissing('incidents', ['id' => $incident->id]);
});

it('prevents managing incidents of another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->get(route('sites.incidents.create', $site));

    $response->assertForbidden();
});

it('redirects unauthenticated users', function (): void {
    $site = Site::factory()->create();

    $response = $this->get(route('sites.incidents.index', $site));

    $response->assertRedirect(route('login'));
});
