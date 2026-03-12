<?php

declare(strict_types=1);

namespace App\Http\Controllers\Sites;

use App\Actions\Sites\UpdateComponentStatusAction;
use App\Enums\ComponentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\UpdateComponentStatusRequest;
use App\Models\Component;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class ComponentStatusController extends Controller
{
    public function __invoke(UpdateComponentStatusRequest $request, Site $site, Component $component, UpdateComponentStatusAction $action): RedirectResponse
    {
        Gate::authorize('update', $site);

        $status = ComponentStatus::from($request->validated()['status']);
        $action->execute($component, $status);

        Inertia::flash('success', __('Component status updated successfully.'));

        return back();
    }
}
