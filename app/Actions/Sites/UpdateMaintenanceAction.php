<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\MaintenanceWindow;

final class UpdateMaintenanceAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(MaintenanceWindow $window, array $data): MaintenanceWindow
    {
        $window->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'ends_at' => $data['ends_at'],
        ]);

        $window->components()->sync($data['component_ids']);

        return $window->refresh();
    }
}
