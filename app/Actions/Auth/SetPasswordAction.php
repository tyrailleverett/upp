<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

final class SetPasswordAction
{
    public function execute(User $user, string $password): void
    {
        $user->update([
            'password' => $password,
        ]);
    }
}
