<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Enums\IncidentStatus;
use App\Models\Incident;

final class ResolveIncidentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Incident $incident, array $data): Incident
    {
        $incident->updates()->create([
            'status' => IncidentStatus::Resolved,
            'message' => $data['message'],
        ]);

        $incident->update([
            'status' => IncidentStatus::Resolved,
            'resolved_at' => now(),
            'postmortem' => $data['postmortem'] ?? null,
        ]);

        return $incident->refresh();
    }
}
