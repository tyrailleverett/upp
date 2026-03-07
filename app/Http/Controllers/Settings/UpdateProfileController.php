<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class UpdateProfileController extends Controller
{
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->execute($user, $request->validated());

        return Inertia::flash('success', 'Profile updated successfully.')->back();
    }
}
