<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class DeleteAccountAction
{
    public function execute(Request $request, User $user): void
    {
        $user->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
