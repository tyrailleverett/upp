<?php

declare(strict_types=1);

use App\Enums\SiteVisibility;

it('has correct values for all cases', function (): void {
    expect(SiteVisibility::Draft->value)->toBe('draft');
    expect(SiteVisibility::Published->value)->toBe('published');
    expect(SiteVisibility::Suspended->value)->toBe('suspended');
});
