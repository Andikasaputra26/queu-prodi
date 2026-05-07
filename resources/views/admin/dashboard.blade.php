@extends('layouts.app')

@section('title', 'Panel Dosen / Staf — Queu')

@push('styles')
<style>
    .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem;
    }
    .page-title { font-size: 1.6rem; font-weight: 900; }
    .page-sub   { color: var(--text-muted); font-size: 0.875rem; margin-top: 0.2rem; }

    /* Stats */
    .stats-row {
        display: grid; grid-template-columns: repeat(6, 1fr);
        gap: 0.75rem; margin-bottom: 1.5rem;
    }
    @media (max-width: 900px) { .stats-row { grid-template-columns: repeat(3, 1fr); } }

    .mini-stat {
        background: var(--glass); border: 1px solid var(--border);
        border-radius: 14px; padding: 1rem;
        text-align: center; position: relative; overflow: hidden;
    }
    .mini-stat::before {
        content: ''; position: absolute;
        top: 0; left: 0; right: 0; height: 2px;
    }
    .ms-total::before  { background: linear-gradient(90deg, var(--primary), var(--secondary)); }
    .ms-pending::before { background: #94A3B8; }
    .ms-admin::before   { background: var(--warning); }
    .ms-prodi::before   { background: var(--accent); }
    .ms-ready::before   { background: var(--success); }
    .ms-done::before    { background: #10B981; }

    .mini-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2rem; font-weight: 900; line-height: 1;
    }
    .ms-total .mini-num   { color: var(--primary); }
    .ms-pending .mini-num { color: #94A3B8; }
    .ms-admin .mini-num   { color: var(--warning); }
    .ms-prodi .mini-num   { color: var(--accent); }
    .ms-ready .mini-num   { color: var(--success); }
    .ms-done .mini-num    { color: #10B981; }

    .mini-lbl { font-size: 0.65rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 0.25rem; }

    /* Layout */
    .admin-layout {
        display: grid; grid-template-columns: 360px 1fr;
        gap: 1.5rem;
    }
    @media (max-width: 950px) { .admin-layout { grid-template-columns: 1fr; } }

    /* Counter cards */
    .counter-block {
        background: var(--surface-2); border: 1px solid var(--border);
        border-radius: 14px; padding: 1.1rem;
        margin-bottom: 0.875rem; transition: all 0.25s;
    }
    .counter-block:hover     { border-color: rgba(37,99,235,0.25); }
    .counter-block.is-serving { border-color: rgba(245,158,11,0.4); background: rgba(245,158,11,0.04); }

    .cb-hdr {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 0.875rem;
    }
    .cb-title {
        font-size: 0.85rem; font-weight: 700;
        display: flex; align-items: center; gap: 0.4rem;
    }
    .cb-officer { font-size: 0.72rem; color: var(--text-muted); font-weight: 400; }

    .cb-active {
        background: rgba(245,158,11,0.1);
        border: 1px solid rgba(245,158,11,0.25);
        border-radius: 10px; padding: 0.7rem;
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 0.5rem; margin-bottom: 0.75rem;
    }
    .cb-qnum { font-family: 'JetBrains Mono', monospace; font-size: 1.6rem; font-weight: 900; color: var(--warning); line-height: 1; }
    .cb-qinfo{ font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem; }
    .cb-qbtns{ display: flex; flex-direction: column; gap: 0.3rem; flex-shrink: 0; }
    .cb-qbtns .btn { padding: 0.3rem 0.65rem; font-size: 0.72rem; }

    .cb-empty {
        background: var(--surface-3); border-radius: 8px;
        padding: 0.6rem; font-size: 0.8rem;
        color: var(--text-muted); text-align: center;
        margin-bottom: 0.75rem; font-style: italic;
    }

    .cb-foot { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
    .cb-foot .btn { padding: 0.45rem 0.8rem; font-size: 0.78rem; }

    .svc-filter {
        background: var(--surface-3); border: 1px solid var(--border);
        border-radius: 8px; color: var(--text); font-family: inherit; font-size: 0.75rem;
        padding: 0.4rem 0.65rem; cursor: pointer; outline: none; flex: 1; min-width: 0;
    }

    /* Queue table */
    .q-search-wrap {
        position: relative; margin-bottom: 0.75rem;
    }
    .q-search-wrap i { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem; }
    .q-search { width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; background: var(--surface-3); border: 1px solid var(--border); border-radius: 10px; color: var(--text); font-family: inherit; font-size: 0.875rem; outline: none; transition: all 0.2s; }
    .q-search:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }

    .tab-nav {
        display: flex; gap: 0.35rem; margin-bottom: 0.875rem;
        border-bottom: 1px solid var(--border); padding-bottom: 0.75rem; flex-wrap: wrap;
    }
    .tab-btn {
        padding: 0.4rem 0.875rem; border-radius: 8px; border: none;
        font-family: inherit; font-size: 0.78rem; font-weight: 600;
        cursor: pointer; color: var(--text-muted); background: transparent; transition: all 0.2s;
    }
    .tab-btn:hover  { color: var(--text); background: var(--surface-3); }
    .tab-btn.active { color: var(--text); background: rgba(37,99,235,0.15); }

    .q-row {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.65rem 0; border-bottom: 1px solid var(--border);
    }
    .q-row:last-child { border-bottom: none; }
    .q-num  { font-family: 'JetBrains Mono', monospace; font-size: 1rem; font-weight: 700; min-width: 55px; }
    .q-name { font-size: 0.85rem; font-weight: 600; }
    .q-meta { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .scrollable { max-height: 460px; overflow-y: auto; }
    .scrollable::-webkit-scrollbar { width: 4px; }
    .scrollable::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 99px; }

    .empty-state { text-align: center; padding: 2.5rem; color: var(--text-muted); }
    .empty-icon  { font-size: 3rem; margin-bottom: 0.5rem; }

    .danger-strip {
        background: rgba(239,68,68,0.05);
        border: 1px solid rgba(239,68,68,0.2);
        border-radius: 12px; padding: 0.875rem 1rem;
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 0.75rem; gap: 0.75rem;
    }

    /* ── Modal ── */
    .modal-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.75);
        backdrop-filter: blur(8px);
        z-index: 9000;
        display: none; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2rem;
        width: 100%; max-width: 460px;
        animation: modalIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
        box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    }
    @keyframes modalIn { from { transform: scale(0.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }

    .modal-title {
        font-size: 1.1rem; font-weight: 800;
        display: flex; align-items: center; gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .modal-close {
        position: absolute; top: 1rem; right: 1rem;
        background: var(--surface-3); border: none; border-radius: 50%;
        width: 32px; height: 32px; font-size: 1.1rem; cursor: pointer;
        color: var(--text-muted); display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .modal-close:hover { background: var(--danger); color: white; }

    .modal-box { position: relative; }

    /* ── Print styles ── */
    @media print {
        body > *:not(#print-area) { display: none !important; }
        #print-area {
            display: block !important;
            font-family: monospace;
            text-align: center;
            padding: 1rem;
        }
    }

    #print-area { display: none; }
</style>
@endpush

@section('content')

<!-- Print area (hidden, shown on print only) -->
<div id="print-area"></div>

<!-- ── Modal Tambah Manual ── -->
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
                <option value="{{ $svc->id }}" data-color="{{ $svc->color }}">{{ $svc->code }} — {{ $svc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Nama Mahasiswa <span style="color:var(--danger)">*</span></label>
            <input type="text" class="form-control" id="manual-name" placeholder="Nama lengkap mahasiswa">
        </div>
        <div class="form-group">
            <label class="form-label">NIM / No. HP (Untuk Notifikasi)</label>
            <input type="text" class="form-control" id="manual-phone" placeholder="Contoh: 08123456789">
        </div>
        <button class="btn btn-primary" style="width:100%; justify-content:center; padding:0.875rem;" onclick="submitManual()">
            <i class="fas fa-save"></i> Daftarkan Pengajuan
        </button>
    </div>
</div>

<!-- ── Page header ── -->
<div class="page-header">
    <div>
        <h1 class="page-title">📄 Pelacakan Surat Mahasiswa</h1>
        <div class="page-sub">Panel Administrasi Prodi — Semua Riwayat</div>
    </div>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
        <div class="ws-status"><div class="ws-dot" id="ws-dot"></div><span id="ws-text">Live</span></div>
        <a href="{{ route('monitor.monitor') }}" class="btn btn-ghost">
            <i class="fas fa-chart-bar"></i> Monitor
        </a>
        <a href="{{ route('monitor.display') }}" class="btn btn-ghost" target="_blank">
            <i class="fas fa-tv"></i> Display
        </a>
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah Manual
        </button>
    </div>
</div>

<!-- ── Stats ── -->
<div class="stats-row">
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
        <div class="mini-lbl">Verif Admin</div>
    </div>
    <div class="mini-stat ms-prodi">
        <div class="mini-num">{{ $todayStats['prodi'] }}</div>
        <div class="mini-lbl">Di Prodi</div>
    </div>
    <div class="mini-stat ms-ready">
        <div class="mini-num">{{ $todayStats['ready'] }}</div>
        <div class="mini-lbl">Siap Ambil</div>
    </div>
    <div class="mini-stat ms-done">
        <div class="mini-num">{{ $todayStats['done'] }}</div>
        <div class="mini-lbl">Selesai</div>
    </div>
</div>

<!-    <!-- KIRI: Kontrol Administrasi -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-tasks" style="color:var(--primary)"></i>
                    Tahapan Dokumen
                </h2>
            </div>
            
            <div style="padding: 1.25rem;">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                    Gunakan daftar di sebelah kanan untuk memilih surat, lalu perbarui statusnya di sini atau langsung pada baris surat.
                </p>
                
                <div class="counter-block">
                    <div class="cb-title" style="margin-bottom: 0.5rem;">⚙️ Panduan Status</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.5rem;">
                        <div><span class="badge badge-pending">Diajukan</span> Mahasiswa baru saja mendaftar.</div>
                        <div><span class="badge badge-calling">Verifikasi Admin</span> Surat sedang diperiksa berkasnya.</div>
                        <div><span class="badge badge-serving">Proses di Prodi</span> Surat sedang menunggu tanda tangan/persetujuan.</div>
                        <div><span class="badge badge-done">Siap Diambil</span> Surat sudah selesai dan menunggu mahasiswa.</div>
                    </div>
                </div>

                <div class="danger-strip">
                    <div>
                        <div style="font-size:0.8rem; font-weight:600; color:var(--danger)">
                            <i class="fas fa-history"></i> Bersihkan Data
                        </div>
                        <div style="font-size:0.7rem; color:var(--text-muted); margin-top:0.1rem">Hapus semua riwayat hari ini</div>
                    </div>
                    <button class="btn btn-danger" onclick="resetToday()" style="font-size:0.8rem; padding:0.45rem 0.875rem; flex-shrink:0">
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- KANAN: Daftar Antrian -->
    <div>
        <div class="card" style="height:100%">
            <div class="card-header" style="flex-wrap:wrap; gap:0.5rem">
                <h2 class="card-title">
                    <i class="fas fa-stream" style="color:var(--primary)"></i>
                    Daftar Pengajuan Surat
                </h2>
                <span id="q-total-lbl" style="font-size:0.78rem; color:var(--text-muted)"></span>
            </div>

            <!-- Filter -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                <input type="date" class="q-search" id="filter-date" 
                       style="flex: 1; padding-left: 1rem;" 
                       onchange="refreshQueues()">
                <select class="q-search" id="filter-service" 
                        style="flex: 1; padding-left: 1rem; appearance: auto;" 
                        onchange="refreshQueues()">
                    <option value="">— Semua Jenis Surat —</option>
                    @foreach($services as $svc)
                    <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search -->
            <div class="q-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="q-search" id="q-search"
                       placeholder="Cari nama, nomor antrian, atau NIM..."
                       oninput="filterQueues()">
            </div>

            <!-- Tabs -->
            <div class="tab-nav">
                <button class="tab-btn active" onclick="switchTab('pending', this)">📥 Baru</button>
                <button class="tab-btn" onclick="switchTab('admin', this)">🔍 Admin</button>
                <button class="tab-btn" onclick="switchTab('prodi', this)">🏛️ Prodi</button>
                <button class="tab-btn" onclick="switchTab('ready', this)">🎁 Siap</button>
                <button class="tab-btn" onclick="switchTab('done', this)">✅ Selesai</button>
                <button class="tab-btn" onclick="switchTab('rejected', this)">❌ Ditolak</button>
                <button class="tab-btn" onclick="switchTab('', this)">📋 Semua</button>
            </div>

            <div class="scrollable" id="q-list"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
