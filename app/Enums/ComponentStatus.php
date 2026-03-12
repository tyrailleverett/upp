<?php

declare(strict_types=1);

namespace App\Enums;

enum ComponentStatus: string
{
    case Operational = 'operational';
    case DegradedPerformance = 'degraded_performance';
    case PartialOutage = 'partial_outage';
    case MajorOutage = 'major_outage';
    case UnderMaintenance = 'under_maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Operational => 'Operational',
            self::DegradedPerformance => 'Degraded Performance',
            self::PartialOutage => 'Partial Outage',
            self::MajorOutage => 'Major Outage',
            self::UnderMaintenance => 'Under Maintenance',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Operational => 'green',
            self::DegradedPerformance => 'yellow',
            self::PartialOutage => 'orange',
            self::MajorOutage => 'red',
            self::UnderMaintenance => 'blue',
        };
    }

    public function severity(): int
    {
        return match ($this) {
            self::Operational => 0,
            self::DegradedPerformance => 1,
            self::PartialOutage => 2,
            self::UnderMaintenance => 3,
            self::MajorOutage => 4,
        };
    }
}
