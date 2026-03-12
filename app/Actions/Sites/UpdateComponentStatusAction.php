<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Enums\ComponentStatus;
use App\Models\Component;

final class UpdateComponentStatusAction
{
    public function execute(Component $component, ComponentStatus $status): Component
    {
        $component->update(['status' => $status]);
        $component->logStatusChange();

        return $component->refresh();
    }
}
