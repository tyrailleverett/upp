<?php

declare(strict_types=1);

use App\Http\Controllers\StatusPage\PublicStatusPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicStatusPageController::class)->name('status-page.index');
