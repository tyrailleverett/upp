<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Events\IncidentUpdated;
use App\Models\Incident;
use App\Models\IncidentUpdate;

final class AddIncidentUpdateAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Incident $incident, array $data): IncidentUpdate
    {
        $update = $incident->updates()->create([
            'status' => $data['status'],
            'message' => $data['message'],
        ]);

        $incident->update([
            'status' => $data['status'],
        ]);

        $update->load('incident.site');

        IncidentUpdated::dispatch($update);

        return $update;
    }
}
