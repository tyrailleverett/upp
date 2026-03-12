<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;
use App\Models\Component;
use App\Models\Incident;
use App\Models\Site;
use App\Models\User;

it('allows creating an incident with affected components', function (): void {
    $user = User::factory()->create([
        'email' => 'incident-browser-create@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'name' => 'Incident Site',
        'slug' => 'incident-site',
    ]);

    Component::factory()->for($site)->create(['name' => 'API']);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.incidents.create', $site, false));

    $page->assertNoJavaScriptErrors()
        ->assertSee('API')
        ->click('API')
        ->fill('input#title', 'API is down')
        ->fill('textarea#message', 'We are investigating a complete API outage.')
        ->click('button[type="submit"]')
        ->assertSee('API is down')
        ->assertSee('Investigating');
});

it('allows posting a timeline update', function (): void {
    $user = User::factory()->create([
        'email' => 'incident-browser-update@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'slug' => 'timeline-site',
    ]);

    $incident = Incident::factory()->for($site)->create([
        'title' => 'Timeline Test Incident',
        'status' => IncidentStatus::Investigating,
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.incidents.show', [$site, $incident], false));

    $page->assertNoJavaScriptErrors()
        ->assertSee('Timeline Test Incident')
        ->fill('textarea#update-message', 'We have identified the root cause.')
        ->click('Post Update')
        ->assertSee('We have identified the root cause.');
});

it('allows resolving an incident with a postmortem', function (): void {
    $user = User::factory()->create([
        'email' => 'incident-browser-resolve@example.com',
        'password' => 'password',
    ]);

    $site = Site::factory()->for($user)->create([
        'slug' => 'resolve-site',
    ]);

    $incident = Incident::factory()->for($site)->create([
        'title' => 'Resolution Test Incident',
        'status' => IncidentStatus::Monitoring,
    ]);

    $page = visit('/login');

    $page->fill('input#email', $user->email)
        ->fill('input#password', 'password')
        ->click('button[type="submit"]')
        ->assertSee('Dashboard');

    $page = visit(route('sites.incidents.show', [$site, $incident], false));

    $page->assertNoJavaScriptErrors()
        ->assertSee('Resolution Test Incident')
        ->fill('textarea#resolve-message', 'The incident has been fully resolved.')
        ->fill('textarea#postmortem', 'Root cause was a misconfigured load balancer rule.')
        ->click('Resolve Incident')
        ->assertSee('Resolved')
        ->assertSee('Root cause was a misconfigured load balancer rule.');
});
