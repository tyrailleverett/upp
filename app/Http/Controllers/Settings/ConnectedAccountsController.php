<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\GetConnectedAccountsDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ConnectedAccountsController extends Controller
{
    public function __invoke(Request $request, GetConnectedAccountsDataAction $action): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return Inertia::render('dashboard/settings/connected-accounts', $action->execute($user));
    }
}
