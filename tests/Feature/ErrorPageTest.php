<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware('web')->group(function (): void {
        Route::get('/test-403', fn () => abort(403));
        Route::get('/test-404', fn () => abort(404));
        Route::get('/test-500', fn () => abort(500));
        Route::get('/test-503', fn () => abort(503));
    });
});

it('renders the error page for 403 in production', function (): void {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/test-403')
        ->assertStatus(403)
        ->assertInertia(fn ($page) => $page
            ->component('error-page')
            ->where('status', 403)
        );
});

it('renders the error page for 404 in production', function (): void {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/test-404')
        ->assertStatus(404)
        ->assertInertia(fn ($page) => $page
            ->component('error-page')
            ->where('status', 404)
        );
});

it('renders the error page for 500 in production', function (): void {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/test-500')
        ->assertStatus(500)
        ->assertInertia(fn ($page) => $page
            ->component('error-page')
            ->where('status', 500)
        );
});

it('renders the error page for 503 in production', function (): void {
    app()->detectEnvironment(fn () => 'production');

    $this->get('/test-503')
        ->assertStatus(503)
        ->assertInertia(fn ($page) => $page
            ->component('error-page')
            ->where('status', 503)
        );
});

it('does not render the error page in testing environment', function (): void {
    $this->get('/non-existent-url-that-does-not-exist')
        ->assertStatus(404)
        ->assertHeaderMissing('X-Inertia');
});
