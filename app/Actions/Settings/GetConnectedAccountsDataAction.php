<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Enums\SocialiteProvider;
use App\Models\User;

final class GetConnectedAccountsDataAction
{
    /**
     * Assemble connected accounts data for the given user.
     *
     * @return array{connectedAccounts: \Illuminate\Database\Eloquent\Collection, availableProviders: array<int, array{value: string, label: string}>, canDisconnect: bool}
     */
    public function execute(User $user): array
    {
        $connectedAccounts = $user->socialAccounts()
            ->get(['id', 'provider', 'email', 'created_at']);

        $availableProviders = array_map(
            fn (SocialiteProvider $provider): array => [
                'value' => $provider->value,
                'label' => $provider->label(),
            ],
            SocialiteProvider::cases(),
        );

        $canDisconnect = $user->hasPassword() || $user->socialAccounts()->count() > 1;

        return [
            'connectedAccounts' => $connectedAccounts,
            'availableProviders' => $availableProviders,
            'canDisconnect' => $canDisconnect,
        ];
    }
}
