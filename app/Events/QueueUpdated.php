<?php

namespace App\Events;

use App\Models\Queue;
use App\Models\QueueCounter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $action, // 'new', 'called', 'serving', 'done', 'skip', 'reset'
        public ?Queue $queueItem = null,
        public array $stats = []
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('queue-monitor'),
        ];
    }

    public function broadcastWith(): array
    {
        $data = [
            'action' => $this->action,
            'stats'  => $this->stats,
        ];

        if ($this->queueItem) {
            $this->queueItem->load(['service', 'counter']);
            $data['queue'] = [
                'id'            => $this->queueItem->id,
                'queue_number'  => $this->queueItem->queue_number,
                'patient_name'  => $this->queueItem->patient_name,
                'status'        => $this->queueItem->status,
                'status_label'  => $this->queueItem->status_label,
                'service_name'  => $this->queueItem->service?->name,
                'service_code'  => $this->queueItem->service?->code,
                'service_color' => $this->queueItem->service?->color,
                'counter_name'  => $this->queueItem->counter?->name,
                'called_at'     => $this->queueItem->called_at?->format('H:i:s'),
            ];
        }

        return $data;
    }

    public function broadcastAs(): string
    {
        return 'queue.updated';
    }
}
