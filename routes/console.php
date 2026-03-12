<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('disposable:update')->daily();
Schedule::command('analytics:finalize-sessions')->hourly();
Schedule::command('analytics:prune')->daily();
Schedule::command('maintenance:complete-expired')->everyMinute();
Schedule::command('maintenance:start-scheduled')->everyMinute();
Schedule::command('uptime:compute-daily')->dailyAt('00:15');
