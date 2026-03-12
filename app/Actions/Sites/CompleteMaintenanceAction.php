<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\MaintenanceWindow;
use Illuminate\Validation\ValidationException;

final class CompleteMaintenanceAction
{
    public function execute(MaintenanceWindow $window): MaintenanceWindow
    {
        if ($window->isCompleted() || ! $window->isActive()) {
            throw ValidationException::withMessages([
                'maintenance_window' => __('Only active maintenance windows can be completed.'),
            ]);
        }

        $window->completed_at = now();
        $window->save();

        return $window->refresh();
    }
}
