@extends('layouts.app')

@section('title', 'Panel Dosen / Staf — SI-PINTER')

@push('styles')
    <style>
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 900;
        }

        .page-sub {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-top: 0.2rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 900px) {
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .mini-stat {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .mini-stat::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
        }

        .ms-total::before {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .ms-pending::before {
            background: #94A3B8;
        }

        .ms-admin::before {
            background: var(--warning);
        }

        .ms-prodi::before {
            background: var(--accent);
        }

        .ms-ready::before {
            background: var(--success);
        }

        .ms-done::before {
            background: #10B981;
        }

        .mini-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
        }

        .ms-total .mini-num {
            color: var(--primary);
        }

        .ms-pending .mini-num {
            color: #94A3B8;
        }

        .ms-admin .mini-num {
            color: var(--warning);
        }

        .ms-prodi .mini-num {
            color: var(--accent);
        }

        .ms-ready .mini-num {
            color: var(--success);
        }

        .ms-done .mini-num {
            color: #10B981;
        }

        .mini-lbl {
            font-size: 0.65rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 0.25rem;
        }

        .admin-layout {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 950px) {
            .admin-layout {
                grid-template-columns: 1fr;
            }
        }

        .counter-block {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.1rem;
            margin-bottom: 0.875rem;
            transition: all 0.25s;
        }

        .counter-block:hover {
            border-color: rgba(37, 99, 235, 0.25);
        }

        .counter-block.is-serving {
            border-color: rgba(245, 158, 11, 0.4);
            background: rgba(245, 158, 11, 0.04);
        }

        .cb-hdr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.875rem;
        }

        .cb-title {
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .cb-officer {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        .cb-active {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 10px;
            padding: 0.7rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .cb-qnum {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--warning);
            line-height: 1;
        }

        .cb-qinfo {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.2rem;
        }

        .cb-qbtns {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            flex-shrink: 0;
        }

        .cb-qbtns .btn {
            padding: 0.3rem 0.65rem;
            font-size: 0.72rem;
        }

        .cb-empty {
            background: var(--surface-3);
            border-radius: 8px;
            padding: 0.6rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 0.75rem;
            font-style: italic;
        }

        .cb-foot {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .cb-foot .btn {
            padding: 0.45rem 0.8rem;
            font-size: 0.78rem;
        }

        .svc-filter {
            background: var(--surface-3);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-family: inherit;
            font-size: 0.75rem;
            padding: 0.4rem 0.65rem;
            cursor: pointer;
            outline: none;
            flex: 1;
            min-width: 0;
        }

        .q-search-wrap {
            position: relative;
            margin-bottom: 0.75rem;
        }

        .q-search-wrap i {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .q-search {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            background: var(--surface-3);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: inherit;
            font-size: 0.875rem;
            outline: none;
            transition: all 0.2s;
        }

        .q-search:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .tab-nav {
            display: flex;
            gap: 0.35rem;
            margin-bottom: 0.875rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.75rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            padding: 0.4rem 0.875rem;
            border-radius: 8px;
            border: none;
            font-family: inherit;
            font-size: 0.78rem;
            font-weight: 600;
            cursor: pointer;
            color: var(--text-muted);
            background: transparent;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: var(--text);
            background: var(--surface-3);
        }

        .tab-btn.active {
            color: var(--text);
            background: rgba(37, 99, 235, 0.15);
        }

        .q-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0;
            border-bottom: 1px solid var(--border);
        }

        .q-row:last-child {
            border-bottom: none;
        }

        .q-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1rem;
            font-weight: 700;
            min-width: 55px;
        }

        .q-name {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .q-meta {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 0.1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .scrollable {
            max-height: 460px;
            overflow-y: auto;
        }

        .scrollable::-webkit-scrollbar {
            width: 4px;
        }

        .scrollable::-webkit-scrollbar-thumb {
            background: var(--surface-3);
            border-radius: 99px;
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem;
            color: var(--text-muted);
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .danger-strip {
            background: rgba(239, 68, 68, 0.05);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.75rem;
            gap: 0.75rem;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 9000;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.open {
            display: flex;
        }

        .modal-box {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            width: 100%;
            max-width: 460px;
            animation: modalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
        }

        @keyframes modalIn {
            from {
                transform: scale(0.85);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-title {
            font-size: 1.1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--surface-3);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            font-size: 1.1rem;
            cursor: pointer;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: var(--danger);
            color: white;
        }

        .modal-box {
            position: relative;
        }

        @media print {
            body>*:not(#print-area) {
                display: none !important;
            }

            #print-area {
                display: block !important;
                font-family: monospace;
                text-align: center;
                padding: 1rem;
            }
        }

        #print-area {
            display: none;
        }

        /* DataTable Overrides */
        .dataTables_wrapper {
            padding: 1rem;
            font-size: 0.85rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.3rem 0.6rem;
            margin-left: 0.5rem;
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 0.3rem;
            outline: none;
        }

        table.dataTable.no-footer {
            border-bottom: 1px solid var(--border);
        }

        table.dataTable thead th,
        table.dataTable thead td {
            border-bottom: 2px solid var(--border);
        }

        table.dataTable tbody th,
        table.dataTable tbody td {
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush

@section('content')

    <div id="print-area"></div>

    <div class="modal-overlay" id="manual-modal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeModal()">×</button>
            <div class="modal-title">
                <i class="fas fa-file-medical" style="color:var(--primary)"></i>
                Input Pengajuan Surat Baru
            </div>
            <div class="form-group">
                <label class="form-label">Jenis Surat <span style="color:var(--danger)">*</span></label>
                <select class="form-control" id="manual-service" required>
                    <option value="">— Pilih Jenis Surat —</option>
                    @foreach($services as $svc)
                        <option value="{{ $svc->id }}" data-color="{{ $svc->color }}">{{ $svc->code }} — {{ $svc->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Mahasiswa <span style="color:var(--danger)">*</span></label>
                <input type="text" class="form-control" id="manual-name" placeholder="Nama lengkap mahasiswa">
            </div>
            <div class="form-group">
                <label class="form-label">No.HP/WA <span style="color:var(--danger)">*</span></label>
                <input type="text" class="form-control" id="manual-phone" placeholder="Contoh: 08123456789" required>
            </div>
            <button class="btn btn-primary" style="width:100%; justify-content:center; padding:0.875rem;"
                onclick="submitManual()">
                <i class="fas fa-save"></i> Daftarkan Pengajuan
            </button>
        </div>
    </div>

    <div class="page-header">
        <div>
            <h1 class="page-title">📄 Pelacakan Surat Mahasiswa</h1>
            <div class="page-sub">Panel Administrasi Jurusan — Semua Riwayat</div>
        </div>
        <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
            <a href="{{ route('admin.users.index') }}" class="btn" style="background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; font-weight: 600;">
                <i class="fas fa-users"></i> Kelola User
            </a>
            <a href="{{ route('admin.services.index') }}" class="btn" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; font-weight: 600;">
                <i class="fas fa-tags"></i> Jenis Surat
            </a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-danger" style="background: #DC2626; color: white; border: none; font-weight: 600;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
        <div class="mini-stat ms-total">
            <div class="mini-num">{{ $todayStats['total'] }}</div>
            <div class="mini-lbl">Total Semua</div>
        </div>
        <div class="mini-stat ms-pending">
            <div class="mini-num">{{ $todayStats['pending'] }}</div>
            <div class="mini-lbl">Diajukan</div>
        </div>
        <div class="mini-stat ms-admin">
            <div class="mini-num">{{ $todayStats['admin'] }}</div>
            <div class="mini-lbl">Diproses</div>
        </div>
        <div class="mini-stat ms-done">
            <div class="mini-num">{{ $todayStats['done'] }}</div>
            <div class="mini-lbl">Selesai</div>
        </div>
    </div>

    <div class="admin-layout">
        <div>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-tasks" style="color:var(--primary)"></i>
                        Tahapan Dokumen
                    </h2>
                </div>

                <div style="padding: 1.25rem;">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                        Gunakan daftar di sebelah kanan untuk memilih surat, lalu perbarui statusnya di sini atau langsung pada baris surat.
                    </p>

                    <div class="counter-block" style="background: #F8FAFC; border: 1px solid var(--border); border-radius: 12px; padding: 1rem; margin-bottom: 1rem;">
                        <div class="cb-title" style="margin-bottom: 0.75rem; font-weight: 700; font-size: 0.85rem;">
                            <i class="fas fa-info-circle" style="color: var(--primary);"></i> Panduan Status
                        </div>
                        <div style="font-size: 0.78rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.6rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge" style="background: var(--primary); color: white; min-width: 75px; text-align: center; font-size: 0.68rem; padding: 0.25rem 0.5rem;">DIAJUKAN</span> 
                                <span>Mahasiswa baru saja mendaftar.</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge" style="background: #4f46e5; color: white; min-width: 75px; text-align: center; font-size: 0.68rem; padding: 0.25rem 0.5rem;">DIPROSES</span> 
                                <span>Surat sedang diperiksa & diproses.</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="badge" style="background: var(--success); color: white; min-width: 75px; text-align: center; font-size: 0.68rem; padding: 0.25rem 0.5rem;">SELESAI</span> 
                                <span>Surat sudah selesai dan dapat diambil.</span>
                            </div>
                        </div>
                    </div>

                    <div class="danger-strip" style="display: flex; flex-direction: column; align-items: stretch; gap: 0.75rem; background: rgba(220, 38, 38, 0.03); border: 1px solid rgba(220, 38, 38, 0.15); border-radius: 12px; padding: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: var(--danger); font-weight: 700; font-size: 0.85rem;">
                            <i class="fas fa-history"></i> Bersihkan Data
                        </div>
                        <p style="font-size: 0.75rem; color: var(--danger); font-style: italic; margin: 0;">Hapus semua riwayat hari ini</p>
                        <button class="btn btn-danger" onclick="resetToday()" style="width: 100%; justify-content: center; font-size: 0.8rem; padding: 0.6rem; background: var(--danger); color: white; border-radius: 8px; border: none; font-weight: 600;">
                            Reset Data
                        </button>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="profile-avatar" style="width: 48px; height: 48px; border-radius: 50%; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #64748B; border: 2px solid var(--primary); box-shadow: var(--glow); flex-shrink: 0;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h3>{{ Auth::user()->name ?? 'unknown' }}</h3>
                    <p>Akses Terakhir: Hari ini, {{ session('login_time', now()->format('H:i')) }}</p>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="height:100%">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <h2 class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-list" style="color:var(--primary)"></i>
                            Daftar Pengajuan Surat
                        </h2>
                        <span id="q-total-lbl" style="font-size:0.8rem; color:var(--text-muted); font-weight: 500;"></span>
                    </div>
                    <button class="btn" onclick="exportReport()"
                        style="background: #047857; color: white; border: none; font-weight: 600; padding: 0.6rem 1.2rem; border-radius: 10px; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-excel"></i> Export Report
                    </button>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap; align-items: center;">
                    <input type="date" class="q-search" id="filter-date" style="flex: 1; padding-left: 1rem;"
                        onchange="refreshQueues()">
                    <select class="q-search" id="filter-status" style="flex: 1; padding-left: 1rem; appearance: auto;"
                        onchange="refreshQueues()">
                        <option value="">-- Semua Status --</option>
                        <option value="pending">Diajukan</option>
                        <option value="admin">Diproses</option>
                        <option value="done">Selesai</option>
                        <option value="picked_up">Selesai & Diambil</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                    <select class="q-search" id="filter-service" style="flex: 1; padding-left: 1rem; appearance: auto;"
                        onchange="refreshQueues()">
                        <option value="">-- Semua Jenis Surat --</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                        @endforeach
                    </select>
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-input" id="search-input" placeholder="Cari data...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="queues-table" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>No. Antrian</th>
                                <th>Nama</th>
                                <th>No. HP/WA</th>
                                <th>Jenis Surat</th>
                                <th>File Hasil</th>
                                <th>Status</th>
                                <th style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.AdminConfig = {
            routes: {
                addManual: '{{ route("admin.add-manual") }}',
                callNext: '{{ route("admin.call-next") }}',
                resetToday: '{{ route("admin.reset-today") }}'
            },
            reverb: {
                key: '{{ env("VITE_REVERB_APP_KEY") }}',
                host: '{{ env("VITE_REVERB_HOST", "localhost") }}',
                port: {{ env("VITE_REVERB_PORT", 8080) }}
            }
        };
    </script>
    <script src="{{ asset('js/admin-dashboard.js') }}"></script>
@endpush