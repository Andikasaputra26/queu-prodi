@extends('layouts.app')

@section('title', 'Kelola User — SI-PINTER')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
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
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #4A5568;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-users" style="color: var(--primary)"></i> Manajemen User</h1>
        <div class="page-sub">Kelola akun administrator, staf, dan dosen</div>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost" style="border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="border-radius: 8px;">
            <i class="fas fa-user-plus"></i> Tambah User
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10B981; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #EF4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="card" style="padding: 0;">
    <div class="table-responsive">
        <table class="display" id="users-table" style="width: 100%;">
            <thead>
                <tr>
                    <th style="width: 50px;">Avatar</th>
                    <th>Nama Pengguna</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Ditambahkan</th>
                    <th style="text-align: right; width: 180px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        @if($user->avatar)
                            <img src="{{ $user->avatar }}" class="user-avatar" alt="{{ $user->name }}">
                        @else
                            <div class="user-avatar">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                    </td>
                    <td style="font-weight: 600;">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                            <span style="font-size: 0.7rem; background: rgba(37, 99, 235, 0.1); color: var(--primary); padding: 0.15rem 0.4rem; border-radius: 4px; margin-left: 0.25rem;">Anda</span>
                        @endif
                    </td>
                    <td style="font-family: 'JetBrains Mono', monospace; font-weight: 500;">{{ $user->username }}</td>
                    <td style="font-family: 'JetBrains Mono', monospace;">{{ $user->email }}</td>
                    <td style="color: var(--text-muted);">{{ $user->created_at->format('d M Y, H:i') }}</td>
                    <td style="text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-ghost" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: var(--warning); border-color: var(--warning);">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="delete-user-form" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-ghost btn-delete-user" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: var(--danger); border-color: var(--danger);">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </form>
                            @endif
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
            $('#users-table').DataTable({
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
                    { orderable: false, targets: [0, 5] } // Disable sorting on avatar and action
                ]
            });

            // SweetAlert Delete Confirmation
            $('.btn-delete-user').on('click', function(e) {
                e.preventDefault();
                const form = $(this).closest('form');
                
                Swal.fire({
                    title: 'Hapus User?',
                    text: "User ini tidak akan dapat mengakses panel admin lagi!",
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
