<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\MaintenanceWindow;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class MaintenanceScheduled implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly MaintenanceWindow $maintenanceWindow,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new Channel('site.'.$this->maintenanceWindow->site->slug),
        ];
    }

    public function broadcastAs(): string
    {
        return 'maintenance.scheduled';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'maintenance_window_id' => $this->maintenanceWindow->id,
            'title' => $this->maintenanceWindow->title,
            'scheduled_at' => $this->maintenanceWindow->scheduled_at?->toISOString(),
            'ends_at' => $this->maintenanceWindow->ends_at?->toISOString(),
            'component_ids' => $this->maintenanceWindow->components->pluck('id')->toArray(),
        ];
    }
}
