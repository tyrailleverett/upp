<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\MaintenanceWindow;

final class DeleteMaintenanceAction
{
    public function execute(MaintenanceWindow $window): void
    {
        $window->delete();
    }
}
