<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\IncidentUpdate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class IncidentUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly IncidentUpdate $incidentUpdate,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new Channel('site.'.$this->incidentUpdate->incident->site->slug),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incident.updated';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'incident_id' => $this->incidentUpdate->incident_id,
            'status' => $this->incidentUpdate->status->value,
            'message' => $this->incidentUpdate->message,
            'created_at' => $this->incidentUpdate->created_at?->toISOString(),
        ];
    }
}
