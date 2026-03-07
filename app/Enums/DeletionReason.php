<?php

declare(strict_types=1);

namespace App\Enums;

enum DeletionReason: string
{
    case TooExpensive = 'too_expensive';
    case NotUseful = 'not_useful';
    case FoundAlternative = 'found_alternative';
    case PrivacyConcerns = 'privacy_concerns';
    case TooComplex = 'too_complex';
    case MissingFeatures = 'missing_features';
    case Other = 'other';
}
