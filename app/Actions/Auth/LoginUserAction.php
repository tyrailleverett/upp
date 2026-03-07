<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\LoginResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class LoginUserAction
{
    /**
     * @param  array{email: string, password: string}  $credentials
     */
    public function execute(Request $request, array $credentials, bool $remember = false): LoginResult
    {
        if (! Auth::attempt($credentials, $remember)) {
            return LoginResult::Failed;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasEnabledTwoFactorAuthentication()) {
            Auth::logout();

            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $remember,
            ]);

            return LoginResult::TwoFactorRequired;
        }

        $request->session()->regenerate();

        return LoginResult::Success;
    }
}
