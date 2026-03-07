<?php

declare(strict_types=1);

it('loads the forgot password page without JavaScript errors', function (): void {
    $page = visit('/forgot-password');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('shows the forgot password page content', function (): void {
    $page = visit('/forgot-password');

    $page->assertSee('Forgot your password?')
        ->assertSee('Enter your email')
        ->assertSee('Email')
        ->assertSee('Send reset link')
        ->assertSee('Back to log in');
});
