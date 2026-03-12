<?php

declare(strict_types=1);

use App\Jobs\ComputeDailyUptimeJob;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;

it('dispatches jobs for all published sites', function (): void {
    Queue::fake();

    Site::factory()->published()->count(3)->create();
    Site::factory()->count(2)->create(); // draft sites, should be excluded

    $this->artisan('uptime:compute-daily', ['--date' => Carbon::yesterday()->toDateString()]);

    Queue::assertPushed(ComputeDailyUptimeJob::class, 3);
});

it('uses yesterday as default date', function (): void {
    Queue::fake();

    $site = Site::factory()->published()->create();
    $yesterday = Carbon::yesterday()->toDateString();

    $this->artisan('uptime:compute-daily');

    Queue::assertPushed(ComputeDailyUptimeJob::class, function (ComputeDailyUptimeJob $job) use ($site, $yesterday): bool {
        return $job->siteId === $site->id && $job->date === $yesterday;
    });
});

it('accepts a custom date option', function (): void {
    Queue::fake();

    $site = Site::factory()->published()->create();
    $customDate = '2026-01-15';

    $this->artisan('uptime:compute-daily', ['--date' => $customDate]);

    Queue::assertPushed(ComputeDailyUptimeJob::class, function (ComputeDailyUptimeJob $job) use ($site, $customDate): bool {
        return $job->siteId === $site->id && $job->date === $customDate;
    });
});
