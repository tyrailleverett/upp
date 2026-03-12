<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicMaintenanceWindowResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'scheduled_at' => $this->scheduled_at,
            'ends_at' => $this->ends_at,
            'completed_at' => $this->completed_at,
            'components' => $this->whenLoaded('components', fn () => $this->components->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])),
        ];
    }
}
