<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\DeleteAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\DeleteAccountRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class DeleteAccountController extends Controller
{
    public function destroy(DeleteAccountRequest $request, DeleteAccountAction $action): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action->execute($request, $user);

        Inertia::flash('success', 'Account deleted successfully.');

        return redirect()->route('login');
    }
}
