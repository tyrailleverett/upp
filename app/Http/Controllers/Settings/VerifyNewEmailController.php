<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

final class VerifyNewEmailController extends Controller
{
    public function verify(string $token): RedirectResponse
    {
        $pendingUserEmail = PendingUserEmail::where('token', $token)->firstOrFail();

        $pendingUserEmail->activate();

        Inertia::flash('success', 'Email verified successfully.');

        return redirect()->route('login');
    }
}
