<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Component;

final class DeleteComponentAction
{
    public function execute(Component $component): void
    {
        $component->delete();
    }
}
