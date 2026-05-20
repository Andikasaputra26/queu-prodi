<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ChatSession;
use App\Models\QueueService;
use App\Models\Queue;
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
        
        $systemInstructionText = "Anda adalah Asisten Virtual Akademik SI-PINTER (Sistem Pelayanan Informasi Terpadu) yang cerdas dan solutif. "
            . "Tugas Anda membantu mahasiswa mengecek status pengajuan secara real-time dan memiliki akses langsung ke sistem untuk mengambil data.\n\n"
            . "Layanan yang kami sediakan: {$services}.\n"
            . "Jika mahasiswa menanyakan status pengajuan atau keluhan dan memberikan ID pengajuan (seperti nomor tiket TKT-xxxxx, nomor antrean, atau nomor surat), "
            . "gunakan tool `checkRequestStatus` untuk mengambil data status pengajuan atau keluhan mahasiswa secara real-time dari database.\n"
            . "Jawab dengan ramah, profesional, dan ringkas dalam Bahasa Indonesia. Jika Anda tidak tahu jawabannya atau mahasiswa butuh bantuan staf asli, katakan bahwa Admin akan segera membalas.";

        $systemInstruction = [
            'parts' => [
                ['text' => $systemInstructionText]
            ]
        ];

        $historyText = "";
        foreach ($session->messages as $msg) {
            $senderName = $msg->sender === 'admin' ? 'Asisten/Admin' : 'Mahasiswa';
            $historyText .= $senderName . ": " . $msg->message . "\n";
        }

        $prompt = "Berikut adalah riwayat obrolan sejauh ini:\n" . $historyText . "\nSilakan berikan balasan Anda selanjutnya sebagai Asisten/Admin (jawab langsung isi pesannya saja, tanpa prefix):";

        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => $prompt]]
            ]
        ];

        $tools = [
            [
                'function_declarations' => [
                    [
                        'name' => 'checkRequestStatus',
                        'description' => 'Mengambil data status pengajuan atau keluhan mahasiswa secara real-time dari database berdasarkan ID Pengajuan.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'request_id' => [
                                    'type' => 'STRING',
                                    'description' => 'ID unik pengajuan keluhan mahasiswa.'
                                ]
                            ],
                            'required' => [
                                'request_id'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return;

        $models = ['gemini-2.5-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash', 'gemini-flash-latest'];
        $reply = null;
        $success = false;

        foreach ($models as $model) {
            try {
                $currentContents = $contents;
                $loopCount = 0;
                $maxLoops = 3;

                while ($loopCount < $maxLoops) {
                    $response = Http::timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => $currentContents,
                        'tools' => $tools,
                        'system_instruction' => $systemInstruction,
                    ]);

                    if (!$response->successful()) {
                        \Log::warning("Gemini model {$model} failed: Status {$response->status()}", ['body' => $response->body()]);
                        break;
                    }

                    $candidate = $response->json('candidates.0');
                    if (!$candidate) {
                        break;
                    }

                    $content = $candidate['content'] ?? null;
                    if (!$content) {
                        break;
                    }

                    // Check if model returned a functionCall
                    $functionCall = null;
                    foreach ($content['parts'] ?? [] as $part) {
                        if (isset($part['functionCall'])) {
                            $functionCall = $part['functionCall'];
                            break;
                        }
                    }

                    if ($functionCall) {
                        $name = $functionCall['name'];
                        $args = $functionCall['args'] ?? [];

                        if ($name === 'checkRequestStatus') {
                            $requestId = $args['request_id'] ?? '';
                            $result = $this->checkRequestStatus($requestId);

                            // Format the model turn with the function call
                            $modelTurn = $content;
                            if (!isset($modelTurn['role'])) {
                                $modelTurn['role'] = 'model';
                            }

                            // Format the function response turn
                            $functionTurn = [
                                'role' => 'function',
                                'parts' => [
                                    [
                                        'functionResponse' => [
                                            'name' => $name,
                                            'response' => $result
                                        ]
                                    ]
                                ]
                            ];

                            // Add both turns to request history and loop back
                            $currentContents[] = $modelTurn;
                            $currentContents[] = $functionTurn;
                            $loopCount++;
                            continue;
                        }
                    }

                    // Look for final text reply
                    $replyText = null;
                    foreach ($content['parts'] ?? [] as $part) {
                        if (isset($part['text'])) {
                            $replyText = $part['text'];
                            break;
                        }
                    }

                    if ($replyText) {
                        $reply = $replyText;
                        $success = true;
                    }
                    break;
                }

                if ($success) {
                    break;
                }
            } catch (\Exception $e) {
                \Log::error("Gemini model {$model} exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
        }

        if ($success && $reply) {
            $message = $session->messages()->create([
                'sender' => 'admin',
                'message' => trim($reply),
            ]);
            $session->update(['last_activity_at' => now()]);
            broadcast(new ChatMessageSent($message, $session));
        } else {
            \Log::error('All Gemini fallback models exhausted or failed to return text.');
        }
    }

    /**
     * Mengambil data status pengajuan atau keluhan mahasiswa secara real-time dari database.
     */
    private function checkRequestStatus(string $requestId): array
    {
        $requestIdClean = trim(strtoupper($requestId));

        // Mencari berdasarkan format tiket (TKT-XXXXX)
        if (preg_match('/^TKT-(\d+)$/i', $requestIdClean, $matches)) {
            $queue = Queue::with('service', 'counter')->find((int) $matches[1]);
        } else {
            // Mencari berdasarkan queue_number, document_number, atau ID secara langsung
            $queue = Queue::with('service', 'counter')
                ->where('queue_number', $requestIdClean)
                ->orWhere('document_number', $requestIdClean)
                ->orWhere(function($query) use ($requestIdClean) {
                    if (is_numeric($requestIdClean)) {
                        $query->where('id', (int) $requestIdClean);
                    }
                })
                ->first();
        }

        if (!$queue) {
            return [
                'found' => false,
                'message' => "Pengajuan atau keluhan dengan ID '{$requestId}' tidak ditemukan di database."
            ];
        }

        $statusLabel = match ($queue->status) {
            'pending'   => 'Diajukan / Menunggu Verifikasi',
            'admin'     => 'Sedang Diproses oleh Admin',
            'done'      => 'Selesai',
            'picked_up' => 'Selesai & Sudah Diambil',
            'rejected'  => 'Ditolak',
            default     => ucfirst($queue->status),
        };

        return [
            'found' => true,
            'id_pengajuan' => 'TKT-' . str_pad($queue->id, 5, '0', STR_PAD_LEFT),
            'nama_mahasiswa' => $queue->patient_name,
            'nomor_antrean' => $queue->queue_number,
            'layanan' => $queue->service?->name,
            'loket' => $queue->counter?->name ?? 'Belum ditentukan',
            'status' => $statusLabel,
            'nomor_surat' => $queue->document_number ?? 'Belum ada',
            'alasan_penolakan' => $queue->reject_reason ?? null,
            'tanggal_pengajuan' => $queue->queue_date ? $queue->queue_date->format('d-m-Y') : null,
            'link_tiket' => route('queue.ticket', 'TKT-' . str_pad($queue->id, 5, '0', STR_PAD_LEFT)),
        ];
    }
}

