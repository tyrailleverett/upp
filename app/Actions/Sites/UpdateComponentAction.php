<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Component;
use Illuminate\Support\Arr;

final class UpdateComponentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Component $component, array $data): Component
    {
        $component->update(Arr::only($data, [
            'name',
            'description',
            'group',
            'sort_order',
        ]));

        return $component->refresh();
    }
}
