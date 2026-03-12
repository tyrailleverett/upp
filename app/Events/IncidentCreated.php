<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Incident;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class IncidentCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Incident $incident,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [
            new Channel('site.'.$this->incident->site->slug),
        ];
    }

    public function broadcastAs(): string
    {
        return 'incident.created';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        $initialUpdate = $this->incident->updates()->latest()->first();

        return [
            'incident_id' => $this->incident->id,
            'title' => $this->incident->title,
            'status' => $this->incident->status->value,
            'component_ids' => $this->incident->components->pluck('id')->toArray(),
            'initial_message' => $initialUpdate?->message,
        ];
    }
}
