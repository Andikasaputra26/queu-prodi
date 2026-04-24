<?php

namespace App\Events;

use App\Models\Queue;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueCalled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Queue $queueItem) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('queue-display'),
        ];
    }

    public function broadcastWith(): array
    {
        $this->queueItem->load(['service', 'counter']);
        return [
            'queue_number'  => $this->queueItem->queue_number,
            'patient_name'  => $this->queueItem->patient_name,
            'service_name'  => $this->queueItem->service?->name,
            'service_code'  => $this->queueItem->service?->code,
            'service_color' => $this->queueItem->service?->color,
            'counter_name'  => $this->queueItem->counter?->name,
            'called_at'     => $this->queueItem->called_at?->format('H:i:s'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'queue.called';
    }
}
