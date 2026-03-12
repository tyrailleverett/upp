<?php

declare(strict_types=1);

namespace App\Http\Controllers\StatusPage;

use App\Http\Controllers\Controller;
use App\Models\ComponentDailyUptime;
use App\Models\Incident;
use App\Models\Site;
use App\Services\EffectiveStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class PublicStatusPageController extends Controller
{
    public function __invoke(Request $request, EffectiveStatusService $service): Response
    {
        /** @var Site $site */
        $site = app('current.site');

        $site->load(['components' => fn ($q) => $q->orderBy('sort_order')]);

        $effectiveStatuses = $service->resolveAllComponentStatuses($site);
        $overallStatus = $service->resolveOverallSiteStatus($site);

        $openIncidents = $site->incidents()
            ->open()
            ->with(['updates' => fn ($q) => $q->orderBy('created_at', 'desc'), 'components'])
            ->orderBy('created_at', 'desc')
            ->get();

        $recentResolvedIncidents = $site->incidents()
            ->resolved()
            ->with(['updates' => fn ($q) => $q->orderBy('created_at', 'desc'), 'components'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        /** @var Collection<int, Incident> $incidentHistory */
        $incidentHistory = $openIncidents
            ->concat($recentResolvedIncidents)
            ->sortByDesc('created_at')
            ->values();

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

        $componentIds = $site->components->pluck('id')->all();
        $ninetyDaysAgo = Carbon::now()->subDays(89)->toDateString();

        $uptimeRecords = ComponentDailyUptime::query()
            ->whereIn('component_id', $componentIds)
            ->where('date', '>=', $ninetyDaysAgo)
            ->orderBy('date')
            ->get();

        $uptimeHistory = $site->components->map(fn ($component) => [
            'component_id' => $component->id,
            'component_name' => $component->name,
            'days' => $uptimeRecords
                ->where('component_id', $component->id)
                ->map(fn ($record) => [
                    'date' => $record->date->toDateString(),
                    'uptime_percentage' => (float) $record->uptime_percentage,
                ])
                ->values(),
        ])->values();

        return Inertia::render('status-page/index', [
            'site' => [
                'name' => $site->name,
                'slug' => $site->slug,
                'description' => $site->description,
                'accent_color' => $site->accent_color,
                'logo_path' => $site->logo_path,
                'favicon_path' => $site->favicon_path,
                'meta_title' => $site->meta_title,
                'meta_description' => $site->meta_description,
                'custom_css' => $site->custom_css,
            ],
            'overall_status' => $overallStatus,
            'components' => $site->components,
            'effective_statuses' => $effectiveStatuses,
            'open_incidents' => $openIncidents,
            'incident_history' => $incidentHistory,
            'upcoming_maintenance' => $upcomingMaintenance,
            'active_maintenance' => $activeMaintenance,
            'uptime_history' => $uptimeHistory,
        ]);
    }
}
