<?php

namespace App\Services;

use App\Models\Queue;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Kirim notifikasi WhatsApp Real melalui Fonnte Gateway
     */
    public static function send($to, $message)
    {
        // Bersihkan nomor
        $to = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($to, '0')) {
            $to = '62' . substr($to, 1);
        }

        $token = trim(env('FONNTE_TOKEN'));

        if (!$token || $token === 'your_token_here' || $token === '') {
            Log::info("[SIMULATION] WhatsApp to {$to}: {$message}");
            return true;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $to,
                'message' => $message,
                'countryCode' => '62', // default Indonesia
            ),
            CURLOPT_HTTPHEADER => array(
                "Authorization: $token"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            Log::error("Fonnte Error: " . $err);
            return false;
        } else {
            // Jika token salah, catat di log
            $resJson = json_decode($response, true);
            if (isset($resJson['status']) && $resJson['status'] === false) {
                Log::warning("Fonnte Warning: " . ($resJson['reason'] ?? 'Unknown Error'));
            }
            Log::info("Fonnte Response: " . $response);
            return true;
        }
    }

    /**
     * Notifikasi saat baru mendaftar (Pengajuan Surat)
     */
    public static function notifyRegistration(Queue $queue)
    {
        $ticketUrl = route('queue.ticket', $queue->id);
        $message = "✅ *PENGAJUAN TERDAFTAR*\n\nHalo, *{$queue->patient_name}*!\n\nPermohonan surat Anda telah diterima oleh sistem.\n\n🆔 ID Surat: *{$queue->queue_number}*\n📝 Jenis: {$queue->service->name}\n📅 Tanggal: " . now()->format('d/m/Y H:i') . "\n\nCek sisa antrian Anda di sini:\n" . $ticketUrl . "\n\nStatus saat ini: *Menunggu Verifikasi Admin*.\nKami akan mengabari Anda kembali melalui WhatsApp ini jika ada perkembangan.";

        return self::send($queue->phone, $message);
    }

    /**
     * Notifikasi saat status diperbarui
     */
    public static function notifyStatusUpdate(Queue $queue)
    {
        if (!$queue->phone) return;

        $statusLabel = $queue->status_label;
        $message = "🔔 *UPDATE PROGRES SURAT*\n\nHalo, *{$queue->patient_name}*!\n\nAda perkembangan terbaru untuk surat Anda (*{$queue->queue_number}*).\n\n📍 *POSISI PROSES SEKARANG*:\n*{$statusLabel}*\n\n";

        if ($queue->status === 'admin') {
            $message .= "Petugas administrasi sedang memeriksa kelengkapan berkas Anda. Mohon ditunggu.";
        } elseif ($queue->status === 'prodi') {
            $message .= "Berkas sudah lengkap dan sekarang sedang diproses di tingkat Program Studi (Ketua Prodi).";
        } elseif ($queue->status === 'ready') {
            $message .= "🎉 *BERITA BAGUS!* Surat Anda sudah selesai dan *SIAP DIAMBIL* di loket Prodi.\n\nSilakan datang pada jam kerja dengan membawa KTM/Kartu Identitas.";
        } elseif ($queue->status === 'rejected') {
            $message .= "❌ Mohon maaf, pengajuan Anda belum bisa diproses karena ada ketidaksesuaian berkas.";
            if ($queue->reject_reason) {
                $message .=  "\n\n*Alasan Penolakan:*\n{$queue->reject_reason}";
            }
            $message .= "\n\nSilakan perbaiki dokumen Anda lalu hubungi loket admin untuk diantrikan ulang.";
        } else {
            $message .= "Silakan pantau terus perkembangannya secara berkala.";
        }

        $message .= "\n\nTerima kasih,\n*Layanan Administrasi Prodi*";

        return self::send($queue->phone, $message);
    }

    public static function notifyCalled(Queue $queue)
    {
        if (!$queue->phone) return;

        $counterName = $queue->counter ? $queue->counter->name : 'Loket Administrasi';
        $message = "📢 *PANGGILAN ANTRIAN*\n\nHalo, *{$queue->patient_name}*!\n\nNomor antrian Anda *{$queue->queue_number}* sedang dipanggil di *{$counterName}*.\n\nSilakan segera menuju ke loket tersebut.\nTerima kasih.";

        return self::send($queue->phone, $message);
    }

    /**
     * Generasikan URL WhatsApp Click-to-Chat
     */
    public static function getLink($to, $message)
    {
        $to = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($to, '0')) {
            $to = '62' . substr($to, 1);
        }
        return "https://wa.me/{$to}?text=" . urlencode($message);
    }
}
