<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Enums\LoginResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class LoginController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/login');
    }

    public function store(LoginRequest $request, LoginUserAction $action): RedirectResponse
    {
        $validated = $request->validated();

        $result = $action->execute(
            $request,
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
            ],
            $validated['remember'] ?? false,
        );

        return match ($result) {
            LoginResult::TwoFactorRequired => redirect('/two-factor-challenge'),
            LoginResult::Success => redirect()->intended(route('dashboard')),
            LoginResult::Failed => back()->withErrors([
                'email' => __('auth.failed'),
            ])->onlyInput('email'),
        };
    }
}
