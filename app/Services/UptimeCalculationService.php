<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentDailyUptime;
use App\Models\ComponentStatusLog;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

final class UptimeCalculationService
{
    public function computeForDate(Component $component, Carbon $date): ComponentDailyUptime
    {
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();
        $totalMinutes = 1440;
        $maintenanceIntervals = $this->maintenanceIntervalsForDay($component, $dayStart, $dayEnd);

        /** @var Collection<int, ComponentStatusLog> $logs */
        $logs = $component->statusLogs()
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at')
            ->get();

        // Determine starting status (last log before this day, or operational as default)
        if ($logs->isEmpty()) {
            /** @var ComponentStatusLog|null $previousLog */
            $previousLog = $component->statusLogs()
                ->where('created_at', '<', $dayStart)
                ->orderByDesc('created_at')
                ->first();

            $startingStatus = $previousLog?->status ?? ComponentStatus::Operational;

            $logs = collect([
                (object) ['status' => $startingStatus, 'created_at' => $dayStart->copy()],
            ]);
        } else {
            /** @var ComponentStatusLog|null $previousLog */
            $previousLog = $component->statusLogs()
                ->where('created_at', '<', $dayStart)
                ->orderByDesc('created_at')
                ->first();

            $startingStatus = $previousLog?->status ?? ComponentStatus::Operational;

            // Prepend a synthetic entry at day start
            $syntheticEntry = (object) ['status' => $startingStatus, 'created_at' => $dayStart->copy()];
            $logs = collect([$syntheticEntry])->merge($logs);
        }

        // Build time segments
        $operationalSeconds = 0;
        $logItems = $logs->values();

        for ($i = 0; $i < $logItems->count(); $i++) {
            $current = $logItems->get($i);
            $next = $logItems->get($i + 1);

            $segmentStart = max(
                $this->toTimestamp($current->created_at),
                $dayStart->timestamp
            );
            $segmentEnd = $next !== null
                ? min($this->toTimestamp($next->created_at), $dayEnd->timestamp)
                : $dayEnd->timestamp;

            if ($segmentEnd <= $segmentStart) {
                continue;
            }

            $segmentSeconds = $segmentEnd - $segmentStart;
            $statusValue = $current->status instanceof ComponentStatus
                ? $current->status->value
                : (string) $current->status;

            if ($statusValue !== ComponentStatus::Operational->value) {
                continue;
            }

            $maintenanceOverlapSeconds = $this->overlapSecondsForInterval(
                $segmentStart,
                $segmentEnd,
                $maintenanceIntervals,
            );

            $operationalSeconds += max(0, $segmentSeconds - $maintenanceOverlapSeconds);
        }

        $minutesOperational = (int) round($operationalSeconds / 60);

        // Calculate maintenance exclusion time
        $minutesExcludedForMaintenance = (int) round($this->intervalsDurationInSeconds($maintenanceIntervals) / 60);

        $denominator = $totalMinutes - $minutesExcludedForMaintenance;

        if ($denominator <= 0) {
            $uptimePercentage = 100.00;
        } else {
            $uptimePercentage = round(($minutesOperational / $denominator) * 100, 2);
            $uptimePercentage = min(100.00, max(0.00, $uptimePercentage));
        }

        /** @var ComponentDailyUptime */
        return ComponentDailyUptime::updateOrCreate(
            [
                'component_id' => $component->id,
                'date' => $date->toDateString(),
            ],
            [
                'uptime_percentage' => $uptimePercentage,
                'minutes_operational' => $minutesOperational,
                'minutes_excluded_for_maintenance' => $minutesExcludedForMaintenance,
            ]
        );
    }

    public function computeForSite(Site $site, Carbon $date): void
    {
        $components = $site->components()->get();

        foreach ($components as $component) {
            $this->computeForDate($component, $date);
        }
    }

    /**
     * @return array<int, array{start: int, end: int}>
     */
    private function maintenanceIntervalsForDay(Component $component, Carbon $dayStart, Carbon $dayEnd): array
    {
        /** @var Collection<int, MaintenanceWindow> $windows */
        $windows = MaintenanceWindow::query()
            ->whereHas('components', fn ($q) => $q->where('components.id', $component->id))
            ->where('scheduled_at', '<', $dayEnd)
            ->where(function ($query) use ($dayStart): void {
                $query->where('ends_at', '>', $dayStart)
                    ->orWhereNotNull('completed_at');
            })
            ->get();

        $intervals = [];

        foreach ($windows as $window) {
            $windowStart = max($window->scheduled_at->timestamp, $dayStart->timestamp);
            $windowEnd = $window->completed_at !== null
                ? min($window->completed_at->timestamp, $dayEnd->timestamp)
                : min($window->ends_at->timestamp, $dayEnd->timestamp);

            if ($windowEnd > $windowStart) {
                $intervals[] = ['start' => $windowStart, 'end' => $windowEnd];
            }
        }

        return $this->mergeIntervals($intervals);
    }

    /**
     * @param  array<int, array{start: int, end: int}>  $intervals
     * @return array<int, array{start: int, end: int}>
     */
    private function mergeIntervals(array $intervals): array
    {
        if ($intervals === []) {
            return [];
        }

        usort($intervals, fn (array $left, array $right): int => $left['start'] <=> $right['start']);

        $merged = [$intervals[0]];

        foreach (array_slice($intervals, 1) as $interval) {
            $lastIndex = count($merged) - 1;
            $lastInterval = $merged[$lastIndex];

            if ($interval['start'] <= $lastInterval['end']) {
                $merged[$lastIndex]['end'] = max($lastInterval['end'], $interval['end']);

                continue;
            }

            $merged[] = $interval;
        }

        return $merged;
    }

    /**
     * @param  array<int, array{start: int, end: int}>  $intervals
     */
    private function overlapSecondsForInterval(int $start, int $end, array $intervals): int
    {
        $seconds = 0;

        foreach ($intervals as $interval) {
            $overlapStart = max($start, $interval['start']);
            $overlapEnd = min($end, $interval['end']);

            if ($overlapEnd > $overlapStart) {
                $seconds += $overlapEnd - $overlapStart;
            }
        }

        return $seconds;
    }

    /**
     * @param  array<int, array{start: int, end: int}>  $intervals
     */
    private function intervalsDurationInSeconds(array $intervals): int
    {
        return array_reduce(
            $intervals,
            fn (int $carry, array $interval): int => $carry + ($interval['end'] - $interval['start']),
            0,
        );
    }

    private function toTimestamp(mixed $value): int
    {
        if ($value instanceof CarbonInterface) {
            return $value->timestamp;
        }

        return Carbon::parse($value)->timestamp;
    }
}
