<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Queue;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class SendQueueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp reminders to students who have not picked up their finished documents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cari antrian yang sudah selesai lebih dari 2 hari tapi belum diingatkan
        $queues = Queue::where('status', 'done')
            ->whereNotNull('done_at')
            ->where('done_at', '<=', now()->subDays(2))
            ->whereNull('reminder_sent_at')
            ->whereNotNull('phone')
            ->get();

        $count = 0;

        foreach ($queues as $queue) {
            $success = WhatsAppService::notifyReminder($queue);
            
            if ($success) {
                $queue->update([
                    'reminder_sent_at' => now()
                ]);
                $count++;
            }
        }

        $this->info("Successfully sent {$count} reminders.");
        Log::info("Command queue:send-reminders executed. Sent {$count} reminders.");
    }
}
