<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\CompleteMaintenanceAction;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class CompleteMaintenanceController extends Controller
{
    public function __invoke(Site $site, MaintenanceWindow $maintenanceWindow, CompleteMaintenanceAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($maintenanceWindow);

        Inertia::flash('success', __('Maintenance window completed successfully.'));

        return back();
    }
}
