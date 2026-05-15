<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = QueueService::all();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:queue_services,code',
            'description' => 'nullable|string',
            'color' => 'required|string|max:50',
        ]);
        $validated['is_active'] = $request->has('is_active');

        QueueService::create($validated);
        return redirect()->route('admin.services.index')->with('success', 'Jenis Surat berhasil ditambahkan.');
    }

    public function edit(QueueService $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(Request $request, QueueService $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:queue_services,code,' . $service->id,
            'description' => 'nullable|string',
            'color' => 'required|string|max:50',
        ]);
        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);
        return redirect()->route('admin.services.index')->with('success', 'Jenis Surat berhasil diubah.');
    }

    public function destroy(QueueService $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Jenis Surat berhasil dihapus.');
    }
}
