<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\AddIncidentUpdateAction;
use App\Actions\Sites\ResolveIncidentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\ResolveIncidentRequest;
use App\Http\Requests\Sites\StoreIncidentUpdateRequest;
use App\Models\Incident;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class IncidentUpdateController extends Controller
{
    public function store(StoreIncidentUpdateRequest $request, Site $site, Incident $incident, AddIncidentUpdateAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($incident, $request->validated());

        Inertia::flash('success', __('Update posted successfully.'));

        return back();
    }

    public function resolve(ResolveIncidentRequest $request, Site $site, Incident $incident, ResolveIncidentAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($incident, $request->validated());

        Inertia::flash('success', __('Incident resolved successfully.'));

        return redirect()->route('sites.incidents.show', [$site, $incident]);
    }
}
