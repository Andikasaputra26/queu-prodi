<?php

namespace App\Services;

use App\Models\Queue;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function send($to, $message)
    {
        $to = preg_replace('/[^0-9]/', '', $to);
        if (str_starts_with($to, '0')) {
            $to = '62' . substr($to, 1);
        }

        // Mengirimkan notifikasi ke background job agar web tidak lemot
        \App\Jobs\SendWhatsAppNotification::dispatch($to, $message);
        
        return true;
    }

    private static function getGreeting()
    {
        $hour = (int) now()->format('H');
        if ($hour < 12) return "Selamat Pagi!";
        if ($hour < 15) return "Selamat Siang!";
        if ($hour < 18) return "Selamat Sore!";
        return "Selamat Malam!";
    }

    public static function notifyRegistration(Queue $queue)
    {
        $ticketUrl = route('queue.ticket', 'TKT-' . str_pad($queue->id, 5, '0', STR_PAD_LEFT));
        $greeting = self::getGreeting();
        
        $message = "🎉 *Yay, registrasi Anda berhasil!*\n\n";
        $message .= "Halo, *{$queue->patient_name}*! 👋\n";
        $message .= "{$greeting}\n";
        $message .= "Permohonan surat yang Anda ajukan telah berhasil dan sedang dalam proses verfikasi nih😊\n\n";
        $message .= "📝 Jenis Surat: {$queue->service->name}\n";
        $message .= "🎟️ Nomor Antrean: *{$queue->queue_number}*\n\n";
        $message .= "Anda dapat memantau perkembangan antrean melalui tautan berikut:\n{$ticketUrl}\n\n";
        $message .= "Mohon ditunggu yah! Kami akan segera menginformasikan melalui WhatsApp jika surat telah selesai dan siap untuk diambil🚀\n\n";
        $message .= "Salam hangat,\n\n*Admin SI-PINTER*";

        return self::send($queue->phone, $message);
    }

    public static function notifyStatusUpdate(Queue $queue)
    {
        if (!$queue->phone) return;

        if ($queue->status === 'done') {
            $message = "🥳 *Hore! Surat Anda sudah siap!*\n\n";
            $message .= "Halo, *{$queue->patient_name}*! 👋\n";
            $message .= "Ada kabar gembira nih! Permohonan surat Anda telah selesai diproses dan siap untuk diambil✨\n\n";
            $message .= "📝 Jenis Surat: {$queue->service->name}\n";
            $message .= "🎟️ Nomor Antrean: *{$queue->queue_number}*\n\n";
            $message .= "Silakan mengunjungi ruang Jurusan Ilmu Administrasi pada jam kerja untuk pengambilan surat. Mohon tunjukkan pesan WhatsApp ini saat pengambilan surat, ya 📱\n\n";
            $message .= "Kami menantikan kedatangan Anda! 😊\n\n";
            $message .= "Salam hangat,\n\n*Admin SI-PINTER*";
        } elseif ($queue->status === 'rejected') {
            $message = "🔔 *Update Progres Pengajuan Surat⏰*\n\nHalo, *{$queue->patient_name}*! 👋\n\n Terima kasih atas pengajuan surat yang telah Anda lakukan. Saat ini, pengajuan Anda belum dapat kami proses karena terdapat beberapa ketidaksesuaian pada berkas yang dilampirkan. ❌.\n";
            if ($queue->reject_reason) {
                $message .= "\n*Alasan Penolakan:*\n{$queue->reject_reason}\n";
            }
            $message .= "\nSilakan perbaiki dokumen Anda lalu lakukan antirian ulang melalui sistem SI-PINTER,ya 😊
                            .\n\nSalam hangat,\n\n*Admin SI-PINTER*";
        } elseif ($queue->status === 'admin') {
            $docNumMsg = $queue->document_number ? "\n*Nomor Surat Anda:* {$queue->document_number}\n" : "";
            $message = "🔔 *Update Progres Pengajuan Surat⏰*\n\nHalo, *{$queue->patient_name}*! 👋\n\nSilahkan pantau perkembangannya secara berkala melalui sistem SI-PINTER, ya 😊 \n\nSalam hangat,\n\n*Admin SI-PINTER*";
        } else {
            return true; 
        }

        return self::send($queue->phone, $message);
    }

    public static function notifyReminder(Queue $queue)
    {
        if (!$queue->phone) return;

        $message = "🔔 *Pengingat pengambilan surat* ⏰\n\n";
        $message .= "Halo, *{$queue->patient_name}*! 👋\n";
        $message .= "Kami ingin mengingatkan kembali bahwa surat permohonan Anda dengan nomor antrean *{$queue->queue_number}* telah tersedia di ruang Jurusan Ilmu Administrasi. Jangan sampai terlewat ya karena dokumen ini penting untuk keperluan administrasi Anda 📑\n\n";
        $message .= "Kami menunggu kehadiran Anda!😊\n\n";
        $message .= "Salam hangat,\n\n*Admin SI-PINTER*";

        return self::send($queue->phone, $message);
    }

    public static function notifySurvey(Queue $queue, $surveyLink)
    {
        if (!$queue->phone) return;

        $message = "🔔 *Yuk, Bantu Kami Menjadi Lebih Baik!* ⭐\n\n";
        $message .= "Hallo, *{$queue->patient_name}*! 👋\n";
        $message .= "Terima kasih telah menggunakan SI-PINTER. Kami berharap pengalaman layanan yang Anda terima berjalan dengan baik dan menyenangkan.\n\n";
        $message .= "Untuk mendukung peningkatan kualitas layanan, kami mengundang Anda untuk mengisi survei kepuasan singkat melalui tautan berikut:\n";
        $message .= "🔗 {$surveyLink}\n\n";
        $message .= "Setiap masukan yang Anda berikan sangat berarti bagi pengembangan layanan di Jurusan Ilmu Administrasi 💡\n\n";
        $message .= "Terima kasih atas partisipasi Anda! 😊\n\n";
        $message .= "Salam hangat,\n\n*Admin SI-PINTER*";

        return self::send($queue->phone, $message);
    }

    public static function notifyCalled(Queue $queue)
    {
        if (!$queue->phone) return;

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
