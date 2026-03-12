<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\CreateComponentAction;
use App\Actions\Sites\DeleteComponentAction;
use App\Actions\Sites\UpdateComponentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\StoreComponentRequest;
use App\Http\Requests\Sites\UpdateComponentRequest;
use App\Models\Component;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class ComponentController extends Controller
{
    public function create(Site $site): Response
    {
        Gate::authorize('update', $site);

        return Inertia::render('sites/components/create', ['site' => $site]);
    }

    public function store(StoreComponentRequest $request, Site $site, CreateComponentAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($site, $request->validated());

        Inertia::flash('success', __('Component created successfully.'));

        return redirect()->route('sites.show', $site);
    }

    public function edit(Site $site, Component $component): Response
    {
        Gate::authorize('update', $site);

        return Inertia::render('sites/components/edit', ['site' => $site, 'component' => $component]);
    }

    public function update(UpdateComponentRequest $request, Site $site, Component $component, UpdateComponentAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($component, $request->validated());

        Inertia::flash('success', __('Component updated successfully.'));

        return back();
    }

    public function destroy(Site $site, Component $component, DeleteComponentAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($component);

        Inertia::flash('success', __('Component deleted successfully.'));

        return redirect()->route('sites.show', $site);
    }
}
