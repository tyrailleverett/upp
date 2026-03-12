<?php

declare(strict_types=1);

namespace App\Events;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Services\EffectiveStatusService;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ComponentStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Component $component,
        public readonly ComponentStatus $previousStatus,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new Channel('site.'.$this->component->site->slug),
        ];
    }

    public function broadcastAs(): string
    {
        return 'component.status_changed';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $effectiveStatus = app(EffectiveStatusService::class)->resolveComponentStatus($this->component);

        return [
            'component_id' => $this->component->id,
            'name' => $this->component->name,
            'status' => $effectiveStatus->value,
            'previous_status' => $this->previousStatus->value,
        ];
    }
}
