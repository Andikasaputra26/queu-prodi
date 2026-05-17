<?php

namespace App\Services;

use App\Models\Queue;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
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
                'countryCode' => '62',
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
            $resJson = json_decode($response, true);
            if (isset($resJson['status']) && $resJson['status'] === false) {
                Log::warning("Fonnte Warning: " . ($resJson['reason'] ?? 'Unknown Error'));
            }
            Log::info("Fonnte Response: " . $response);
            return true;
        }
    }

    public static function notifyRegistration(Queue $queue)
    {
        $ticketUrl = route('queue.ticket', $queue->id);
        $message = "✅ *Yay, REGISTRASI ANDA BERHASIL*\n\nHalo, *{$queue->patient_name}*!\n\nRegistrasi antrian surat Anda berhasil dilakukan dan saat ini *SEDANG DIPROSES* oleh admin.\n\n🆔 ID Surat: *{$queue->queue_number}*\n📝 Jenis: {$queue->service->name}\n📅 Tanggal: " . now()->format('d/m/Y H:i') . "\n\nCek progress antrian Anda di sini:\n" . $ticketUrl . "\n\nKami akan mengabari Anda kembali melalui WhatsApp jika proses telah selesai.";

        return self::send($queue->phone, $message);
    }

    public static function notifyStatusUpdate(Queue $queue)
    {
        if (!$queue->phone)
            return;

        $statusLabel = $queue->status_label;
        $message = "🔔 *UPDATE PROGRES SURAT*\n\nHalo, *{$queue->patient_name}*!\n\nAda perkembangan terbaru untuk surat Anda (*{$queue->queue_number}*).\n\n📍 *POSISI PROSES SEKARANG*:\n*{$statusLabel}*\n\n";

        if ($queue->status === 'admin') {
            $docNumMsg = $queue->document_number ? "\n*Nomor Surat Anda:* {$queue->document_number}\n" : "";
            $message .= "Surat Anda telah diterima oleh petugas dan sedang diproses.{$docNumMsg}";
        } elseif ($queue->status === 'done') {
            $message .= "🎉 *BERITA BAGUS!* Proses pengajuan surat Anda telah *SELESAI*.\n\nSilakan datang ke loket atau cek aplikasi untuk informasi pengambilan/tindak lanjut berikutnya.";
        } elseif ($queue->status === 'rejected') {
            $message .= "❌ Mohon maaf, pengajuan Anda belum bisa diproses karena ada ketidaksesuaian berkas.";
            if ($queue->reject_reason) {
                $message .= "\n\n*Alasan Penolakan:*\n{$queue->reject_reason}";
            }
            $message .= "\n\nSilakan perbaiki dokumen Anda lalu hubungi loket admin untuk diantrikan ulang.";
        } else {
            $message .= "Silakan pantau terus perkembangannya secara berkala.";
        }

        $message .= "\n\nTerima kasih,\n*Layanan Administrasi Jurusan*";

        return self::send($queue->phone, $message);
    }

    public static function notifyCalled(Queue $queue)
    {
        if (!$queue->phone)
            return;

        $counterName = $queue->counter ? $queue->counter->name : 'Loket Administrasi';
        $message = "📢 *PANGGILAN ANTRIAN*\n\nHalo, *{$queue->patient_name}*!\n\nNomor antrian Anda *{$queue->queue_number}* sedang dipanggil di *{$counterName}*.\n\nSilakan segera menuju ke loket tersebut.\nTerima kasih.";

        return self::send($queue->phone, $message);
    }

    public static function getLink($to, $message)
    {
        $to = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($to, '0')) {
            $to = '62' . substr($to, 1);
        }
        return "https://wa.me/{$to}?text=" . urlencode($message);
    }
}
