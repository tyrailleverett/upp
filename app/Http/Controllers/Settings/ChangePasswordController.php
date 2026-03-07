<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\ChangePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ChangePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class ChangePasswordController extends Controller
{
    public function update(ChangePasswordRequest $request, ChangePasswordAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->execute($user, $request->validated('password'));

        return Inertia::flash('success', 'Password changed successfully.')->back();
    }
}
