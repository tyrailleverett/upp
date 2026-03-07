<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\ConnectedAccountsController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\Settings\SupportController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'two-factor-confirmed'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('dashboard/settings')->group(function (): void {
    Route::redirect('/', 'dashboard/settings/profile')->name('settings');

    Route::middleware('two-factor-confirmed')->group(function (): void {
        Route::get('profile', ProfileController::class)->name('settings.profile');
        Route::get('appearance', AppearanceController::class)->name('settings.appearance');
        Route::get('connected-accounts', ConnectedAccountsController::class)->name('settings.connected-accounts');
        Route::get('support', SupportController::class)->name('settings.support');
    });

    Route::get('security', SecurityController::class)->name('settings.security');
});

require __DIR__.'/auth.php';
require __DIR__.'/settings.php';
require __DIR__.'/support.php';
require __DIR__.'/feedback.php';
