<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\MaintenanceWindow;
use App\Models\Site;

final class EffectiveStatusService
{
    public function resolveComponentStatus(Component $component): ComponentStatus
    {
        $baseStatus = $component->status;

        if ($baseStatus !== ComponentStatus::Operational) {
            return $baseStatus;
        }

        $hasActiveMaintenance = MaintenanceWindow::query()
            ->active()
            ->whereHas('components', fn ($q) => $q->where('components.id', $component->id))
            ->exists();

        if ($hasActiveMaintenance) {
            return ComponentStatus::UnderMaintenance;
        }

        return ComponentStatus::Operational;
    }

    /**
     * @return array<int, ComponentStatus>
     */
    public function resolveAllComponentStatuses(Site $site): array
    {
        $components = $site->components()->get();

        $activeWindowComponentIds = MaintenanceWindow::query()
            ->active()
            ->whereHas('site', fn ($q) => $q->where('sites.id', $site->id))
            ->with('components:id')
            ->get()
            ->flatMap(fn (MaintenanceWindow $window) => $window->components->pluck('id'))
            ->unique()
            ->flip()
            ->all();

        $statuses = [];

        foreach ($components as $component) {
            $baseStatus = $component->status;

            if ($baseStatus !== ComponentStatus::Operational) {
                $statuses[$component->id] = $baseStatus;
            } elseif (isset($activeWindowComponentIds[$component->id])) {
                $statuses[$component->id] = ComponentStatus::UnderMaintenance;
            } else {
                $statuses[$component->id] = ComponentStatus::Operational;
            }
        }

        return $statuses;
    }

    public function resolveOverallSiteStatus(Site $site): ComponentStatus
    {
        $statuses = $this->resolveAllComponentStatuses($site);

        if ($statuses === []) {
            return ComponentStatus::Operational;
        }

        return array_reduce(
            $statuses,
            function (ComponentStatus $worst, ComponentStatus $current): ComponentStatus {
                return $current->severity() > $worst->severity() ? $current : $worst;
            },
            ComponentStatus::Operational,
        );
    }
}
