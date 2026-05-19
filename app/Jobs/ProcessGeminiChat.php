<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ChatSession;
use App\Models\QueueService;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Http;

class ProcessGeminiChat implements ShouldQueue
{
    use Queueable;

    public $sessionId;

    public function __construct($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    public function handle(): void
    {
        $session = ChatSession::with('messages')->find($this->sessionId);
        
        if (!$session || !$session->is_ai_active || $session->status === 'closed') {
            return;
        }

        $services = QueueService::where('is_active', true)->get()->pluck('name')->implode(', ');
        $systemContext = "[SISTEM: Anda adalah Asisten AI SI-PINTER (Sistem Pelayanan Informasi Terpadu). Tugas Anda menjawab mahasiswa dengan ramah, profesional, dan ringkas. Layanan yang kami sediakan: {$services}. Jika Anda tidak tahu jawabannya atau mahasiswa butuh bantuan staf asli, katakan bahwa Admin akan segera membalas.]\n\n";

        $historyText = "";
        foreach ($session->messages as $msg) {
            $senderName = $msg->sender === 'admin' ? 'Asisten/Admin' : 'Mahasiswa';
            $historyText .= $senderName . ": " . $msg->message . "\n";
        }

        $prompt = $systemContext . "Berikut adalah riwayat obrolan sejauh ini:\n" . $historyText . "\nSilakan berikan balasan Anda selanjutnya sebagai Asisten/Admin (jawab langsung isi pesannya saja, tanpa prefix):";

        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => $prompt]]
            ]
        ];

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return;

        try {
            $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => $contents,
            ]);

            if ($response->successful()) {
                $reply = $response->json('candidates.0.content.parts.0.text');
                if ($reply) {
                    $message = $session->messages()->create([
                        'sender' => 'admin',
                        'message' => trim($reply),
                    ]);
                    $session->update(['last_activity_at' => now()]);
                    broadcast(new ChatMessageSent($message, $session));
                } else {
                    \Log::error('Gemini API returned success but no reply text.', ['response' => $response->json()]);
                }
            } else {
                \Log::error('Gemini API Error', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            \Log::error('Gemini Job Exception: ' . $e->getMessage());
        }
    }
}
