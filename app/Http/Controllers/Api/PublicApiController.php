<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicIncidentResource;
use App\Http\Resources\PublicMaintenanceWindowResource;
use App\Http\Resources\PublicSiteStatusResource;
use App\Models\Incident;
use App\Models\Site;
use App\Services\EffectiveStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class PublicApiController extends Controller
{
    public function status(Request $request, string $slug, EffectiveStatusService $service): JsonResponse
    {
        $site = Site::query()
            ->published()
            ->where('slug', $slug)
            ->with(['components' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount([
                'incidents as active_incidents_count' => fn ($query) => $query->open(),
            ])
            ->firstOrFail();

        $openIncidents = $site->incidents()
            ->open()
            ->with(['updates' => fn ($query) => $query->orderBy('created_at', 'desc'), 'components'])
            ->orderBy('created_at', 'desc')
            ->get();

        $recentResolvedIncidents = $site->incidents()
            ->resolved()
            ->with(['updates' => fn ($query) => $query->orderBy('created_at', 'desc'), 'components'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $upcomingMaintenance = $site->maintenanceWindows()
            ->upcoming()
            ->with('components')
            ->orderBy('scheduled_at')
            ->get();

        $activeMaintenance = $site->maintenanceWindows()
            ->active()
            ->with('components')
            ->orderBy('scheduled_at')
            ->get();

        return (new PublicSiteStatusResource($site))
            ->additional([
                'effective_statuses' => $service->resolveAllComponentStatuses($site),
                'open_incidents' => PublicIncidentResource::collection($openIncidents)->resolve(),
                'incident_history' => PublicIncidentResource::collection(
                    $openIncidents->concat($recentResolvedIncidents)->sortByDesc('created_at')->values()
                )->resolve(),
                'upcoming_maintenance' => PublicMaintenanceWindowResource::collection($upcomingMaintenance)->resolve(),
                'active_maintenance' => PublicMaintenanceWindowResource::collection($activeMaintenance)->resolve(),
            ])
            ->response();
    }

    public function incidents(Request $request, string $slug): AnonymousResourceCollection
    {
        $site = Site::query()->published()->where('slug', $slug)->firstOrFail();

        $incidents = $site->incidents()
            ->with(['updates' => fn ($q) => $q->orderBy('created_at', 'desc'), 'components'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return PublicIncidentResource::collection($incidents);
    }

    public function incident(Request $request, string $slug, int $incidentId): JsonResponse
    {
        $site = Site::query()->published()->where('slug', $slug)->firstOrFail();

        $incident = Incident::query()
            ->where('site_id', $site->id)
            ->with(['updates' => fn ($q) => $q->orderBy('created_at', 'desc'), 'components'])
            ->findOrFail($incidentId);

        return (new PublicIncidentResource($incident))->response();
    }

    public function maintenance(Request $request, string $slug): AnonymousResourceCollection
    {
        $site = Site::query()->published()->where('slug', $slug)->firstOrFail();

        $windows = $site->maintenanceWindows()
            ->where(function ($query): void {
                $query->upcoming()->orWhere(fn ($q) => $q->active());
            })
            ->with('components')
            ->orderBy('scheduled_at')
            ->get();

        return PublicMaintenanceWindowResource::collection($windows);
    }
}
