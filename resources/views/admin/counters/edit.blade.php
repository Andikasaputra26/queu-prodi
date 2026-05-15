@extends('layouts.app')

@section('title', 'Edit Dosen/Loket — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Edit Dosen / Loket</h1>
        <div class="page-sub">Edit data dosen atau loket pelayanan</div>
    </div>
    <div>
        <a href="{{ route('admin.counters.index') }}" class="btn btn-ghost">Kembali</a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('admin.counters.update', $counter->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Nama Loket</label>
            <input type="text" name="name" class="form-control" value="{{ $counter->name }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Nama Petugas/Dosen</label>
            <input type="text" name="officer_name" class="form-control" value="{{ $counter->officer_name }}" required>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ $counter->is_active ? 'checked' : '' }}>
            <label for="is_active" class="form-label" style="margin-bottom: 0;">Aktif</label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Update</button>
    </form>
</div>
@endsection
