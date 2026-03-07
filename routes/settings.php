<?php

declare(strict_types=1);

use App\Http\Controllers\Settings\ChangePasswordController;
use App\Http\Controllers\Settings\DeleteAccountController;
use App\Http\Controllers\Settings\DisconnectSocialAccountController;
use App\Http\Controllers\Settings\PendingEmailController;
use App\Http\Controllers\Settings\UpdateProfileController;
use App\Http\Controllers\Settings\VerifyNewEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::put('settings/profile', [UpdateProfileController::class, 'update'])
        ->name('settings.profile.update');

    Route::put('settings/password', [ChangePasswordController::class, 'update'])
        ->name('settings.password.update');

    Route::delete('settings/account', [DeleteAccountController::class, 'destroy'])
        ->name('settings.account.destroy');

    Route::get('settings/email/verify/{token}', [VerifyNewEmailController::class, 'verify'])
        ->middleware('signed')
        ->name('settings.email.verify');

    Route::post('settings/email/resend', [PendingEmailController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('settings.email.resend');

    Route::delete('settings/email/pending', [PendingEmailController::class, 'destroy'])
        ->name('settings.email.destroy');

    Route::delete('settings/social-accounts/{socialAccount}', [DisconnectSocialAccountController::class, 'destroy'])
        ->name('settings.social-accounts.destroy');
});
