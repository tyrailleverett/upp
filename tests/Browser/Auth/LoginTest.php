<?php

declare(strict_types=1);

it('loads the login page without JavaScript errors', function (): void {
    $page = visit('/login');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('shows the login page content', function (): void {
    $page = visit('/login');

    $page->assertSee('Welcome to')
        ->assertSee('Continue with Google')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Remember me')
        ->assertSee('Log in')
        ->assertSee('Sign up')
        ->assertSee('Forgot your password?')
        ->assertSee('Terms of Service')
        ->assertSee('Privacy Policy');
});
