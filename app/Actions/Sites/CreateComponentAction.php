<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Component;
use App\Models\Site;

final class CreateComponentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Site $site, array $data): Component
    {
        $component = $site->components()->create($data);
        $component->refresh();
        $component->logStatusChange();

        return $component;
    }
}
