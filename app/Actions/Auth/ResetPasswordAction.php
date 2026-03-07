<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class ResetPasswordAction
{
    /**
     * @param  array{token: string, email: string, password: string, password_confirmation: string}  $data
     */
    public function execute(array $data): string
    {
        return Password::reset($data, function ($user, string $password): void {
            $user->forceFill([
                'password' => $password,
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });
    }
}
