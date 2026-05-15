@extends('layouts.app')

@section('title', 'Kelola Dosen/Loket — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">👨‍🏫 Dosen / Loket</h1>
        <div class="page-sub">Kelola loket atau dosen yang melayani</div>
    </div>
    <div>
        <a href="{{ route('admin.counters.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Dosen/Loket
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 1rem;">Nama Loket/Poli</th>
                    <th style="padding: 1rem;">Nama Petugas/Dosen</th>
                    <th style="padding: 1rem;">Status</th>
                    <th style="padding: 1rem;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($counters as $counter)
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem; font-weight: bold;">{{ $counter->name }}</td>
                    <td style="padding: 1rem;">{{ $counter->officer_name }}</td>
                    <td style="padding: 1rem;">
                        <form action="{{ route('admin.counters.toggle-active', $counter->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn {{ $counter->is_active ? 'btn-success' : 'btn-secondary' }}" style="padding: 0.2rem 0.6rem; font-size: 0.8rem; border-radius: 20px; border: none; cursor: pointer; background-color: {{ $counter->is_active ? '#10b981' : '#6b7280' }}; color: white;">
                                {{ $counter->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </button>
                        </form>
                    </td>
                    <td style="padding: 1rem; display: flex; gap: 0.5rem;">
                        <a href="{{ route('admin.counters.edit', $counter->id) }}" class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Edit</a>
                        <form action="{{ route('admin.counters.destroy', $counter->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
