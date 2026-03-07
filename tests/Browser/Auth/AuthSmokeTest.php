<?php

declare(strict_types=1);

it('passes smoke test on all guest auth pages', function (): void {
    $pages = visit(['/login', '/register', '/forgot-password', '/reset-password/test-token']);

    $pages->assertNoSmoke();
});
