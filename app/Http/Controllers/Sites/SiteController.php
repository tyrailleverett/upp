<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\CreateSiteAction;
use App\Actions\Sites\DeleteSiteAction;
use App\Actions\Sites\UpdateSiteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\StoreSiteRequest;
use App\Http\Requests\Sites\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class SiteController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Site::class);

        $sites = $request->user()
            ->sites()
            ->withCount('components')
            ->latest()
            ->get();

        return Inertia::render('sites/index', ['sites' => $sites]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Site::class);

        return Inertia::render('sites/create');
    }

    public function store(StoreSiteRequest $request, CreateSiteAction $action): RedirectResponse
    {
        Gate::authorize('create', Site::class);

        $site = $action->execute($request->user(), $request->validated());

        Inertia::flash('success', __('Site created successfully.'));

        return redirect()->route('sites.show', $site);
    }

    public function show(Site $site): Response
    {
        Gate::authorize('view', $site);

        $site->load(['components' => fn ($query) => $query->orderBy('sort_order')]);

        return Inertia::render('sites/show', ['site' => $site]);
    }

    public function edit(Site $site): Response
    {
        Gate::authorize('update', $site);

        return Inertia::render('sites/edit', ['site' => $site]);
    }

    public function update(UpdateSiteRequest $request, Site $site, UpdateSiteAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $action->execute($site, $request->validated());

        Inertia::flash('success', __('Site updated successfully.'));

        return back();
    }

    public function destroy(Site $site, DeleteSiteAction $action): RedirectResponse
    {
        Gate::authorize('delete', $site);

        $action->execute($site);

        Inertia::flash('success', __('Site deleted successfully.'));

        return redirect()->route('sites.index');
    }
}
