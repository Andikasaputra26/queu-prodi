@extends('layouts.app')

@section('title', 'Tambah User — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title"><i class="fas fa-user-plus" style="color: var(--primary)"></i> Tambah User Baru</h1>
        <div class="page-sub">Daftarkan akun administrator, staf, atau dosen baru</div>
    </div>
    <div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #EF4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <h4 style="margin-bottom: 0.5rem; font-weight: 700;"><i class="fas fa-exclamation-circle"></i> Terjadi Kesalahan:</h4>
        <ul style="margin-left: 1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        
        <div class="form-group">
            <label class="form-label" for="name">Nama Lengkap <span style="color: var(--danger)">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required placeholder="Masukkan nama lengkap" value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <label class="form-label" for="username">Username <span style="color: var(--danger)">*</span></label>
            <input type="text" name="username" id="username" class="form-control" required placeholder="Masukkan username (tanpa spasi)" value="{{ old('username') }}">
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Alamat Email <span style="color: var(--danger)">*</span></label>
            <input type="email" name="email" id="email" class="form-control" required placeholder="Contoh: user@admin.com" value="{{ old('email') }}">
            <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; display: block;">
                Penting: Pastikan email ini juga sesuai jika ingin menggunakan akses Google Login.
            </span>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password <span style="color: var(--danger)">*</span></label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="Minimal 8 karakter">
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">Konfirmasi Password <span style="color: var(--danger)">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Ketik ulang password">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem; width: 100%; justify-content: center; padding: 0.8rem;">
            <i class="fas fa-save"></i> Simpan User Baru
        </button>
    </form>
</div>
@endsection
