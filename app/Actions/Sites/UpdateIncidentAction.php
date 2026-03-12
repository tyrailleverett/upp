<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Incident;

final class UpdateIncidentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Incident $incident, array $data): Incident
    {
        $incident->update([
            'title' => $data['title'],
        ]);

        $incident->components()->sync($data['component_ids']);

        return $incident->refresh();
    }
}
