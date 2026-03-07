<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;

final class RegisterUserAction
{
    /**
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function execute(array $data): User
    {
        $user = User::create($data);

        event(new Registered($user));

        return $user;
    }
}
