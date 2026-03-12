<?php

declare(strict_types=1);

use App\Http\Controllers\Api\PublicApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('sites/{slug}')
    ->middleware('throttle:60,1')
    ->group(function (): void {
        Route::get('status', [PublicApiController::class, 'status'])->name('api.sites.status');
        Route::get('incidents', [PublicApiController::class, 'incidents'])->name('api.sites.incidents');
        Route::get('incidents/{incident}', [PublicApiController::class, 'incident'])->name('api.sites.incidents.show');
        Route::get('maintenance', [PublicApiController::class, 'maintenance'])->name('api.sites.maintenance');
    });
