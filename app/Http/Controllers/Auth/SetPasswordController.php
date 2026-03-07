<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SetPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SetPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class SetPasswordController extends Controller
{
    public function store(SetPasswordRequest $request, SetPasswordAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->execute($user, $request->validated('password'));

        return Inertia::flash('success', 'Password set successfully.')->back();
    }
}
