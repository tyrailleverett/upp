<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Enums\SiteVisibility;
use App\Models\Site;
use App\Models\User;

final class CreateSiteAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): Site
    {
        return $user->sites()->create(array_merge([
            'visibility' => SiteVisibility::Draft,
        ], $data));
    }
}
