<?php

declare(strict_types=1);

namespace App\Http\Controllers\Support;

use App\Actions\Support\CreateSupportTicketAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Support\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

final class SupportTicketController extends Controller
{
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', SupportTicket::class);

        /** @var \App\Models\User $user */
        $user = request()->user();

        $tickets = Cache::remember(
            "user:{$user->id}:support-tickets",
            now()->addMinutes(30),
            fn () => $user->supportTickets()
                ->latest()
                ->get(['title', 'description', 'topic', 'resolution', 'created_at']),
        );

        return response()->json($tickets);
    }

    public function store(StoreSupportTicketRequest $request, CreateSupportTicketAction $action): RedirectResponse
    {
        Gate::authorize('create', SupportTicket::class);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->execute($user, $request->validated());

        Inertia::flash('success', __('Support ticket submitted.'));

        return back();
    }
}
