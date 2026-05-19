<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $session;

    public function __construct(ChatMessage $message, ChatSession $session)
    {
        $this->message = $message;
        $this->session = $session;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('chat.' . $this->session->session_id),
            new Channel('admin-chat')
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }
}
