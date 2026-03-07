<?php

declare(strict_types=1);

use App\Http\Controllers\Support\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('support/tickets', [SupportTicketController::class, 'index'])
        ->name('support.tickets.index');

    Route::post('support/tickets', [SupportTicketController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('support.tickets.store');
});
