@extends('layouts.app')

@section('title', 'Kelola Jenis Surat — SI-PINTER')

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">📄 Jenis Surat</h1>
        <div class="page-sub">Kelola layanan dan jenis surat</div>
    </div>
    <div>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Jenis Surat
        </a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border);">
                    <th style="padding: 1rem;">Kode</th>
                    <th style="padding: 1rem;">Nama</th>
                    <th style="padding: 1rem;">Warna</th>
                    <th style="padding: 1rem;">Status</th>
                    <th style="padding: 1rem;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $svc)
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem; font-weight: bold;">{{ $svc->code }}</td>
                    <td style="padding: 1rem;">{{ $svc->name }}</td>
                    <td style="padding: 1rem;"><span style="display:inline-block; width:20px; height:20px; border-radius:50%; background-color:var(--{{ $svc->color }}, {{ $svc->color }});"></span> {{ $svc->color }}</td>
                    <td style="padding: 1rem;">{{ $svc->is_active ? 'Aktif' : 'Non-Aktif' }}</td>
                    <td style="padding: 1rem; display: flex; gap: 0.5rem;">
                        <a href="{{ route('admin.services.edit', $svc->id) }}" class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Edit</a>
                        <form action="{{ route('admin.services.destroy', $svc->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?')">
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
