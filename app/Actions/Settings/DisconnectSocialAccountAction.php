<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\SocialAccount;

final class DisconnectSocialAccountAction
{
    public function execute(SocialAccount $socialAccount): void
    {
        $socialAccount->delete();
    }
}
