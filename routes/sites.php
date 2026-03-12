<?php

declare(strict_types=1);

use App\Http\Controllers\Sites\ComponentController;
use App\Http\Controllers\Sites\ComponentStatusController;
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
    });
