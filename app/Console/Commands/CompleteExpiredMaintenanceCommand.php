<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\MaintenanceWindow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CompleteExpiredMaintenanceCommand extends Command
{
    protected $signature = 'maintenance:complete-expired';

    protected $description = 'Complete maintenance windows that have passed their end time';

    public function handle(): int
    {
        $count = MaintenanceWindow::query()
            ->expired()
            ->update([
                'completed_at' => DB::raw('ends_at'),
            ]);

        Log::info('Completed expired maintenance windows.', [
            'count' => $count,
        ]);

        $this->info("Completed {$count} expired maintenance window(s).");

        return self::SUCCESS;
    }
}
