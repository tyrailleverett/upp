<?php

declare(strict_types=1);

namespace App\Actions\Sites;

use App\Models\Incident;
use App\Models\Site;

final class CreateIncidentAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Site $site, array $data): Incident
    {
        $incident = $site->incidents()->create([
            'title' => $data['title'],
            'status' => $data['status'],
        ]);

        $incident->components()->attach($data['component_ids']);

        $incident->updates()->create([
            'status' => $data['status'],
            'message' => $data['message'],
        ]);

        return $incident;
    }
}
