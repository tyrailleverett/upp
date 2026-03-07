<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketTopic: string
{
    case General = 'general';
    case Technical = 'technical';
    case Account = 'account';
    case FeatureRequest = 'feature_request';
    case BugReport = 'bug_report';
}
