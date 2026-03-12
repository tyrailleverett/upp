<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicIncidentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'postmortem' => $this->postmortem,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at,
            'components' => $this->whenLoaded('components', fn () => $this->components->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])),
            'updates' => PublicIncidentUpdateResource::collection($this->whenLoaded('updates')),
        ];
    }
}
