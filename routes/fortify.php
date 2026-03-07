<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\TwoFactorSecretKeyController;

Route::middleware('web')->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Challenge (Guest)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['guest:web'])->group(function (): void {
        Route::post('two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:two-factor')
            ->name('two-factor.login.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Management (Authenticated)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['auth:web'])->group(function (): void {
        Route::post('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
            ->name('two-factor.enable');

        Route::delete('user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
            ->name('two-factor.disable');

        Route::get('user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->name('two-factor.qr-code');

        Route::get('user/two-factor-secret-key', [TwoFactorSecretKeyController::class, 'show'])
            ->name('two-factor.secret-key');

        Route::post('user/confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
            ->name('two-factor.confirm');

        Route::get('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
            ->name('two-factor.recovery-codes');

        Route::post('user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
            ->name('two-factor.regenerate-recovery-codes');
    });
});
