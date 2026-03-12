<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\MaintenanceStarted;
use App\Models\MaintenanceWindow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

final class StartScheduledMaintenanceCommand extends Command
{
    protected $signature = 'maintenance:start-scheduled';

    protected $description = 'Dispatch events for maintenance windows that have just started';

    public function handle(): int
    {
        $windows = MaintenanceWindow::query()
            ->where('scheduled_at', '<=', now())
            ->whereNull('completed_at')
            ->whereNull('started_notified_at')
            ->with(['site', 'components'])
            ->get();

        $count = 0;

        foreach ($windows as $window) {
            MaintenanceStarted::dispatch($window);

            $window->update(['started_notified_at' => now()]);

            $count++;
        }

        Log::info('Dispatched MaintenanceStarted events.', ['count' => $count]);

        $this->info("Dispatched {$count} maintenance started event(s).");

        return self::SUCCESS;
    }
}
