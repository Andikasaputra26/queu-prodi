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
        // Gunakan eager loading untuk langsung mengambil pesan demi menghemat query DB
        $session = ChatSession::with('messages')->find($this->sessionId);
        
        if (!$session || !$session->is_ai_active || $session->status === 'closed') {
            return;
        }

        $lastStudentMessage = $session->messages->where('sender', 'student')->last();
        if ($lastStudentMessage) {
            $msgText = strtolower(trim($lastStudentMessage->message));
            $cleanMsgText = preg_replace('/[?.!,]/', '', $msgText);
            
            $isAskingCreator = false;
            if (
                str_contains($cleanMsgText, 'siapa yang membuat website ini') ||
                str_contains($cleanMsgText, 'siapa pembuat website ini') ||
                str_contains($cleanMsgText, 'siapa yang buat website ini') ||
                (
                    str_contains($cleanMsgText, 'siapa') && 
                    (
                        str_contains($cleanMsgText, 'buat') || 
                        str_contains($cleanMsgText, 'bikin') || 
                        str_contains($cleanMsgText, 'membuat') || 
                        str_contains($cleanMsgText, 'pembuat') || 
                        str_contains($cleanMsgText, 'creator') || 
                        str_contains($cleanMsgText, 'developer') ||
                        str_contains($cleanMsgText, 'pencipta') ||
                        str_contains($cleanMsgText, 'cipta') ||
                        str_contains($cleanMsgText, 'rancang') ||
                        str_contains($cleanMsgText, 'perancang')
                    ) && 
                    (
                        str_contains($cleanMsgText, 'web') || 
                        str_contains($cleanMsgText, 'aplikasi') || 
                        str_contains($cleanMsgText, 'sistem') || 
                        str_contains($cleanMsgText, 'si-pinter') || 
                        str_contains($cleanMsgText, 'sipinter') ||
                        str_contains($cleanMsgText, 'platform') ||
                        str_contains($cleanMsgText, 'platfrom') ||
                        str_contains($cleanMsgText, 'chatbot') ||
                        str_contains($cleanMsgText, 'bot') ||
                        str_contains($cleanMsgText, 'layanan ini')
                    )
                )
            ) {
                $isAskingCreator = true;
            }

            if ($isAskingCreator) {
                $reply = "Website ini dibuat oleh ANDIKA SAPUTRA dari makassar.";
                $message = $session->messages()->create([
                    'sender' => 'admin',
                    'message' => $reply,
                ]);
                $session->update(['last_activity_at' => now()]);
                broadcast(new ChatMessageSent($message, $session));
                return;
            }
        }

        $services = QueueService::where('is_active', true)->get()->pluck('name')->implode(', ');
        $currentTime = now()->timezone('Asia/Makassar')->locale('id')->translatedFormat('l, d F Y H:i');

        $systemInstructionText = "Anda adalah Asisten Virtual Akademik SI-PINTER (Sistem Pelayanan Informasi Terpadu) yang cerdas dan solutif. "
            . "Tugas Anda membantu mahasiswa mengecek status pengajuan secara real-time dan memberikan informasi mengenai syarat pengajuan surat/layanan akademik di Program Studi.\n\n"
            . "Layanan yang tersedia beserta persyaratannya:\n"
            . "- **Layanan Seminar (Seminar Proposal [SP], Seminar Hasil [SH], Seminar Tutup [ST])**:\n"
            . "  * Mahasiswa **wajib** mengunggah file draf/berkas seminar saat mendaftar (Format PDF/Word, Maks 5MB).\n"
            . "  * Jika mengunggah format Word (.docx), mahasiswa **wajib** menyisipkan teks `" . '${nmr}' . "` di dalam dokumen agar nomor surat/nomor antrean dapat terisi otomatis secara sistem ketika diproses oleh Admin.\n"
            . "  * Mahasiswa dapat mengunduh Template Surat (`FormatSurat.docx`) langsung pada halaman pendaftaran.\n"
            . "- **Surat Keterangan Aktif (SKA)**:\n"
            . "  * Digunakan untuk keperluan beasiswa, tunjangan orang tua, dll. Pengajuan di web bersifat opsional tanpa berkas wajib, namun mahasiswa disarankan membawa dokumen pendukung (seperti KTM/KRS aktif) jika diperlukan.\n"
            . "- **Legalisir Dokumen (LEG)**:\n"
            . "  * Untuk melegalisir ijazah, transkrip nilai, atau dokumen akademik lainnya. Mahasiswa wajib membawa dokumen asli beserta fotokopinya yang ingin dilegalisir saat datang ke Program Studi.\n"
            . "- **Surat Pengantar Magang (MAG)**:\n"
            . "  * Untuk PKL, Magang, atau penelitian instansi luar. Mahasiswa perlu memasukkan nama instansi/perusahaan tujuan magang atau lokasi penelitian.\n"
            . "- **Cuti / Aktif Kembali (ADM)**:\n"
            . "  * Pengajuan cuti akademik atau aktif kembali kuliah. Siapkan formulir/berkas permohonan resmi.\n"
            . "- **Surat Rekomendasi (REC)**:\n"
            . "  * Rekomendasi dosen untuk lanjut studi atau kompetisi. Siapkan draf rekomendasi atau berkas penunjang prestasi/studi.\n"
            . "- **Urusan Administrasi Lain (OTH)**:\n"
            . "  * Keperluan administrasi lainnya yang tidak terdaftar di atas.\n\n"
            . "Jam operasional pelayanan akademik (Fakultas Ilmu Sosial dan Hukum, Universitas Negeri Makassar) adalah hari Senin s.d. Jumat, pukul 08:00 hingga 16:00 WITA. Pelayanan tutup pada hari Sabtu, Minggu, dan hari libur nasional. Semua waktu operasional menggunakan zona waktu WITA.\n"
            . "Waktu saat ini: {$currentTime} WITA.\n\n"
            . "Jika mahasiswa menanyakan status pengajuan atau keluhan dan memberikan ID pengajuan (seperti nomor tiket TKT-xxxxx, nomor antrean, atau nomor surat), "
            . "gunakan tool `checkRequestStatus` untuk mengambil data status pengajuan atau keluhan mahasiswa secara real-time dari database.\n"
            . "Jika mahasiswa menanyakan siapa pembuat atau pengembang website/sistem ini, jawab bahwa pembuat website/sistem ini adalah ANDIKA SAPUTRA dari makassar.\n"
            . "Jawab dengan ramah, profesional, ringkas, dan jelas dalam Bahasa Indonesia. Gunakan formatting list/bullet points agar mudah dibaca jika menjelaskan persyaratan.";

        $systemInstruction = [
            'parts' => [['text' => $systemInstructionText]]
        ];

        $contents = [];
        foreach ($session->messages as $msg) {
            $contents[] = [
                'role'  => $msg->sender === 'admin' ? 'model' : 'user',
                'parts' => [['text' => $msg->message]]
            ];
        }

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
                            'required' => ['request_id']
                        ]
                    ]
                ]
            ]
        ];

        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) return;

        $models = ['gemini-3.5-flash', 'gemini-2.5-flash', 'gemini-flash-latest'];
        $reply = null;
        $success = false;

        foreach ($models as $model) {
            try {
                $currentContents = $contents;
                $loopCount = 0;
                $maxLoops = 2; 

                while ($loopCount < $maxLoops) {
                    $response = Http::timeout(12)->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                        'contents' => $currentContents,
                        'tools' => $tools,
                        'system_instruction' => $systemInstruction,
                    ]);

                    if (!$response->successful()) {
                        \Log::warning("Gemini model {$model} failed: Status {$response->status()}");
                        break;
                    }

                    $candidate = $response->json('candidates.0');
                    if (!$candidate) break;

                    $content = $candidate['content'] ?? null;
                    if (!$content) break;

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

                            $modelTurn = $content;
                            if (!isset($modelTurn['role'])) {
                                $modelTurn['role'] = 'model';
                            }

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

                            $currentContents[] = $modelTurn;
                            $currentContents[] = $functionTurn;
                            $loopCount++;
                            continue;
                        }
                    }

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

                if ($success) break;
            } catch (\Exception $e) {
                \Log::error("Gemini model {$model} exception: " . $e->getMessage());
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

    private function checkRequestStatus(string $requestId): array
    {
        $requestIdClean = trim(strtoupper($requestId));

        if (preg_match('/^TKT-(\d+)$/i', $requestIdClean, $matches)) {
            $queue = Queue::with('service', 'counter')->find((int) $matches[1]);
        } else {
            $queue = Queue::with('service', 'counter')
                ->where('queue_number', $requestIdClean)
                ->orWhere('document_number', $requestIdClean)
                ->orWhere(function($query) use ($requestIdClean) {
                    if (is_numeric($requestIdClean)) {
                        $query->where('id', (int) $requestIdClean);
                    }
                })                ->first();
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