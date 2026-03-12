<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Enums\ComponentStatus;
use App\Events\ComponentStatusChanged;
use App\Models\Component;

final class UpdateComponentStatusAction
{
    public function execute(Component $component, ComponentStatus $status): Component
    {
        $previousStatus = $component->status;

        $component->update(['status' => $status]);
        $component->logStatusChange();

        $component->load('site');

        ComponentStatusChanged::dispatch($component, $previousStatus);

        return $component->refresh();
    }
}
