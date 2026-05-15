@extends('layouts.app')

@section('title', 'Edit Jenis Surat — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Edit Jenis Surat</h1>
        <div class="page-sub">Edit data layanan atau surat</div>
    </div>
    <div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-ghost">Kembali</a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('admin.services.update', $service->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Kode Surat</label>
            <input type="text" name="code" class="form-control" value="{{ $service->code }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Nama Surat</label>
            <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
        </div>
        <div class="form-group">
            <label class="form-label">Warna (Badge/Token)</label>
            <input type="text" name="color" class="form-control" value="{{ $service->color }}" required>
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ $service->is_active ? 'checked' : '' }}>
            <label for="is_active" class="form-label" style="margin-bottom: 0;">Aktif</label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Update</button>
    </form>
</div>
@endsection
