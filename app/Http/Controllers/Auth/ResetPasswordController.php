<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

final class ResetPasswordController extends Controller
{
    public function show(Request $request): Response
    {
        return Inertia::render('auth/reset-password', [
            'token' => $request->route('token'),
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(ResetPasswordRequest $request, ResetPasswordAction $action): RedirectResponse
    {
        $status = $action->execute($request->validated());

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        Inertia::flash('success', __($status));

        return redirect()->route('login');
    }
}
