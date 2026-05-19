<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\ChatSession::create([
    'session_id' => 'test-fallback-' . time(),
    'student_name' => 'Test',
    'status' => 'open',
    'is_ai_active' => true,
    'last_activity_at' => now()
]);
$s->messages()->create(['sender' => 'student', 'message' => 'Layanan apa saja yang ada?']);

App\Jobs\ProcessGeminiChat::dispatchSync($s->id);

$s->refresh();
echo "REPLY: " . json_encode($s->messages->pluck('message')->toArray()) . "\n";
