<?php

declare(strict_types=1);

use App\Events\MaintenanceStarted;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use Illuminate\Support\Facades\Event;

it('dispatches MaintenanceStarted event for windows that just started', function (): void {
    Event::fake();

    $site = Site::factory()->published()->create();
    MaintenanceWindow::factory()->active()->for($site)->create(['started_notified_at' => null]);

    $this->artisan('maintenance:start-scheduled')->assertSuccessful();

    Event::assertDispatched(MaintenanceStarted::class);
});

it('sets started_notified_at to prevent duplicate dispatches', function (): void {
    Event::fake();

    $site = Site::factory()->published()->create();
    $window = MaintenanceWindow::factory()->active()->for($site)->create(['started_notified_at' => null]);

    $this->artisan('maintenance:start-scheduled');

    $window->refresh();
    expect($window->started_notified_at)->not->toBeNull();
});

it('ignores already notified windows', function (): void {
    Event::fake();

    $site = Site::factory()->published()->create();
    MaintenanceWindow::factory()->active()->for($site)->create([
        'started_notified_at' => now()->subMinutes(5),
    ]);

    $this->artisan('maintenance:start-scheduled');

    Event::assertNotDispatched(MaintenanceStarted::class);
});

it('ignores future windows', function (): void {
    Event::fake();

    $site = Site::factory()->published()->create();
    MaintenanceWindow::factory()->upcoming()->for($site)->create(['started_notified_at' => null]);

    $this->artisan('maintenance:start-scheduled');

    Event::assertNotDispatched(MaintenanceStarted::class);
});

it('dispatches for overdue windows that have not been notified yet', function (): void {
    Event::fake();

    $site = Site::factory()->published()->create();
    MaintenanceWindow::factory()->expired()->for($site)->create([
        'started_notified_at' => null,
        'completed_at' => null,
    ]);

    $this->artisan('maintenance:start-scheduled');

    Event::assertDispatched(MaintenanceStarted::class);
});
