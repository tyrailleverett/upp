<?php

declare(strict_types=1);

it('loads the homepage without JavaScript errors', function (): void {
    $page = visit('/');

    $page->assertNoJavaScriptErrors()
        ->assertNoConsoleLogs();
});

it('shows the home page navigation', function (): void {
    $page = visit('/');

    $page->assertSee('Features')
        ->assertSee('Pricing')
        ->assertSee('FAQ')
        ->assertSee('Login')
        ->assertSee('Get Started for Free');
});
