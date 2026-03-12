<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\EffectiveStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicComponentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $effectiveStatus = app(EffectiveStatusService::class)->resolveComponentStatus($this->resource);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'group' => $this->group,
            'status' => $effectiveStatus,
            'sort_order' => $this->sort_order,
        ];
    }
}
