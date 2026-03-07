<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final class ChangePasswordAction
{
    public function execute(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }
}
