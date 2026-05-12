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
    public function index()
    {
        $counters = QueueCounter::with(['currentQueue.service'])->where('is_active', true)->get();
        $services = QueueService::where('is_active', true)->get();

        $todayStats = [
            'total'   => Queue::count(),
            'pending' => Queue::where('status', 'pending')->count(),
            'admin'   => Queue::where('status', 'admin')->count(),
            'done'    => Queue::where('status', 'done')->count(),
        ];

        return view('admin.dashboard', compact('counters', 'services', 'todayStats'));
    }

    public function addManual(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:100',
            'phone'        => 'required|string|max:30',
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

    public function callNext(Request $request)
    {
        $request->validate([
            'counter_id' => 'required|exists:queue_counters,id',
            'service_id' => 'nullable|exists:queue_services,id',
        ]);

        $counter = QueueCounter::findOrFail($request->counter_id);

        if ($counter->current_queue_id) {
            $current = Queue::find($counter->current_queue_id);
            if ($current && in_array($current->status, ['calling', 'serving'])) {
                $current->update(['status' => 'done', 'done_at' => now()]);
                broadcast(new QueueUpdated('done', $current, []));
            }
        }

        $query = Queue::whereIn('status', ['waiting', 'pending'])
            ->orderBy('created_at');

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

        \App\Services\WhatsAppService::notifyCalled($nextQueue);

        return response()->json([
            'success'      => true,
            'queue_number' => $nextQueue->queue_number,
            'patient_name' => $nextQueue->patient_name,
            'message'      => "Memanggil {$nextQueue->queue_number} — {$nextQueue->patient_name}",
        ]);
    }

    public function updateStatus(Request $request, Queue $queue)
    {
        $request->validate([
            'status' => 'required|string',
            'reject_reason' => 'nullable|string',
            'document_number' => 'nullable|string',
        ]);

        $updates = ['status' => $request->status];
        if ($request->has('reject_reason')) {
            $updates['reject_reason'] = $request->reject_reason;
        }
        if ($request->has('document_number')) {
            $updates['document_number'] = $request->document_number;
        }

        if ($request->status === 'done' || $request->status === 'rejected') {
            $updates['done_at'] = now();
            
            if ($queue->counter_id) {
                $counter = QueueCounter::find($queue->counter_id);
                if ($counter && $counter->current_queue_id === $queue->id) {
                    $counter->update(['current_queue_id' => null]);
                }
            }
        } elseif ($request->status === 'admin') {
            $updates['served_at'] = now();
        }

        $queue->update($updates);
        broadcast(new QueueUpdated($request->status, $queue, $this->getStats()));

        \App\Services\WhatsAppService::notifyStatusUpdate($queue);

        return response()->json(['success' => true, 'message' => 'Status surat diperbarui ke: ' . $queue->status_label]);
    }

    public function requeue(Queue $queue)
    {
        if ($queue->status !== 'rejected') {
            return response()->json(['success' => false, 'message' => 'Hanya antrian yang ditolak yang bisa diantrikan ulang.']);
        }

        $queue->update([
            'status' => 'pending',
            'reject_reason' => null,
            'queue_date' => today(),
            'done_at' => null,
        ]);

        broadcast(new QueueUpdated('pending', $queue, $this->getStats()));

        \App\Services\WhatsAppService::notifyStatusUpdate($queue);

        return response()->json(['success' => true, 'message' => 'Antrian berhasil diantrikan ulang menjadi Pending.']);
    }

    public function recall(Queue $queue)
    {
        if (!in_array($queue->status, ['calling', 'serving'])) {
            return response()->json(['success' => false, 'message' => 'Antrian tidak bisa dipanggil ulang']);
        }

        $queue->update(['called_at' => now(), 'status' => 'calling']);
        broadcast(new QueueCalled($queue));
        broadcast(new QueueUpdated('called', $queue, $this->getStats()));

        \App\Services\WhatsAppService::notifyCalled($queue);

        return response()->json(['success' => true, 'message' => "Memanggil ulang {$queue->queue_number}"]);
    }

    public function resetToday()
    {
        Queue::whereDate('queue_date', today())->delete();
        QueueCounter::where('is_active', true)->update(['current_queue_id' => null]);

        broadcast(new QueueUpdated('reset', null, $this->getStats()));

        return response()->json(['success' => true, 'message' => 'Antrian hari ini telah direset']);
    }

    public function todayList(Request $request)
    {
        $query = Queue::with(['service', 'counter']);

        if ($request->date) {
            $query->whereDate('queue_date', $request->date);
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $queues = $query->orderBy('queue_date', 'desc')
            ->orderBy('service_id')
            ->orderBy('sequence')
            ->get()
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
                'created_at'    => $q->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json($queues);
    }

    public function export(Request $request)
    {
        $query = Queue::with(['service', 'counter']);

        if ($request->date) {
            $query->whereDate('queue_date', $request->date);
        }

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $queues = $query->orderBy('queue_date', 'desc')
            ->orderBy('service_id')
            ->orderBy('sequence')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Laporan_Antrian_Mahasiswa.csv"',
        ];

        $callback = function () use ($queues) {
            $file = fopen('php://output', 'w');

            // Header for CSV
            fputcsv($file, [
                'Tanggal Antrian',
                'Nomor Antrian',
                'Nama Mahasiswa',
                'No. HP/WA',
                'Jenis Surat',
                'Status',
                'Waktu Pengajuan',
                'Waktu Diproses',
                'Waktu Selesai/Ditolak',
                'Nomor Dokumen',
                'Alasan Penolakan'
            ]);

            foreach ($queues as $q) {
                fputcsv($file, [
                    $q->queue_date ? $q->queue_date->format('Y-m-d') : '-',
                    $q->queue_number,
                    $q->patient_name,
                    "'" . $q->phone, // Prevent scientific notation for long phone numbers
                    $q->service?->name ?? '-',
                    $q->status_label,
                    $q->created_at ? $q->created_at->format('H:i:s') : '-',
                    $q->served_at ? $q->served_at->format('H:i:s') : '-',
                    $q->done_at ? $q->done_at->format('H:i:s') : '-',
                    $q->document_number ?? '-',
                    $q->reject_reason ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getStats(): array
    {
        return app(QueueController::class)->getStats();
    }

    public function statsApi()
    {
        return response()->json($this->getStats());
    }
}
