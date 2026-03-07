<?php

declare(strict_types=1);

use App\Http\Controllers\Dev\MailPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('dev/mail', [MailPreviewController::class, 'index'])->name('dev.mail.index');
Route::get('dev/mail/{mailable}', [MailPreviewController::class, 'show'])->name('dev.mail.show');
