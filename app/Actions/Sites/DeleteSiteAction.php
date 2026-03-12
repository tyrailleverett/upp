<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Site;

final class DeleteSiteAction
{
    public function execute(Site $site): void
    {
        $site->delete();
    }
}
