<?php

declare(strict_types=1);

use App\Enums\SiteVisibility;
use App\Models\Site;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

it('displays sites index for authenticated user', function (): void {
    $user = User::factory()->create();
    Site::factory()->for($user)->count(3)->create();

    $response = $this->actingAs($user)->get(route('sites.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/index')
        ->has('sites', 3)
    );
});

it('only shows sites owned by the authenticated user', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Site::factory()->for($user)->create(['name' => 'My Site']);
    Site::factory()->for($otherUser)->create(['name' => 'Other Site']);

    $response = $this->actingAs($user)->get(route('sites.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/index')
        ->has('sites', 1)
        ->where('sites.0.name', 'My Site')
    );
});

it('renders create site page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('sites.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('sites/create'));
});

it('creates a site with valid data', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('sites.store'), [
        'name' => 'My Status Page',
        'slug' => 'my-status-page',
        'description' => 'A public status page.',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('sites', [
        'user_id' => $user->id,
        'name' => 'My Status Page',
        'slug' => 'my-status-page',
        'visibility' => SiteVisibility::Draft->value,
    ]);
});

it('rejects duplicate slugs', function (): void {
    $user = User::factory()->create();
    Site::factory()->for($user)->create(['slug' => 'existing-slug']);

    $response = $this->actingAs($user)->post(route('sites.store'), [
        'name' => 'Another Site',
        'slug' => 'existing-slug',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('rejects invalid slug characters', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('sites.store'), [
        'name' => 'Bad Slug',
        'slug' => 'Invalid Slug!',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('displays site show page with components', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();
    $site->components()->createMany([
        ['name' => 'API', 'status' => 'operational', 'sort_order' => 0],
        ['name' => 'Website', 'status' => 'operational', 'sort_order' => 1],
    ]);

    $response = $this->actingAs($user)->get(route('sites.show', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/show')
        ->has('site')
        ->has('site.components', 2)
    );
});

it('renders edit site page', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('sites.edit', $site));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('sites/edit')
        ->has('site')
    );
});

it('updates a site with valid data', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create(['name' => 'Old Name', 'slug' => 'old-slug']);

    $response = $this->actingAs($user)->put(route('sites.update', $site), [
        'name' => 'New Name',
        'slug' => 'old-slug',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('sites', [
        'id' => $site->id,
        'name' => 'New Name',
    ]);
});

it('rejects updating slug to an existing slug', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create(['slug' => 'my-site']);
    Site::factory()->for($user)->create(['slug' => 'other-site']);

    $response = $this->actingAs($user)->put(route('sites.update', $site), [
        'name' => 'My Site',
        'slug' => 'other-site',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('deletes a site', function (): void {
    $user = User::factory()->create();
    $site = Site::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('sites.destroy', $site));

    $response->assertRedirect(route('sites.index'));
    assertDatabaseMissing('sites', ['id' => $site->id]);
});

it('prevents viewing another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->get(route('sites.show', $site));

    $response->assertForbidden();
});

it('prevents updating another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->put(route('sites.update', $site), [
        'name' => 'Hijacked',
        'slug' => $site->slug,
    ]);

    $response->assertForbidden();
});

it('prevents deleting another users site', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $site = Site::factory()->for($otherUser)->create();

    $response = $this->actingAs($user)->delete(route('sites.destroy', $site));

    $response->assertForbidden();
});

it('redirects unauthenticated users to login', function (): void {
    $response = $this->get(route('sites.index'));

    $response->assertRedirect(route('login'));
});

it('redirects unverified users', function (): void {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('sites.index'));

    $response->assertRedirect();
});
