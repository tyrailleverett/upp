<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\DeleteMaintenanceAction;
use App\Actions\Sites\ScheduleMaintenanceAction;
use App\Actions\Sites\UpdateMaintenanceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\StoreMaintenanceWindowRequest;
use App\Http\Requests\Sites\UpdateMaintenanceWindowRequest;
use App\Models\MaintenanceWindow;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class MaintenanceWindowController extends Controller
{
    public function index(Site $site): Response
    {
        Gate::authorize('view', $site);

        $active = $site->maintenanceWindows()
            ->active()
            ->with('components')
            ->orderBy('scheduled_at')
            ->get();

        $upcoming = $site->maintenanceWindows()
            ->upcoming()
            ->with('components')
            ->orderBy('scheduled_at')
            ->get();

        $completed = $site->maintenanceWindows()
            ->completed()
            ->with('components')
            ->orderBy('scheduled_at')
            ->get();

        return Inertia::render('sites/maintenance/index', [
            'site' => $site,
            'active' => $active,
            'upcoming' => $upcoming,
            'completed' => $completed,
        ]);
    }

    public function create(Site $site): Response
    {
        Gate::authorize('update', $site);

        $components = $site->components()->get();

        return Inertia::render('sites/maintenance/create', [
            'site' => $site,
            'components' => $components,
        ]);
    }

    public function store(StoreMaintenanceWindowRequest $request, Site $site, ScheduleMaintenanceAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($site, $request->validated());

        Inertia::flash('success', __('Maintenance window scheduled successfully.'));

        return redirect()->route('sites.maintenance.index', $site);
    }

    public function show(Site $site, MaintenanceWindow $maintenanceWindow): Response
    {
        Gate::authorize('view', $site);

        $maintenanceWindow->load('components');

        return Inertia::render('sites/maintenance/show', [
            'site' => $site,
            'maintenanceWindow' => $maintenanceWindow,
        ]);
    }

    public function edit(Site $site, MaintenanceWindow $maintenanceWindow): Response
    {
        Gate::authorize('update', $site);

        $maintenanceWindow->load('components');

        $components = $site->components()->get();

        return Inertia::render('sites/maintenance/edit', [
            'site' => $site,
            'maintenanceWindow' => $maintenanceWindow,
            'components' => $components,
        ]);
    }

    public function update(UpdateMaintenanceWindowRequest $request, Site $site, MaintenanceWindow $maintenanceWindow, UpdateMaintenanceAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $this->ensureUpcoming($maintenanceWindow);

        $action->execute($maintenanceWindow, $request->validated());

        Inertia::flash('success', __('Maintenance window updated successfully.'));

        return back();
    }

    public function destroy(Site $site, MaintenanceWindow $maintenanceWindow, DeleteMaintenanceAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $this->ensureUpcoming($maintenanceWindow);

        $action->execute($maintenanceWindow);

        Inertia::flash('success', __('Maintenance window deleted successfully.'));

        return redirect()->route('sites.maintenance.index', $site);
    }

    private function ensureUpcoming(MaintenanceWindow $maintenanceWindow): void
    {
        if (! $maintenanceWindow->isUpcoming()) {
            throw ValidationException::withMessages([
                'maintenance_window' => __('Only upcoming maintenance windows can be modified.'),
            ]);
        }
    }
}
