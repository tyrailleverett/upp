<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\SocialiteProvider;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;

final class HandleSocialiteCallbackAction
{
    public function execute(SocialiteProvider $provider, SocialiteUser $socialiteUser): User
    {
        $email = $socialiteUser->getEmail();

        if ($email === null || $email === '') {
            throw new RuntimeException('The OAuth provider did not return an email address.');
        }

        return DB::transaction(function () use ($provider, $socialiteUser, $email): User {
            $socialAccount = SocialAccount::where('provider', $provider->value)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($socialAccount) {
                $socialAccount->update([
                    'name' => $socialiteUser->getName(),
                    'email' => $email,
                    'avatar' => $socialiteUser->getAvatar(),
                    'token' => $socialiteUser->token,
                    'refresh_token' => $socialiteUser->refreshToken,
                    'token_expires_at' => $socialiteUser->expiresIn
                        ? now()->addSeconds($socialiteUser->expiresIn)
                        : null,
                ]);

                return $socialAccount->user;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $socialiteUser->getName(),
                    'avatar' => $socialiteUser->getAvatar(),
                ],
            );

            if ($user->wasRecentlyCreated) {
                $user->markEmailAsVerified();
            }

            $user->socialAccounts()->create([
                'provider' => $provider->value,
                'provider_id' => $socialiteUser->getId(),
                'name' => $socialiteUser->getName(),
                'email' => $email,
                'avatar' => $socialiteUser->getAvatar(),
                'token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken,
                'token_expires_at' => $socialiteUser->expiresIn
                    ? now()->addSeconds($socialiteUser->expiresIn)
                    : null,
            ]);

            return $user;
        });
    }
}
