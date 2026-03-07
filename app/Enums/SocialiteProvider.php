<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialiteProvider: string
{
    case Google = 'google';

    public function label(): string
    {
        return match ($this) {
            self::Google => 'Google',
        };
    }
}
