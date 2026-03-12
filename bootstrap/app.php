<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        using: function (): void {
            // Domain-constrained routes MUST be registered before web routes
            // so they take priority in Laravel's first-match routing.
            Route::middleware(['web', 'resolve-site'])
                ->domain('{slug}.'.config('app.domain', 'statuskit.app'))
                ->group(base_path('routes/status-page.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            PreventRequestsDuringMaintenance::except('/up');

            Route::get('/up', function (): Response {
                Event::dispatch(new DiagnosingHealth);

                return response('');
            });

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            if (app()->environment('local', 'testing')) {
                Route::middleware('web')
                    ->group(base_path('routes/dev.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request): string => $request->session()->has('login.id')
                ? route('two-factor.login')
                : route('login'));

        $middleware->redirectUsersTo('/dashboard');

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'two-factor-confirmed' => App\Http\Middleware\EnsureTwoFactorIsConfirmed::class,
            'resolve-site' => App\Http\Middleware\ResolveSiteFromSubdomain::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->respond(function (SymfonyResponse $response, Throwable $exception, Request $request) {
            if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
                return Inertia::render('error-page', ['status' => $response->getStatusCode()])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->create();
