<?php

declare(strict_types=1);

use App\Models\MaintenanceWindow;
use App\Models\Site;

it('completes expired maintenance windows', function (): void {
    $site = Site::factory()->create();
    MaintenanceWindow::factory()->expired()->for($site)->create();
    MaintenanceWindow::factory()->expired()->for($site)->create();

    $this->artisan('maintenance:complete-expired');

    $completed = MaintenanceWindow::query()->whereNotNull('completed_at')->count();
    expect($completed)->toBe(2);
});

it('sets completed_at to ends_at value', function (): void {
    $site = Site::factory()->create();
    $window = MaintenanceWindow::factory()->expired()->for($site)->create();

    $this->artisan('maintenance:complete-expired');

    $window->refresh();
    expect($window->completed_at->toDateTimeString())
        ->toBe($window->ends_at->toDateTimeString());
});

it('does not complete windows that have not expired', function (): void {
    $site = Site::factory()->create();
    MaintenanceWindow::factory()->active()->for($site)->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create();

    $this->artisan('maintenance:complete-expired');

    $completed = MaintenanceWindow::query()->whereNotNull('completed_at')->count();
    expect($completed)->toBe(0);
});

it('does not complete already completed windows', function (): void {
    $site = Site::factory()->create();
    $window = MaintenanceWindow::factory()->completed()->for($site)->create();
    $completedAt = $window->completed_at;

    $this->artisan('maintenance:complete-expired');

    $window->refresh();
    expect($window->completed_at->toDateTimeString())
        ->toBe($completedAt->toDateTimeString());
});

it('handles no expired windows gracefully', function (): void {
    $this->artisan('maintenance:complete-expired')->assertSuccessful();
});
