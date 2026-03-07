<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\User;

final class UpdateProfileAction
{
    /**
     * @param  array{name: string, email: string}  $data
     */
    public function execute(User $user, array $data): void
    {
        $user->update([
            'name' => $data['name'],
        ]);

        if ($user->email !== $data['email']) {
            $user->newEmail($data['email']);
        }
    }
}
