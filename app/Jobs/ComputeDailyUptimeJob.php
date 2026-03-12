<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Site;
use App\Services\UptimeCalculationService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ComputeDailyUptimeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public function __construct(
        public readonly int $siteId,
        public readonly string $date,
    ) {}

    public function handle(UptimeCalculationService $service): void
    {
        $site = Site::findOrFail($this->siteId);

        $service->computeForSite($site, Carbon::parse($this->date));
    }

    public function failed(Throwable $e): void
    {
        Log::error('ComputeDailyUptimeJob failed', [
            'site_id' => $this->siteId,
            'date' => $this->date,
            'error' => $e->getMessage(),
        ]);
    }
}
