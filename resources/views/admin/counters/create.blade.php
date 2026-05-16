@extends('layouts.app')

@section('title', 'Tambah Dosen/Loket — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Tambah Dosen / Loket</h1>
        <div class="page-sub">Tambahkan dosen atau loket pelayanan baru</div>
    </div>
    <div>
        <a href="{{ route('admin.counters.index') }}" class="btn btn-ghost">Kembali</a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('admin.counters.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Nama Loket</label>
            <input type="text" name="name" class="form-control" required placeholder="Contoh: Loket 1">
        </div>
        <div class="form-group">
            <label class="form-label">Nama Petugas/Dosen</label>
            <input type="text" name="officer_name" class="form-control" required placeholder="Contoh: Dr. Andi">
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked>
            <label for="is_active" class="form-label" style="margin-bottom: 0;">Aktif</label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Simpan</button>
    </form>
</div>
@endsection
