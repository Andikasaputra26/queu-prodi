<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\QueueCounter;
use App\Models\QueueService;
use Illuminate\Http\Request;

class MonitorController extends Controller
{
    /**
     * Halaman monitor antrian (display public)
     */
    public function display()
    {
        $services = QueueService::where('is_active', true)->get();
        return view('monitor.display', compact('services'));
    }

    /**
     * Halaman monitoring admin (stats lengkap)
     */
    public function monitor()
    {
        $services = QueueService::where('is_active', true)->get();
        return view('monitor.monitor', compact('services'));
    }

    /**
     * API: get counter status
     */
    public function counters()
    {
        $counters = QueueCounter::with(['currentQueue.service'])
            ->where('is_active', true)
            ->get()
            ->map(fn($c) => [
                'id'           => $c->id,
                'name'         => $c->name,
                'officer_name' => $c->officer_name,
                'current_queue' => $c->currentQueue ? [
                    'queue_number'  => $c->currentQueue->queue_number,
                    'patient_name'  => $c->currentQueue->patient_name,
                    'status'        => $c->currentQueue->status,
                    'status_label'  => $c->currentQueue->status_label,
                    'service_name'  => $c->currentQueue->service?->name,
                    'service_color' => $c->currentQueue->service?->color,
                ] : null,
            ]);

        return response()->json($counters);
    }
}
