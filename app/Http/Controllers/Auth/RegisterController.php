<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class RegisterController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/register');
    }

    public function store(RegisterRequest $request, RegisterUserAction $action): RedirectResponse
    {
        $user = $action->execute($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
