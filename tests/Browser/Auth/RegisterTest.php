<?php

declare(strict_types=1);

it('loads the register page without JavaScript errors', function (): void {
    $page = visit('/register');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('shows the register page content', function (): void {
    $page = visit('/register');

    $page->assertSee('Create an account')
        ->assertSee('Continue with Google')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertSee('Register')
        ->assertSee('Log in')
        ->assertSee('Terms of Service')
        ->assertSee('Privacy Policy');
});
