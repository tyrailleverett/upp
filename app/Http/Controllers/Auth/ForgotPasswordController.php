<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

final class ForgotPasswordController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/forgot-password');
    }

    public function store(ForgotPasswordRequest $request, SendPasswordResetLinkAction $action): RedirectResponse
    {
        $status = $action->execute($request->validated());

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['email' => __($status)]);
        }

        return Inertia::flash('success', __($status))->back();
    }
}
