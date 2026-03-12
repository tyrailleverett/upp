<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\ComputeDailyUptimeJob;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class ComputeDailyUptimeCommand extends Command
{
    protected $signature = 'uptime:compute-daily {--date= : Date to compute, defaults to yesterday}';

    protected $description = 'Compute daily uptime rollups for all published sites';

    public function handle(): void
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $sites = Site::query()->published()->get();

        foreach ($sites as $site) {
            ComputeDailyUptimeJob::dispatch($site->id, $date->toDateString());
        }

        $this->info("Dispatched uptime compute jobs for {$sites->count()} sites for {$date->toDateString()}.");
    }
}
