<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;

it('adds an update to an incident', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.updates.store', [$site, $incident]), [
        'status' => IncidentStatus::Identified->value,
        'message' => 'We have identified the root cause.',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('incident_updates', [
        'incident_id' => $incident->id,
        'message' => 'We have identified the root cause.',
        'status' => 'identified',
    ]);
});

it('updates incident status when adding an update', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create(['status' => IncidentStatus::Investigating]);

    $this->actingAs($user)->post(route('sites.incidents.updates.store', [$site, $incident]), [
        'status' => IncidentStatus::Monitoring->value,
        'message' => 'A fix has been deployed and we are monitoring.',
    ]);

    assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'status' => 'monitoring',
    ]);
});

it('resolves an incident with message', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.resolve', [$site, $incident]), [
        'message' => 'This incident has been resolved.',
    ]);

    $response->assertRedirect(route('sites.incidents.show', [$site, $incident]));
    assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'status' => 'resolved',
    ]);
    assertDatabaseHas('incident_updates', [
        'incident_id' => $incident->id,
        'message' => 'This incident has been resolved.',
        'status' => 'resolved',
    ]);
});

it('resolves an incident with postmortem', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $this->actingAs($user)->post(route('sites.incidents.resolve', [$site, $incident]), [
        'message' => 'Resolved.',
        'postmortem' => 'Root cause was a misconfigured load balancer.',
    ]);

    assertDatabaseHas('incidents', [
        'id' => $incident->id,
        'postmortem' => 'Root cause was a misconfigured load balancer.',
    ]);
});

it('sets resolved_at when resolving', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $this->actingAs($user)->post(route('sites.incidents.resolve', [$site, $incident]), [
        'message' => 'All clear.',
    ]);

    $incident->refresh();

    expect($incident->resolved_at)->not->toBeNull();
    expect($incident->isResolved())->toBeTrue();
});

it('does not change component statuses when resolving an incident', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();
    $component = Component::factory()->for($site)->create([
        'status' => ComponentStatus::MajorOutage,
    ]);

    $incident->components()->attach($component);

    $this->actingAs($user)->post(route('sites.incidents.resolve', [$site, $incident]), [
        'message' => 'Resolved without changing components.',
    ]);

    expect($component->fresh()->status)->toBe(ComponentStatus::MajorOutage);
});

it('rejects empty update message', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $incident = Incident::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.updates.store', [$site, $incident]), [
        'status' => IncidentStatus::Identified->value,
        'message' => '',
    ]);

    $response->assertSessionHasErrors(['message']);
});

it('prevents updating incidents of another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();
    $incident = Incident::factory()->for($site)->create();

    $response = $this->actingAs($user)->post(route('sites.incidents.updates.store', [$site, $incident]), [
        'status' => IncidentStatus::Identified->value,
        'message' => 'Some update.',
    ]);

    $response->assertForbidden();
});
