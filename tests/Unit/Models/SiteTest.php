<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;
use App\Enums\SiteVisibility;
use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has correct fillable attributes', function (): void {
    $site = new Site();

    expect($site->getFillable())->toBe([
        'user_id',
        'name',
        'slug',
        'description',
        'visibility',
        'custom_domain',
        'logo_path',
        'favicon_path',
        'accent_color',
        'custom_css',
        'meta_title',
        'meta_description',
        'published_at',
        'suspended_at',
    ]);
});

it('casts visibility to SiteVisibility enum', function (): void {
    $site = Site::factory()->create(['visibility' => SiteVisibility::Draft]);

    expect($site->visibility)->toBeInstanceOf(SiteVisibility::class);
    expect($site->visibility)->toBe(SiteVisibility::Draft);
});

it('casts published_at and suspended_at to datetime', function (): void {
    $site = Site::factory()->published()->suspended()->create();

    expect($site->published_at)->toBeInstanceOf(DateTimeInterface::class);
    expect($site->suspended_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('uses slug as route key name', function (): void {
    $site = new Site();

    expect($site->getRouteKeyName())->toBe('slug');
});

it('belongs to a user', function (): void {
    $site = Site::factory()->create();

    expect($site->user)->toBeInstanceOf(User::class);
});

it('has many components', function (): void {
    $site = Site::factory()->create();
    Component::factory()->count(3)->for($site)->create();

    expect($site->components)->toHaveCount(3);
    expect($site->components->first())->toBeInstanceOf(Component::class);
});

it('reports isPublished correctly', function (): void {
    $draft = Site::factory()->create();
    $published = Site::factory()->published()->create();

    expect($draft->isPublished())->toBeFalse();
    expect($published->isPublished())->toBeTrue();
});

it('reports isDraft correctly', function (): void {
    $draft = Site::factory()->create();
    $published = Site::factory()->published()->create();

    expect($draft->isDraft())->toBeTrue();
    expect($published->isDraft())->toBeFalse();
});

it('reports isSuspended correctly', function (): void {
    $draft = Site::factory()->create();
    $suspended = Site::factory()->suspended()->create();

    expect($draft->isSuspended())->toBeFalse();
    expect($suspended->isSuspended())->toBeTrue();
});

it('computes overall status from worst component status', function (): void {
    $site = Site::factory()->create();
    Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);
    Component::factory()->for($site)->majorOutage()->create();

    expect($site->overallStatus())->toBe(ComponentStatus::MajorOutage);
});

it('returns operational when no components exist', function (): void {
    $site = Site::factory()->create();

    expect($site->overallStatus())->toBe(ComponentStatus::Operational);
});

it('computes overall status using active maintenance overlays', function (): void {
    $site = Site::factory()->create();
    $component = Component::factory()->for($site)->create(['status' => ComponentStatus::Operational]);

    MaintenanceWindow::factory()->active()->for($site)->create()
        ->components()->attach([$component->id]);

    expect($site->overallStatus())->toBe(ComponentStatus::UnderMaintenance);
});

it('scopes to published sites', function (): void {
    Site::factory()->create();
    Site::factory()->published()->create();

    $published = Site::query()->published()->get();

    expect($published)->toHaveCount(1);
    expect($published->first()->visibility)->toBe(SiteVisibility::Published);
});

it('scopes to sites owned by a user', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    Site::factory()->for($user)->create();
    Site::factory()->for($other)->create();

    $sites = Site::query()->ownedBy($user)->get();

    expect($sites)->toHaveCount(1);
    expect($sites->first()->user_id)->toBe($user->id);
});
