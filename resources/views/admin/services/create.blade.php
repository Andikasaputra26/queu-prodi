@extends('layouts.app')

@section('title', 'Tambah Jenis Surat — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Tambah Jenis Surat</h1>
        <div class="page-sub">Tambahkan jenis layanan atau surat baru</div>
    </div>
    <div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-ghost">Kembali</a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <form action="{{ route('admin.services.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Kode Surat</label>
            <input type="text" name="code" class="form-control" required placeholder="Contoh: SKL">
        </div>
        <div class="form-group">
            <label class="form-label">Nama Surat</label>
            <input type="text" name="name" class="form-control" required placeholder="Contoh: Surat Keterangan Lulus">
        </div>
        <div class="form-group">
            <label class="form-label">Warna (Badge/Token)</label>
            <input type="text" name="color" class="form-control" required placeholder="Contoh: primary, success, danger, warning">
        </div>
        <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
            <input type="checkbox" name="is_active" id="is_active" value="1" checked>
            <label for="is_active" class="form-label" style="margin-bottom: 0;">Aktif</label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Simpan</button>
    </form>
</div>
@endsection
