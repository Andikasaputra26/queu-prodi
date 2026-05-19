@extends('layouts.app')

@section('title', 'Kelola Jenis Surat — SI-PINTER')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.25rem;
        }
        .page-sub {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .dataTables_wrapper {
            padding: 1rem;
            font-size: 0.85rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary);
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.2rem;
            outline: none;
        }
        table.dataTable thead th {
            border-bottom: 2px solid var(--border) !important;
            color: var(--text-muted);
            font-weight: 600;
        }
        table.dataTable tbody td {
            border-bottom: 1px solid var(--surface-3);
            vertical-align: middle;
        }
        .color-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
    </style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-layer-group" style="color: var(--primary)"></i> Jenis Surat</h1>
        <div class="page-sub">Kelola layanan dan jenis surat</div>
    </div>
    <div>
        <a href="{{ route('admin.services.create') }}" class="btn btn-primary" style="border-radius: 8px;">
            <i class="fas fa-plus"></i> Tambah Jenis Surat
        </a>
    </div>
</div>

<div class="card" style="padding: 0;">
    <div class="table-responsive">
        <table class="display" id="services-table" style="width: 100%;">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Layanan</th>
                    <th>Warna Label</th>
                    <th>Status</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $svc)
                <tr>
                    <td style="font-weight: 600; color: var(--primary);">{{ $svc->code }}</td>
                    <td style="font-weight: 500;">{{ $svc->name }}</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <span class="color-dot" style="background-color: var(--{{ $svc->color }}, {{ $svc->color }});"></span> 
                            {{ $svc->color }}
                        </div>
                    </td>
                    <td>
                        <form action="{{ route('admin.services.toggle-active', $svc->id) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" style="padding: 0.3rem 0.8rem; font-size: 0.75rem; border-radius: 99px; border: none; cursor: pointer; font-weight: 600; background-color: {{ $svc->is_active ? 'rgba(16, 185, 129, 0.1)' : 'rgba(107, 114, 128, 0.1)' }}; color: {{ $svc->is_active ? '#10b981' : '#6b7280' }}; transition: all 0.2s;">
                                {{ $svc->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </button>
                        </form>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                            <a href="{{ route('admin.services.edit', $svc->id) }}" class="btn btn-ghost" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: var(--warning); border-color: var(--warning);">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('admin.services.destroy', $svc->id) }}" method="POST" class="delete-service-form" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-ghost btn-delete-svc" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: var(--danger); border-color: var(--danger);">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#services-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari _MAX_ total data)",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
                pageLength: 10,
                ordering: true,
                columnDefs: [
                    { orderable: false, targets: 4 } // Disable sorting on actions column
                ]
            });

            // SweetAlert Delete Confirmation
            $('.btn-delete-svc').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Hapus Jenis Surat?',
                    text: "Tindakan ini tidak bisa dibatalkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#DC2626',
                    cancelButtonColor: '#6B7280',
                    confirmButtonText: 'Ya, Hapus!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
