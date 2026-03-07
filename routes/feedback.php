<?php

declare(strict_types=1);

use App\Http\Controllers\Feedback\DeletionFeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('feedback/account-deletion', [DeletionFeedbackController::class, 'create'])
    ->middleware('signed')
    ->name('feedback.account-deletion.create');

Route::post('feedback/account-deletion', [DeletionFeedbackController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('feedback.account-deletion.store');
