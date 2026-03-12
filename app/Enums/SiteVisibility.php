<?php

declare(strict_types=1);

namespace App\Enums;

enum SiteVisibility: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Suspended = 'suspended';
}
