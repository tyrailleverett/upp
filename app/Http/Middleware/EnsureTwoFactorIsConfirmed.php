<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTwoFactorIsConfirmed
{
    /**
     * Cancel incomplete two-factor authentication setup when navigating away from security settings.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! is_null($user->two_factor_secret) && is_null($user->two_factor_confirmed_at)) {
            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
            ])->save();
        }

        return $next($request);
    }
}
