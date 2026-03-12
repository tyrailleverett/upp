<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\CreateIncidentAction;
use App\Actions\Sites\UpdateIncidentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\StoreIncidentRequest;
use App\Http\Requests\Sites\UpdateIncidentRequest;
use App\Models\Incident;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class IncidentController extends Controller
{
    public function index(Site $site): Response
    {
        Gate::authorize('view', $site);

        $incidents = $site->incidents()
            ->with([
                'updates' => fn ($query) => $query->orderBy('created_at', 'desc'),
                'components',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('sites/incidents/index', [
            'site' => $site,
            'incidents' => $incidents,
        ]);
    }

    public function create(Site $site): Response
    {
        Gate::authorize('update', $site);

        $components = $site->components()->get();

        return Inertia::render('sites/incidents/create', [
            'site' => $site,
            'components' => $components,
        ]);
    }

    public function store(StoreIncidentRequest $request, Site $site, CreateIncidentAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $incident = $action->execute($site, $request->validated());

        Inertia::flash('success', __('Incident reported successfully.'));

        return redirect()->route('sites.incidents.show', [$site, $incident]);
    }

    public function show(Site $site, Incident $incident): Response
    {
        Gate::authorize('view', $site);

        $incident->load([
            'updates' => fn ($query) => $query->orderBy('created_at', 'desc'),
            'components',
        ]);

        $siteComponents = $site->components()->get();

        return Inertia::render('sites/incidents/show', [
            'site' => $site,
            'incident' => $incident,
            'siteComponents' => $siteComponents,
        ]);
    }

    public function edit(Site $site, Incident $incident): Response
    {
        Gate::authorize('update', $site);

        $components = $site->components()->get();

        return Inertia::render('sites/incidents/edit', [
            'site' => $site,
            'incident' => $incident,
            'components' => $components,
        ]);
    }

    public function update(UpdateIncidentRequest $request, Site $site, Incident $incident, UpdateIncidentAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($incident, $request->validated());

        Inertia::flash('success', __('Incident updated successfully.'));

        return back();
    }

    public function destroy(Site $site, Incident $incident): RedirectResponse
    {
        Gate::authorize('update', $site);

        $incident->delete();

        Inertia::flash('success', __('Incident deleted successfully.'));

        return redirect()->route('sites.incidents.index', $site);
    }
}
