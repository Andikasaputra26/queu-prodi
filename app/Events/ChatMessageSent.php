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

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_session_id' => $this->message->chat_session_id,
                'sender' => $this->message->sender,
                'message' => $this->message->message,
                'is_read' => $this->message->is_read,
                'created_at' => $this->message->created_at ? $this->message->created_at->toIso8601String() : null,
            ],
            'session' => [
                'id' => $this->session->id,
                'session_id' => $this->session->session_id,
                'student_name' => $this->session->student_name,
                'student_phone' => $this->session->student_phone,
                'status' => $this->session->status,
                'is_ai_active' => $this->session->is_ai_active,
                'last_activity_at' => $this->session->last_activity_at ? $this->session->last_activity_at->toIso8601String() : null,
            ]
        ];
    }
}
