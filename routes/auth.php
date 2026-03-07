<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store'])
        ->middleware('throttle:login')
        ->name('login.store');

    Route::get('register', [RegisterController::class, 'show'])->name('register.show');
    Route::post('register', [RegisterController::class, 'store'])
        ->middleware('throttle:login')
        ->name('register.store');

    Route::get('two-factor-challenge', function () {
        if (! session('login.id')) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/two-factor-challenge');
    })->name('two-factor.login');

    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:login')
        ->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:login')
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function (): void {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::get('email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('set-password', [SetPasswordController::class, 'store'])
        ->name('password.set');
});

/*
|--------------------------------------------------------------------------
| Socialite Routes (No Auth Restriction)
|--------------------------------------------------------------------------
*/

Route::get('auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
    ->name('socialite.redirect');

Route::get('auth/{provider}/callback', [SocialiteController::class, 'callback'])
    ->name('socialite.callback');
