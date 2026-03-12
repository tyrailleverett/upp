<?php

declare(strict_types=1);

use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('has correct fillable attributes', function (): void {
    $window = new MaintenanceWindow();

    expect($window->getFillable())->toBe([
        'site_id',
        'title',
        'description',
        'scheduled_at',
        'ends_at',
        'completed_at',
        'started_notified_at',
    ]);
});

it('casts scheduled_at, ends_at, completed_at to datetime', function (): void {
    $window = MaintenanceWindow::factory()->completed()->create();

    expect($window->scheduled_at)->toBeInstanceOf(DateTimeInterface::class);
    expect($window->ends_at)->toBeInstanceOf(DateTimeInterface::class);
    expect($window->completed_at)->toBeInstanceOf(DateTimeInterface::class);
});

it('belongs to a site', function (): void {
    $window = MaintenanceWindow::factory()->create();

    expect($window->site)->toBeInstanceOf(Site::class);
});

it('belongs to many components', function (): void {
    $site = Site::factory()->create();
    $window = MaintenanceWindow::factory()->for($site)->create();
    $components = Component::factory()->count(2)->for($site)->create();
    $window->components()->attach($components->pluck('id'));

    expect($window->components)->toHaveCount(2);
    expect($window->components->first())->toBeInstanceOf(Component::class);
});

it('scopes to active windows', function (): void {
    $site = Site::factory()->create();
    MaintenanceWindow::factory()->active()->for($site)->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create();
    MaintenanceWindow::factory()->completed()->for($site)->create();

    $active = MaintenanceWindow::query()->active()->get();

    expect($active)->toHaveCount(1);
});

it('scopes to upcoming windows', function (): void {
    $site = Site::factory()->create();
    MaintenanceWindow::factory()->active()->for($site)->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create();
    MaintenanceWindow::factory()->completed()->for($site)->create();

    $upcoming = MaintenanceWindow::query()->upcoming()->get();

    expect($upcoming)->toHaveCount(2);
});

it('scopes to completed windows', function (): void {
    $site = Site::factory()->create();
    MaintenanceWindow::factory()->active()->for($site)->create();
    MaintenanceWindow::factory()->completed()->for($site)->create();
    MaintenanceWindow::factory()->completed()->for($site)->create();

    $completed = MaintenanceWindow::query()->completed()->get();

    expect($completed)->toHaveCount(2);
});

it('scopes to expired windows', function (): void {
    $site = Site::factory()->create();
    MaintenanceWindow::factory()->expired()->for($site)->create();
    MaintenanceWindow::factory()->expired()->for($site)->create();
    MaintenanceWindow::factory()->completed()->for($site)->create();
    MaintenanceWindow::factory()->active()->for($site)->create();

    $expired = MaintenanceWindow::query()->expired()->get();

    expect($expired)->toHaveCount(2);
});

it('reports isActive correctly', function (): void {
    $active = MaintenanceWindow::factory()->active()->create();
    $upcoming = MaintenanceWindow::factory()->upcoming()->create();
    $completed = MaintenanceWindow::factory()->completed()->create();

    expect($active->isActive())->toBeTrue();
    expect($upcoming->isActive())->toBeFalse();
    expect($completed->isActive())->toBeFalse();
});

it('reports isUpcoming correctly', function (): void {
    $active = MaintenanceWindow::factory()->active()->create();
    $upcoming = MaintenanceWindow::factory()->upcoming()->create();
    $completed = MaintenanceWindow::factory()->completed()->create();

    expect($upcoming->isUpcoming())->toBeTrue();
    expect($active->isUpcoming())->toBeFalse();
    expect($completed->isUpcoming())->toBeFalse();
});

it('reports isCompleted correctly', function (): void {
    $completed = MaintenanceWindow::factory()->completed()->create();
    $active = MaintenanceWindow::factory()->active()->create();

    expect($completed->isCompleted())->toBeTrue();
    expect($active->isCompleted())->toBeFalse();
});
