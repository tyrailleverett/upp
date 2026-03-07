<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Password;

final class SendPasswordResetLinkAction
{
    /**
     * @param  array{email: string}  $credentials
     */
    public function execute(array $credentials): string
    {
        return Password::sendResetLink($credentials);
    }
}
