<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Events\ChatMessageSent;

echo "Checking Pusher broadcast connection...\n";
echo "BROADCAST_CONNECTION: " . env('BROADCAST_CONNECTION') . "\n";
echo "PUSHER_APP_ID: " . env('PUSHER_APP_ID') . "\n";
echo "PUSHER_APP_KEY: " . env('PUSHER_APP_KEY') . "\n";
echo "PUSHER_APP_CLUSTER: " . env('PUSHER_APP_CLUSTER') . "\n";

try {
    // Let's find or create a dummy session
    $session = ChatSession::firstOrCreate(
        ['session_id' => 'test-session-123'],
        [
            'student_name' => 'Test Student',
            'status' => 'open',
            'last_activity_at' => now(),
            'is_ai_active' => true,
        ]
    );

    $message = new ChatMessage([
        'sender' => 'admin',
        'message' => 'Hello from Pusher Broadcast Test! ' . now()->toDateTimeString(),
    ]);
    $message->chat_session_id = $session->id;
    $message->save();

    echo "Broadcasting ChatMessageSent event...\n";
    broadcast(new ChatMessageSent($message, $session));
    echo "Broadcast event dispatched successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
