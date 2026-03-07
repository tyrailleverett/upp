<?php

declare(strict_types=1);

it('loads the reset password page without JavaScript errors', function (): void {
    $page = visit('/reset-password/test-token');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('shows the reset password page content', function (): void {
    $page = visit('/reset-password/test-token');

    $page->assertSee('Reset your password')
        ->assertSee('Enter your new password')
        ->assertSee('Email')
        ->assertSee('New password')
        ->assertSee('Confirm password')
        ->assertSee('Reset password');
});
