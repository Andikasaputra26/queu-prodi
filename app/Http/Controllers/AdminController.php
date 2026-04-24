<?php

namespace App\Http\Controllers;

use App\Events\QueueCalled;
use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\QueueCounter;
use App\Models\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Dashboard Admin
     */
    public function index()
    {
        $counters = QueueCounter::with(['currentQueue.service'])->where('is_active', true)->get();
        $services = QueueService::where('is_active', true)->get();

        $todayStats = [
            'total'   => Queue::whereDate('queue_date', today())->count(),
            'pending' => Queue::whereDate('queue_date', today())->where('status', 'pending')->count(),
            'admin'   => Queue::whereDate('queue_date', today())->where('status', 'admin')->count(),
            'prodi'   => Queue::whereDate('queue_date', today())->where('status', 'prodi')->count(),
            'ready'   => Queue::whereDate('queue_date', today())->where('status', 'ready')->count(),
            'done'    => Queue::whereDate('queue_date', today())->where('status', 'done')->count(),
        ];

        return view('admin.dashboard', compact('counters', 'services', 'todayStats'));
    }

    /**
     * Tambah Mahasiswa Manual (dari admin)
     */
    public function addManual(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:100',
            'phone'        => 'nullable|string|max:30',
            'service_id'   => 'required|exists:queue_services,id',
        ]);

        $queue = DB::transaction(function () use ($request) {
            $service  = QueueService::where('id', $request->service_id)->lockForUpdate()->firstOrFail();
            $sequence = $service->getNextSequence();
            $queueNumber = $service->code . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            return Queue::create([
                'queue_number' => $queueNumber,
                'patient_name' => $request->patient_name,
                'phone'        => $request->phone,
                'service_id'   => $service->id,
                'status'       => 'pending',
                'sequence'     => $sequence,
                'queue_date'   => today(),
            ]);
        });

        \App\Services\WhatsAppService::notifyRegistration($queue);

        broadcast(new QueueUpdated('new', $queue, $this->getStats()));

        return response()->json([
            'success'      => true,
            'queue_number' => $queue->queue_number,
            'message'      => "Antrian {$queue->queue_number} berhasil dibuat",
        ]);
    }

    /**
     * Panggil antrian berikutnya
     */
    public function callNext(Request $request)
    {
        $request->validate([
            'counter_id' => 'required|exists:queue_counters,id',
            'service_id' => 'nullable|exists:queue_services,id',
        ]);

        $counter = QueueCounter::findOrFail($request->counter_id);

        // Selesaikan antrian yang sedang dilayani
        if ($counter->current_queue_id) {
            $current = Queue::find($counter->current_queue_id);
            if ($current && in_array($current->status, ['calling', 'serving'])) {
                $current->update(['status' => 'done', 'done_at' => now()]);
                broadcast(new QueueUpdated('done', $current, []));
            }
        }

        // Ambil antrian waiting berikutnya
        $query = Queue::whereDate('queue_date', today())
            ->where('status', 'waiting')
            ->orderBy('created_at'); // FIFO murni

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        $nextQueue = $query->first();

        if (!$nextQueue) {
            return response()->json(['message' => 'Tidak ada antrian yang menunggu', 'success' => false]);
        }

        $nextQueue->update([
            'status'     => 'calling',
            'counter_id' => $counter->id,
            'called_at'  => now(),
        ]);

        $counter->update(['current_queue_id' => $nextQueue->id]);

        broadcast(new QueueCalled($nextQueue));
        broadcast(new QueueUpdated('called', $nextQueue, $this->getStats()));

        // Notifikasi WA Panggilan
        \App\Services\WhatsAppService::notifyCalled($nextQueue);

        return response()->json([
            'success'      => true,
            'queue_number' => $nextQueue->queue_number,
            'patient_name' => $nextQueue->patient_name,
            'message'      => "Memanggil {$nextQueue->queue_number} — {$nextQueue->patient_name}",
        ]);
    }

    /**
     * Ubah status antrian
     */
    public function updateStatus(Request $request, Queue $queue)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $updates = ['status' => $request->status];

        if ($request->status === 'done' || $request->status === 'rejected') {
            $updates['done_at'] = now();
            
            // Lepas dari loket jika ada
            if ($queue->counter_id) {
                $counter = QueueCounter::find($queue->counter_id);
                if ($counter && $counter->current_queue_id === $queue->id) {
                    $counter->update(['current_queue_id' => null]);
                }
            }
        } elseif ($request->status === 'prodi') {
            $updates['served_at'] = now();
        }

        $queue->update($updates);
        broadcast(new QueueUpdated($request->status, $queue, $this->getStats()));

        // Notifikasi WA Update Status
        \App\Services\WhatsAppService::notifyStatusUpdate($queue);

        return response()->json(['success' => true, 'message' => 'Status surat diperbarui ke: ' . $queue->status_label]);
    }

    /**
     * Panggil ulang
     */
    public function recall(Queue $queue)
    {
        // Allow recall for calling status, or re-call a waiting queue
        if (!in_array($queue->status, ['calling', 'serving'])) {
            return response()->json(['success' => false, 'message' => 'Antrian tidak bisa dipanggil ulang']);
        }

        $queue->update(['called_at' => now(), 'status' => 'calling']);
        broadcast(new QueueCalled($queue));
        broadcast(new QueueUpdated('called', $queue, $this->getStats()));

        // Notifikasi WA Panggilan
        \App\Services\WhatsAppService::notifyCalled($queue);

        return response()->json(['success' => true, 'message' => "Memanggil ulang {$queue->queue_number}"]);
    }

    /**
     * Reset antrian hari ini
     */
    public function resetToday()
    {
        Queue::whereDate('queue_date', today())->delete();
        QueueCounter::where('is_active', true)->update(['current_queue_id' => null]);

        broadcast(new QueueUpdated('reset', null, $this->getStats()));

        return response()->json(['success' => true, 'message' => 'Antrian hari ini telah direset']);
    }

    /**
     * API: daftar antrian hari ini
     */
    public function todayList(Request $request)
    {
        $query = Queue::with(['service', 'counter'])
            ->whereDate('queue_date', today());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $queues = $query->orderBy('service_id')->orderBy('sequence')->get()
            ->map(fn($q) => [
                'id'            => $q->id,
                'queue_number'  => $q->queue_number,
                'patient_name'  => $q->patient_name,
                'phone'         => $q->phone,
                'status'        => $q->status,
                'status_label'  => $q->status_label,
                'service_name'  => $q->service?->name,
                'service_color' => $q->service?->color,
                'counter_name'  => $q->counter?->name,
                'called_at'     => $q->called_at?->format('H:i:s'),
                'created_at'    => $q->created_at->format('H:i:s'),
            ]);

        return response()->json($queues);
    }

    /**
     * Get Queue stats - shared between admin & monitor
     */
    public function getStats(): array
    {
        return app(QueueController::class)->getStats();
    }

    /**
     * API stats endpoint
     */
    public function statsApi()
    {
        return response()->json($this->getStats());
    }
}
