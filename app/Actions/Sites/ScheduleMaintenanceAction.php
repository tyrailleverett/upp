<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\MaintenanceWindow;
use App\Models\Site;

final class ScheduleMaintenanceAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Site $site, array $data): MaintenanceWindow
    {
        $window = $site->maintenanceWindows()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'ends_at' => $data['ends_at'],
        ]);

        $window->components()->attach($data['component_ids']);

        return $window;
    }
}
