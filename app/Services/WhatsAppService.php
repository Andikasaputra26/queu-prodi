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

        $token = env('FONNTE_TOKEN');

        if (!$token || $token === 'your_token_here') {
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
            Log::info("Fonnte Response: " . $response);
            return true;
        }
    }

    /**
     * Notifikasi saat baru mendaftar (Pengajuan Surat)
     */
    public static function notifyRegistration(Queue $queue)
    {
        if (!$queue->phone) return;

        $message = "Halo, *{$queue->patient_name}*!\n\nPengajuan surat Anda telah terdaftar di *SiPro — Pelacakan Surat Mahasiswa*.\n\nID Surat: *{$queue->queue_number}*\nJenis Surat: {$queue->service->name}\n\nStatus saat ini: *Menunggu Verifikasi Admin*.\nSilakan pantau progres surat Anda secara berkala.\n\nTerima kasih.";

        return self::send($queue->phone, $message);
    }

    /**
     * Notifikasi saat status diperbarui
     */
    public static function notifyStatusUpdate(Queue $queue)
    {
        if (!$queue->phone) return;

        $statusLabel = $queue->status_label;
        $message = "🔔 *UPDATE PROGRES SURAT*\n\nHalo, *{$queue->patient_name}*!\n\nSurat Anda (*{$queue->queue_number}*) telah diperbarui.\nStatus Terbaru: *{$statusLabel}*.\n\n";

        if ($queue->status === 'ready') {
            $message .= "Surat Anda *SUDAH SIAP* dan dapat diambil di loket Administrasi Prodi pada jam kerja.\n\nMohon membawa kartu identitas/KTM saat pengambilan.";
        } elseif ($queue->status === 'prodi') {
            $message .= "Surat sedang dalam proses tanda tangan / pengerjaan di tingkat Program Studi.";
        } elseif ($queue->status === 'rejected') {
            $message .= "Mohon maaf, pengajuan surat Anda *DITOLAK*. Silakan hubungi admin prodi untuk informasi lebih lanjut.";
        } else {
            $message .= "Silakan pantau progres selanjutnya melalui sistem.";
        }

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
