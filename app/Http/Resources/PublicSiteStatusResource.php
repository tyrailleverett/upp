<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\EffectiveStatusService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class PublicSiteStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $service = app(EffectiveStatusService::class);
        $overallStatus = $service->resolveOverallSiteStatus($this->resource);

        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'overall_status' => $overallStatus,
            'components' => PublicComponentResource::collection($this->whenLoaded('components')),
            'active_incidents_count' => $this->whenHas('active_incidents_count'),
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'accent_color' => $this->accent_color,
            'logo_path' => $this->logo_path,
            'favicon_path' => $this->favicon_path,
        ];
    }
}
