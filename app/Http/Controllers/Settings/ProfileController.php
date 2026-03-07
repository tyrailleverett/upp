<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

final class ProfileController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $pendingEmail = PendingUserEmail::query()
            ->where('user_type', $user->getMorphClass())
            ->where('user_id', $user->id)
            ->value('email');

        return Inertia::render('dashboard/settings/profile', [
            'pendingEmail' => $pendingEmail,
        ]);
    }
}
