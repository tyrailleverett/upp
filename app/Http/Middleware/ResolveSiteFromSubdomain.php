<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class ResolveSiteFromSubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $appDomain = config('app.domain', 'statuskit.app');

        if ($host === $appDomain) {
            abort(404);
        }

        $subdomain = str_replace('.'.$appDomain, '', $host);

        if ($subdomain === $host) {
            abort(404);
        }

        $site = Site::where('slug', $subdomain)->first();

        if ($site === null) {
            abort(404);
        }

        if ($site->isDraft()) {
            abort(404);
        }

        if ($site->isSuspended()) {
            return Inertia::render('status-page/suspended', ['site' => $site])
                ->toResponse($request);
        }

        $request->attributes->set('current.site', $site);
        app()->instance('current.site', $site);

        return $next($request);
    }
}
