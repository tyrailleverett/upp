<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class SupportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', SupportTicket::class);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $page = max((int) $request->integer('page', 1), 1);
        $cacheVersion = Cache::get("user:{$user->id}:support-tickets:version", 0);

        $tickets = Cache::remember(
            "user:{$user->id}:support-tickets:v{$cacheVersion}:page:{$page}",
            now()->addMinutes(30),
            fn () => $user->supportTickets()
                ->latest()
                ->paginate(10, ['id', 'title', 'description', 'topic', 'resolution', 'created_at']),
        );

        $tickets->withQueryString();

        return Inertia::render('dashboard/settings/support', [
            'tickets' => $tickets,
        ]);
    }
}
