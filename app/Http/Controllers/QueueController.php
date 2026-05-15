<?php

namespace App\Http\Controllers;

use App\Events\QueueUpdated;
use App\Models\Queue;
use App\Models\QueueCounter;
use App\Models\QueueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    public function index()
    {
        $services = QueueService::where('is_active', true)->get();
        return view('queue.register', compact('services'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:100',
            'phone'        => 'required|string|max:30',
            'service_id'   => 'required|exists:queue_services,id',
        ]);

        $queue = DB::transaction(function () use ($request) {
            $service     = QueueService::where('id', $request->service_id)->lockForUpdate()->firstOrFail();
            $sequence    = $service->getNextSequence();
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

        broadcast(new QueueUpdated(action: 'new', queueItem: $queue, stats: $this->getStats()))->toOthers();

        return redirect()->route('queue.ticket', $queue->id);
    }

    public function ticket(Queue $queue)
    {
        $waitingBefore = Queue::where('service_id', $queue->service_id)
            ->where('queue_date', $queue->queue_date)
            ->whereIn('status', ['pending', 'admin'])
            ->where('sequence', '<', $queue->sequence)
            ->count();

        return view('queue.ticket', compact('queue', 'waitingBefore'));
    }

    public function search(Request $request)
    {
        $request->validate(['queue_number' => 'required|string']);
        
        $queue = Queue::where('queue_number', strtoupper($request->queue_number))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$queue) {
            return back()->withErrors(['queue_number' => 'Nomor antrian tidak ditemukan untuk hari ini.']);
        }

        return redirect()->route('queue.ticket', $queue->id);
    }

    public function getStats(): array
    {
        $services = QueueService::where('is_active', true)->with('queues')->get();

        $stats = [];
        foreach ($services as $service) {
            $stats[$service->code] = [
                'service_name' => $service->name,
                'color'        => $service->color,
                'pending'      => $service->queues->where('status', 'pending')->count(),
                'admin'        => $service->queues->where('status', 'admin')->count(),
                'done'         => $service->queues->where('status', 'done')->count(),
                'total'        => $service->queues->count(),
            ];
        }

        $calling = Queue::with(['service', 'counter'])
            ->where('status', 'calling')
            ->latest('called_at')
            ->first();

        return [
            'services' => $stats,
            'calling'  => $calling ? [
                'queue_number'  => $calling->queue_number,
                'patient_name'  => $calling->patient_name,
                'service_name'  => $calling->service?->name,
                'service_code'  => $calling->service?->code,
                'service_color' => $calling->service?->color,
                'counter_name'  => $calling->counter?->name,
                'called_at'     => $calling->called_at?->format('H:i:s'),
            ] : null,
        ];
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    public function waitingList(Request $request)
    {
        $query = Queue::with(['service', 'counter'])
            ->whereIn('status', ['pending', 'admin', 'calling', 'serving']);

        $date = $request->date ?: today()->format('Y-m-d');
        $query->whereDate('queue_date', $date);

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        $queues = $query->orderByRaw("FIELD(status, 'admin', 'calling', 'serving', 'pending')")
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
                'queue_date'    => $q->queue_date->format('d/m/Y'),
                'created_at'    => $q->created_at->format('H:i:s'),
            ]);

        return response()->json($queues);
    }
}
