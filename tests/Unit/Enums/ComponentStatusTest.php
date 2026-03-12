<?php

declare(strict_types=1);

use App\Enums\ComponentStatus;

it('returns correct labels for all cases', function (): void {
    expect(ComponentStatus::Operational->label())->toBe('Operational');
    expect(ComponentStatus::DegradedPerformance->label())->toBe('Degraded Performance');
    expect(ComponentStatus::PartialOutage->label())->toBe('Partial Outage');
    expect(ComponentStatus::MajorOutage->label())->toBe('Major Outage');
    expect(ComponentStatus::UnderMaintenance->label())->toBe('Under Maintenance');
});

it('returns correct colors for all cases', function (): void {
    expect(ComponentStatus::Operational->color())->toBe('green');
    expect(ComponentStatus::DegradedPerformance->color())->toBe('yellow');
    expect(ComponentStatus::PartialOutage->color())->toBe('orange');
    expect(ComponentStatus::MajorOutage->color())->toBe('red');
    expect(ComponentStatus::UnderMaintenance->color())->toBe('blue');
});

it('returns correct severity ordering', function (): void {
    expect(ComponentStatus::Operational->severity())->toBe(0);
    expect(ComponentStatus::DegradedPerformance->severity())->toBe(1);
    expect(ComponentStatus::PartialOutage->severity())->toBe(2);
    expect(ComponentStatus::MajorOutage->severity())->toBe(4);
    expect(ComponentStatus::UnderMaintenance->severity())->toBe(3);
});
