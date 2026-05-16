<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueCounter;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        $counters = QueueCounter::all();
        return view('admin.counters.index', compact('counters'));
    }

    public function create()
    {
        return view('admin.counters.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'officer_name' => 'required|string|max:255',
        ]);
        $validated['is_active'] = $request->has('is_active');

        QueueCounter::create($validated);
        return redirect()->route('admin.counters.index')->with('success', 'Dosen/Loket berhasil ditambahkan.');
    }

    public function edit(QueueCounter $counter)
    {
        return view('admin.counters.edit', compact('counter'));
    }

    public function update(Request $request, QueueCounter $counter)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'officer_name' => 'required|string|max:255',
        ]);
        $validated['is_active'] = $request->has('is_active');

        $counter->update($validated);
        return redirect()->route('admin.counters.index')->with('success', 'Dosen/Loket berhasil diubah.');
    }

    public function destroy(QueueCounter $counter)
    {
        $counter->delete();
        return redirect()->route('admin.counters.index')->with('success', 'Dosen/Loket berhasil dihapus.');
    }

    public function toggleActive(QueueCounter $counter)
    {
        $counter->update(['is_active' => !$counter->is_active]);
        return redirect()->back()->with('success', 'Status loket berhasil diubah.');
    }
}
