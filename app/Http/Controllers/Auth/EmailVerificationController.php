<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|Response
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        return Inertia::render('auth/verify-email', [
            'status' => session('status'),
        ]);
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->intended(route('dashboard'));
    }

    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()?->sendEmailVerificationNotification();

        return Inertia::flash('success', 'Verification link sent.')->back();
    }
}
