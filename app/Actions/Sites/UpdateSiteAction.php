<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Site;

final class UpdateSiteAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Site $site, array $data): Site
    {
        $site->update($data);

        return $site->refresh();
    }
}
