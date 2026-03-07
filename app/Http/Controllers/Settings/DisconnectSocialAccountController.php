<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\DisconnectSocialAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\DisconnectSocialAccountRequest;
use App\Models\SocialAccount;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class DisconnectSocialAccountController extends Controller
{
    public function destroy(DisconnectSocialAccountRequest $request, SocialAccount $socialAccount, DisconnectSocialAccountAction $action): RedirectResponse
    {
        $action->execute($socialAccount);

        Inertia::flash('success', 'Account disconnected successfully.');

        return back();
    }
}
