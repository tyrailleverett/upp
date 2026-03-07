<?php

declare(strict_types=1);

namespace App\Enums;

enum LoginResult
{
    case Failed;
    case Success;
    case TwoFactorRequired;
}
