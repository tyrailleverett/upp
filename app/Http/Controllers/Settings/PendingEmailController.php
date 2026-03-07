<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class PendingEmailController extends Controller
{
    public function resend(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->resendPendingEmailVerificationMail();

        return Inertia::flash('success', 'Verification email resent.')->back();
    }

    public function destroy(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->clearPendingEmail();

        return Inertia::flash('success', 'Pending email change cancelled.')->back();
    }
}
