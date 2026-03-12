<?php

declare(strict_types=1);

use App\Http\Controllers\Sites\ComponentController;
use App\Http\Controllers\Sites\ComponentStatusController;
use App\Http\Controllers\Sites\IncidentController;
use App\Http\Controllers\Sites\IncidentUpdateController;
use App\Http\Controllers\Sites\SiteController;
use Illuminate\Support\Facades\Route;

Route::scopeBindings()
    ->middleware(['auth', 'verified', 'two-factor-confirmed'])
    ->prefix('dashboard/sites')
    ->name('sites.')
    ->group(function (): void {
        Route::get('/', [SiteController::class, 'index'])->name('index');
        Route::get('create', [SiteController::class, 'create'])->name('create');
        Route::post('/', [SiteController::class, 'store'])->name('store');
        Route::get('{site}', [SiteController::class, 'show'])->name('show');
        Route::get('{site}/edit', [SiteController::class, 'edit'])->name('edit');
        Route::put('{site}', [SiteController::class, 'update'])->name('update');
        Route::delete('{site}', [SiteController::class, 'destroy'])->name('destroy');

        Route::get('{site}/components/create', [ComponentController::class, 'create'])->name('components.create');
        Route::post('{site}/components', [ComponentController::class, 'store'])->name('components.store');
        Route::get('{site}/components/{component}/edit', [ComponentController::class, 'edit'])->name('components.edit');
        Route::put('{site}/components/{component}', [ComponentController::class, 'update'])->name('components.update');
        Route::delete('{site}/components/{component}', [ComponentController::class, 'destroy'])->name('components.destroy');
        Route::put('{site}/components/{component}/status', ComponentStatusController::class)->name('components.status.update');

        Route::get('{site}/incidents', [IncidentController::class, 'index'])->name('incidents.index');
        Route::get('{site}/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
        Route::post('{site}/incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::get('{site}/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
        Route::get('{site}/incidents/{incident}/edit', [IncidentController::class, 'edit'])->name('incidents.edit');
        Route::put('{site}/incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
        Route::delete('{site}/incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
        Route::post('{site}/incidents/{incident}/updates', [IncidentUpdateController::class, 'store'])->name('incidents.updates.store');
        Route::post('{site}/incidents/{incident}/resolve', [IncidentUpdateController::class, 'resolve'])->name('incidents.resolve');
    });
