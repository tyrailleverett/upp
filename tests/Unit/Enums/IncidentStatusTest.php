<?php

declare(strict_types=1);

use App\Enums\IncidentStatus;

it('has correct values for all cases', function (): void {
    expect(IncidentStatus::Investigating->value)->toBe('investigating');
    expect(IncidentStatus::Identified->value)->toBe('identified');
    expect(IncidentStatus::Monitoring->value)->toBe('monitoring');
    expect(IncidentStatus::Resolved->value)->toBe('resolved');
});

it('returns correct labels', function (): void {
    expect(IncidentStatus::Investigating->label())->toBe('Investigating');
    expect(IncidentStatus::Identified->label())->toBe('Identified');
    expect(IncidentStatus::Monitoring->label())->toBe('Monitoring');
    expect(IncidentStatus::Resolved->label())->toBe('Resolved');
});

it('returns correct colors', function (): void {
    expect(IncidentStatus::Investigating->color())->toBe('red');
    expect(IncidentStatus::Identified->color())->toBe('orange');
    expect(IncidentStatus::Monitoring->color())->toBe('yellow');
    expect(IncidentStatus::Resolved->color())->toBe('green');
});
