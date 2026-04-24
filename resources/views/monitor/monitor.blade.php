@extends('layouts.app')

@section('title', 'Monitoring Real-time — Queu')

@push('styles')
<style>
    /* ── Top stat bar ── */
    .stat-bar {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    @media (max-width: 900px) { .stat-bar { grid-template-columns: repeat(3, 1fr); } }

    .stat-box {
        background: var(--glass);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.25rem 1rem;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s;
    }
    .stat-box:hover { transform: translateY(-2px); }

    .stat-box::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 2px;
    }
    .s-total::after  { background: linear-gradient(90deg, var(--primary), var(--secondary)); }
    .s-wait::after   { background: #64748B; }
    .s-call::after   { background: var(--warning); }
    .s-serve::after  { background: var(--accent); }
    .s-done::after   { background: var(--success); }

    .stat-icon { font-size: 1.5rem; margin-bottom: 0.4rem; }

    .stat-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 2.75rem; font-weight: 900; line-height: 1;
        transition: all 0.4s;
    }
    .s-total .stat-num  { color: var(--primary); }
    .s-wait  .stat-num  { color: #94A3B8; }
    .s-call  .stat-num  { color: var(--warning); }
    .s-serve .stat-num  { color: var(--accent); }
    .s-done  .stat-num  { color: var(--success); }

    .stat-lbl { font-size: 0.68rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-top: 0.3rem; }

    .stat-num.bump {
        animation: statBump 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes statBump { 0% { transform: scale(1.3); } 100% { transform: scale(1); } }

    /* ── Layout ── */
    .monitor-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
    }
    @media (max-width: 1100px) { .monitor-layout { grid-template-columns: 1fr; } }

    .left-col { display: flex; flex-direction: column; gap: 1.25rem; }
    .right-col { display: flex; flex-direction: column; gap: 1.25rem; }

    /* ── Currently called highlight ── */
    .calling-highlight {
        background: linear-gradient(135deg, rgba(245,158,11,0.12), rgba(245,158,11,0.05));
        border: 1px solid rgba(245,158,11,0.3);
        border-radius: 16px;
        padding: 1.25rem 1.5rem;
        display: flex; align-items: center; gap: 1.5rem;
        transition: all 0.4s;
    }
    .calling-highlight.has-call { box-shadow: 0 0 30px rgba(245,158,11,0.2); }

    .call-badge {
        font-size: 0.65rem; font-weight: 700;
        letter-spacing: 0.15em; text-transform: uppercase;
        color: var(--warning);
        display: flex; align-items: center; gap: 0.4rem;
        margin-bottom: 0.4rem;
    }
    .call-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: var(--warning); box-shadow: 0 0 8px var(--warning);
        animation: pulse 1s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.5;transform:scale(0.8)} }

    .call-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 3.5rem; font-weight: 900;
        line-height: 1; color: var(--warning);
        text-shadow: 0 0 30px rgba(245,158,11,0.4);
        transition: all 0.3s;
    }
    .call-num.pop { animation: statBump 0.5s cubic-bezier(0.34,1.56,0.64,1); }

    .call-divider { width: 1px; height: 60px; background: var(--border); flex-shrink: 0; }

    .call-meta { flex: 1; min-width: 0; }
    .call-name { font-size: 1.1rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .call-service { font-size: 0.82rem; color: var(--text-muted); margin-top: 0.25rem; }
    .call-room { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.4rem; font-size: 0.82rem; font-weight: 600; }

    .no-call-msg { color: var(--text-muted); font-size: 0.9rem; }

    /* ── Service breakdown bars ── */
    .svc-row {
        display: flex; align-items: center; gap: 0.875rem;
        padding: 0.7rem 0; border-bottom: 1px solid var(--border);
    }
    .svc-row:last-child { border-bottom: none; }

    .svc-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    .svc-info { flex: 1; min-width: 0; }
    .svc-name-text { font-size: 0.85rem; font-weight: 600; }
    .svc-code-text { font-size: 0.72rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }

    .svc-bar-wrap { width: 120px; }
    .svc-bar-bg { height: 5px; background: var(--surface-3); border-radius: 99px; overflow: hidden; }
    .svc-bar-fill { height: 100%; border-radius: 99px; transition: width 0.6s ease; }

    .svc-nums { display: flex; gap: 0.625rem; font-size: 0.75rem; font-family: 'JetBrains Mono', monospace; }
    .svc-w { color: #94A3B8; }
    .svc-a { color: var(--warning); }
    .svc-d { color: var(--success); }

    /* ── Queue table ── */
    .queue-table-wrap { overflow-x: auto; max-height: 380px; overflow-y: auto; }
    .queue-table-wrap::-webkit-scrollbar { width: 4px; height: 4px; }
    .queue-table-wrap::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 99px; }

    .q-table { width: 100%; border-collapse: collapse; }
    .q-table th {
        padding: 0.6rem 0.875rem;
        font-size: 0.65rem; font-weight: 700;
        letter-spacing: 0.1em; text-transform: uppercase;
        color: var(--text-muted);
        border-bottom: 1px solid var(--border);
        text-align: left; white-space: nowrap;
        position: sticky; top: 0; background: var(--surface);
        z-index: 1;
    }
    .q-table td {
        padding: 0.6rem 0.875rem;
        font-size: 0.82rem;
        border-bottom: 1px solid var(--border);
        transition: background 0.3s;
    }
    .q-table tr:last-child td { border-bottom: none; }
    .q-table tr:hover td { background: rgba(99,102,241,0.04); }
    .q-table tr.row-new td { animation: rowHighlight 2.5s ease forwards; }
    @keyframes rowHighlight { 0% { background: rgba(99,102,241,0.25); } 100% { background: transparent; } }
    .q-table tr.row-calling td { background: rgba(245,158,11,0.08); }

    .qnum-cell { font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 0.95rem; }

    /* ── Activity log ── */
    .log-list { max-height: 340px; overflow-y: auto; }
    .log-list::-webkit-scrollbar { width: 4px; }
    .log-list::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 99px; }

    .log-item {
        display: flex; align-items: flex-start; gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.8rem;
    }
    .log-item:last-child { border-bottom: none; }

    .log-icon {
        width: 30px; height: 30px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem; flex-shrink: 0;
    }

    .log-text { flex: 1; line-height: 1.4; }
    .log-num  { font-family: 'JetBrains Mono', monospace; font-weight: 700; }
    .log-time { font-size: 0.68rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; margin-top: 0.1rem; }

    /* ── Counter grid ── */
    .counter-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .cg-card {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.875rem;
        transition: all 0.3s;
    }
    .cg-card.active {
        border-color: rgba(245,158,11,0.4);
        background: rgba(245,158,11,0.05);
        box-shadow: 0 0 15px rgba(245,158,11,0.1);
    }
    .cg-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-muted); margin-bottom: 0.4rem; }
    .cg-num   { font-family: 'JetBrains Mono', monospace; font-size: 1.6rem; font-weight: 900; line-height: 1; }
    .cg-name  { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.15rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cg-off   { font-size: 0.65rem; color: var(--text-muted); opacity: 0.6; margin-top: 0.2rem; }
    .cg-empty { font-size: 0.78rem; color: var(--text-muted); font-style: italic; }

    /* ── Section header ── */
    .sec-hdr {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1rem;
    }
    .sec-title {
        font-size: 0.72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.1em;
        color: var(--text-muted);
        display: flex; align-items: center; gap: 0.5rem;
    }
    .live-pill {
        display: flex; align-items: center; gap: 0.35rem;
        font-size: 0.65rem; font-weight: 600;
        color: var(--success);
        background: rgba(16,185,129,0.1);
        border: 1px solid rgba(16,185,129,0.25);
        padding: 0.2rem 0.6rem; border-radius: 99px;
    }
    .live-pill-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--success); animation: pulse 2s infinite; }

    /* ── Tab ── */
    .tab-strip { display: flex; gap: 0.35rem; margin-bottom: 1rem; }
    .t-btn {
        padding: 0.35rem 0.75rem; border-radius: 7px;
        border: none; font-family: inherit; font-size: 0.75rem;
        font-weight: 600; cursor: pointer;
        color: var(--text-muted); background: transparent; transition: all 0.2s;
    }
    .t-btn:hover  { color: var(--text); background: var(--surface-3); }
    .t-btn.active { color: var(--text); background: rgba(99,102,241,0.15); }

    /* ── Wait time ── */
    .wait-bar {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.75rem;
        background: var(--surface-3);
        border-radius: 10px;
        margin-top: 0.75rem;
    }
    .wait-bar-icon { font-size: 1.25rem; }
    .wait-num   { font-family: 'JetBrains Mono', monospace; font-size: 1.4rem; font-weight: 900; color: var(--accent); }
    .wait-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem; }

    /* empty */
    .empty-state { text-align: center; padding: 2rem; color: var(--text-muted); }
    .empty-icon  { font-size: 2.5rem; margin-bottom: 0.5rem; }
</style>
@endpush

@section('content')

<!-- ───── Top: Judul + controls ───── -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h1 style="font-size:1.5rem; font-weight:900;">📊 Monitoring Progres Surat</h1>
        <div style="font-size:0.82rem; color:var(--text-muted); margin-top:0.2rem;">
            {{ now()->format('l, d F Y') }} &mdash;
            <span id="mon-clock" style="font-family:'JetBrains Mono',monospace"></span>
        </div>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <div class="live-pill">
            <div class="live-pill-dot"></div>
            Live WebSocket
        </div>
        <a href="{{ route('monitor.display') }}" class="btn btn-ghost" target="_blank">
            <i class="fas fa-tv"></i> Display TV
        </a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            <i class="fas fa-cog"></i> Panel Admin
        </a>
    </div>
</div>

<!-- ───── Stat bar ───── -->
<div class="stat-bar">
    <div class="stat-box s-total">
        <div class="stat-icon">📄</div>
        <div class="stat-num" id="st-total">0</div>
        <div class="stat-lbl">Total Pengajuan</div>
    </div>
    <div class="stat-box s-pending">
        <div class="stat-icon">📥</div>
        <div class="stat-num" id="st-pending">0</div>
        <div class="stat-lbl">Diajukan</div>
    </div>
    <div class="stat-box s-admin">
        <div class="stat-icon">🔍</div>
        <div class="stat-num" id="st-admin">0</div>
        <div class="stat-lbl">Verif Admin</div>
    </div>
    <div class="stat-box s-prodi">
        <div class="stat-icon">🏛️</div>
        <div class="stat-num" id="st-prodi">0</div>
        <div class="stat-lbl">Di Prodi</div>
    </div>
    <div class="stat-box s-ready">
        <div class="stat-icon">🎁</div>
        <div class="stat-num" id="st-ready">0</div>
        <div class="stat-lbl">Siap Ambil</div>
    </div>
</div>

<!-- ───── Currently Called ───── -->
<div class="calling-highlight card" id="calling-highlight" style="margin-bottom:1.5rem;">
    <div id="ch-no-call" style="display:flex; align-items:center; gap:0.75rem; color:var(--text-muted);">
        <span style="font-size:1.5rem">📄</span>
        <div class="no-call-msg">Update terbaru akan muncul di sini secara real-time.</div>
    </div>
    <div id="ch-has-call" style="display:none; width:100%">
        <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
            <div>
                <div class="call-badge"><div class="call-dot"></div> UPDATE TERBARU</div>
                <div class="call-num" id="ch-number" style="font-size: 2.5rem;">—</div>
            </div>
            <div class="call-divider"></div>
            <div class="call-meta">
                <div class="call-name" id="ch-name">—</div>
                <div class="call-service" id="ch-service"></div>
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--primary);" id="ch-status"></div>
            </div>
            <div style="margin-left:auto; text-align: right;">
                <div style="font-size:0.7rem; color:var(--text-muted)">Waktu Update</div>
                <div style="font-family:'JetBrains Mono',monospace; font-size:1.1rem; font-weight:700" id="ch-time">—</div>
            </div>
        </div>
    </div>
</div>

<!-- ───── Main layout ───── -->
<div class="monitor-layout">

    <!-- ── Left column ── -->
    <div class="left-col">

        <!-- Service Breakdown -->
        <div class="card">
            <div class="sec-hdr">
                <div class="sec-title"><i class="fas fa-layer-group"></i> Rekap Per Jenis Surat</div>
                <div class="live-pill"><div class="live-pill-dot"></div>Auto-update</div>
            </div>
            <div id="svc-breakdown">
                <div class="empty-state"><div class="empty-icon">⏳</div>Memuat...</div>
            </div>
            <!-- Wait time replacement -->
            <div class="wait-bar" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.1);">
                <div class="wait-bar-icon">✅</div>
                <div>
                    <div class="wait-num" style="color: var(--success);" id="done-today">0</div>
                    <div class="wait-label">Surat diselesaikan hari ini</div>
                </div>
            </div>
        </div>

        <!-- Queue list -->
        <div class="card">
            <div class="sec-hdr">
                <div class="sec-title"><i class="fas fa-list"></i> Daftar Pengajuan</div>
                <span id="q-count-lbl" style="font-size:0.75rem; color:var(--text-muted)"></span>
            </div>
            <div class="tab-strip">
                <button class="t-btn active" onclick="switchTab('pending', this)">📥 Baru</button>
                <button class="t-btn" onclick="switchTab('admin', this)">🔍 Admin</button>
                <button class="t-btn" onclick="switchTab('prodi', this)">🏛️ Prodi</button>
                <button class="t-btn" onclick="switchTab('ready', this)">🎁 Siap</button>
                <button class="t-btn" onclick="switchTab('done', this)">✅ Selesai</button>
                <button class="t-btn" onclick="switchTab('', this)">📋 Semua</button>
            </div>
            <div class="queue-table-wrap">
                <table class="q-table">
                    <thead>
                        <tr>
                            <th>ID Surat</th>
                            <th>Mahasiswa</th>
                            <th>NIM/HP</th>
                            <th>Jenis Surat</th>
                            <th>Status</th>
                            <th>Waktu Daftar</th>
                        </tr>
                    </thead>
                    <tbody id="q-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Right column ── -->
    <div class="right-col">

        <!-- Counter grid -->
        <div class="card">
            <div class="sec-hdr">
                <div class="sec-title"><i class="fas fa-door-open"></i> Status Ruang Dosen</div>
            </div>
            <div class="counter-grid" id="cg-grid"></div>
        </div>

        <!-- Activity Log -->
        <div class="card">
            <div class="sec-hdr">
                <div class="sec-title"><i class="fas fa-history"></i> Log Aktivitas</div>
                <button onclick="clearLog()" class="btn btn-ghost" style="padding:0.3rem 0.6rem; font-size:0.72rem;">
                    <i class="fas fa-trash-alt"></i> Hapus
                </button>
            </div>
            <div class="log-list" id="activity-log">
                <div class="empty-state" id="log-empty">
                    <div class="empty-icon">📭</div>
                    Belum ada aktivitas
                </div>
            </div>
        </div>

        <!-- Connection status card -->
        <div class="card" id="ws-card" style="padding:0.875rem 1.25rem;">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                <div style="display:flex; align-items:center; gap:0.625rem;">
                    <div class="ws-dot" id="ws-dot"></div>
                    <div>
                        <div style="font-size:0.82rem; font-weight:600;" id="ws-text">Menghubungkan WebSocket...</div>
                        <div style="font-size:0.7rem; color:var(--text-muted)">Reverb — ws://localhost:8080</div>
                    </div>
                </div>
                <div style="font-size:0.72rem; color:var(--text-muted); font-family:'JetBrains Mono',monospace" id="last-update">—</div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
// ──────────────────────────────
// State
// ──────────────────────────────
let qTab = 'pending';
const LOG_MAX = 50;
let activityLog = [];
let lastCalledData = null;

// ──────────────────────────────
// Clock
// ──────────────────────────────
function updateClock() {
    document.getElementById('mon-clock').textContent = new Date().toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000); updateClock();

// ──────────────────────────────
// Number counter animation
// ──────────────────────────────
function setStatNum(id, newVal) {
    const el = document.getElementById(id);
    if (!el) return;
    const current = parseInt(el.textContent) || 0;
    if (current === newVal) return;
    el.textContent = newVal;
    el.classList.remove('bump');
    void el.offsetWidth;
    el.classList.add('bump');
    el.addEventListener('animationend', () => el.classList.remove('bump'), { once: true });
}

// ──────────────────────────────
// Update "calling highlight"
// ──────────────────────────────
function showCallingHighlight(data) {
    if (!data) {
        document.getElementById('ch-no-call').style.display = 'flex';
        document.getElementById('ch-has-call').style.display = 'none';
        document.getElementById('calling-highlight').classList.remove('has-call');
        return;
    }
    document.getElementById('ch-no-call').style.display = 'none';
    document.getElementById('ch-has-call').style.display = 'block';
    document.getElementById('calling-highlight').classList.add('has-call');

    const numEl = document.getElementById('ch-number');
    numEl.textContent = data.queue_number;
    numEl.classList.remove('pop');
    void numEl.offsetWidth;
    numEl.classList.add('pop');

    document.getElementById('ch-name').textContent    = data.patient_name || '—';
    document.getElementById('ch-time').textContent    = data.called_at || '—';

    const svcEl = document.getElementById('ch-service');
    const color = data.service_color || '#6366F1';
    svcEl.innerHTML = `<span style="display:inline-flex;align-items:center;gap:0.4rem;background:${color}20;color:${color};padding:0.2rem 0.7rem;border-radius:99px;font-size:0.78rem;font-weight:700">${data.service_name || ''}</span>`;

    const roomEl = document.getElementById('ch-room');
    roomEl.innerHTML = `<i class="fas fa-door-open" style="color:${color}"></i> <span style="color:${color}">${data.counter_name || '—'}</span>`;
}

// ──────────────────────────────
// Service breakdown
// ──────────────────────────────
function renderBreakdown(services) {
    const el = document.getElementById('svc-breakdown');
    if (!services || !Object.keys(services).length) {
        el.innerHTML = '<div class="empty-state"><div class="empty-icon">📭</div>Belum ada data</div>';
        return;
    }

    // Calculate total for avg wait estimation
    let totalDone = 0, totalWait = 0;
    Object.values(services).forEach(s => { totalDone += s.done; totalWait += s.waiting; });

    el.innerHTML = '';
    Object.entries(services).forEach(([code, s]) => {
        const pct = s.total > 0 ? Math.round((s.done / s.total) * 100) : 0;
        const row = document.createElement('div');
        row.className = 'svc-row';
        row.innerHTML = `
            <div class="svc-dot" style="background:${s.color}"></div>
            <div class="svc-info">
                <div class="svc-name-text">${s.service_name}</div>
                <div class="svc-code-text">${code}</div>
            </div>
            <div class="svc-bar-wrap">
                <div class="svc-bar-bg">
                    <div class="svc-bar-fill" style="width:${pct}%;background:${s.color}"></div>
                </div>
                <div style="font-size:0.62rem;color:var(--text-muted);margin-top:0.15rem;text-align:right">${pct}% selesai</div>
            </div>
            <div class="svc-nums">
                <span class="svc-w" title="Pending">📥${s.pending}</span>
                <span class="svc-a" title="Proses">⚙️${s.admin + s.prodi}</span>
                <span class="svc-d" title="Selesai">✅${s.done}</span>
            </div>
        `;
        el.appendChild(row);
    });

    // Done today count
    document.getElementById('done-today').textContent = totalDone;
}

// ──────────────────────────────
// Stats
// ──────────────────────────────
function refreshStats() {
    fetch('/api/stats').then(r => r.json()).then(data => {
        let total = 0, waiting = 0, calling = 0, serving = 0, done = 0;
        if (data.services) {
            Object.values(data.services).forEach(s => {
                total   += s.total;
                waiting += s.waiting;
                serving += s.serving;
                done    += s.done;
            });
        }
        // calling = serving (dipanggil) tapi kita buat terpisah
        calling = serving;
        setStatNum('st-total',   total);
        setStatNum('st-waiting', waiting);
        setStatNum('st-calling', calling);
        setStatNum('st-serving', serving);
        setStatNum('st-done',    done);

        renderBreakdown(data.services);
        showCallingHighlight(data.calling);
        document.getElementById('last-update').textContent = 'Update: ' + new Date().toLocaleTimeString('id-ID');
    }).catch(() => {});
}

// ──────────────────────────────
// Queue list
// ──────────────────────────────
function switchTab(tab, btn) {
    qTab = tab;
    document.querySelectorAll('.t-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    refreshQueues();
}

function refreshQueues() {
    let url;
    if (qTab === 'active')   url = '/api/admin/today?status=calling';
    else if (qTab === 'waiting') url = '/api/admin/today?status=waiting';
    else if (qTab === 'done')    url = '/api/admin/today?status=done';
    else url = '/api/admin/today';

    // For "active" tab, combine waiting+calling+serving
    if (qTab === 'active') {
        Promise.all([
            fetch('/api/admin/today?status=waiting').then(r => r.json()),
            fetch('/api/admin/today?status=calling').then(r => r.json()),
            fetch('/api/admin/today?status=serving').then(r => r.json()),
        ]).then(([w, c, s]) => renderQueues([...c, ...s, ...w]));
        return;
    }

    fetch(url).then(r => r.json()).then(renderQueues).catch(() => {});
}

function renderQueues(list) {
    const tbody = document.getElementById('q-tbody');
    document.getElementById('q-count-lbl').textContent = `${list.length} antrian`;
    tbody.innerHTML = '';
    if (!list.length) {
        tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted)"><div style="font-size:2rem">📭</div>Tidak ada antrian</td></tr>`;
        return;
    }
    list.forEach(q => {
        const color = q.service_color || '#6366F1';
        const tr = document.createElement('tr');
        if (q.status === 'calling') tr.className = 'row-calling';
        tr.innerHTML = `
            <td class="qnum-cell" style="color:${color}">${q.queue_number}</td>
            <td style="font-weight:600">${q.patient_name}</td>
            <td style="color:var(--text-muted);font-size:0.78rem;font-family:'JetBrains Mono',monospace">${q.phone || '—'}</td>
            <td>
                <span style="background:${color}18;color:${color};padding:0.2rem 0.6rem;border-radius:99px;font-size:0.72rem;font-weight:700;white-space:nowrap">
                    ${q.service_name}
                </span>
            </td>
            <td><span class="badge badge-${q.status}">${q.status_label}</span></td>
            <td style="color:var(--text-muted);font-size:0.75rem;font-family:'JetBrains Mono',monospace">${q.created_at}</td>
        `;
        tbody.appendChild(tr);
    });
}

// ──────────────────────────────
// Counters grid
// ──────────────────────────────
function refreshCounters() {
    fetch('/api/counters').then(r => r.json()).then(list => {
        const el = document.getElementById('cg-grid');
        el.innerHTML = '';
        list.forEach(c => {
            const color = c.current_queue?.service_color || '#64748B';
            const div = document.createElement('div');
            div.className = 'cg-card' + (c.current_queue ? ' active' : '');
            div.innerHTML = `
                <div class="cg-label">${c.name}</div>
                ${c.current_queue
                    ? `<div class="cg-num" style="color:${color}">${c.current_queue.queue_number}</div>
                       <div class="cg-name">${c.current_queue.patient_name}</div>
                       <div class="cg-off"><span class="badge badge-${c.current_queue.status}" style="font-size:0.65rem">${c.current_queue.status_label}</span></div>`
                    : `<div class="cg-empty">Kosong</div>`
                }
                ${c.officer_name ? `<div class="cg-off" style="margin-top:0.4rem">${c.officer_name}</div>` : ''}
            `;
            el.appendChild(div);
        });
    });
}

// ──────────────────────────────
// Activity log
// ──────────────────────────────
const LOG_ICONS = {
    pending: { icon: '🟢', bg: 'rgba(16,185,129,0.15)', label: 'Pengajuan Baru' },
    admin:   { icon: '🔍', bg: 'rgba(245,158,11,0.15)', label: 'Verif Admin' },
    prodi:   { icon: '🏛️', bg: 'rgba(99,102,241,0.15)', label: 'Kirim Prodi' },
    ready:   { icon: '🎁', bg: 'rgba(16,185,129,0.2)',  label: 'Siap Ambil' },
    done:    { icon: '✅', bg: 'rgba(16,185,129,0.1)',  label: 'Selesai' },
    rejected:{ icon: '❌', bg: 'rgba(239,68,68,0.1)',   label: 'Ditolak' },
    reset:   { icon: '🔄', bg: 'rgba(99,102,241,0.1)', label: 'Reset' },
};

function addLog(action, queue) {
    const conf = LOG_ICONS[action] || LOG_ICONS.new;
    const time = new Date().toLocaleTimeString('id-ID');
    activityLog.unshift({ action, queue, time, conf });
    if (activityLog.length > LOG_MAX) activityLog.pop();
    renderLog();
}

function renderLog() {
    const el = document.getElementById('activity-log');
    const empty = document.getElementById('log-empty');
    if (!activityLog.length) { if (empty) empty.style.display = 'block'; return; }
    if (empty) empty.style.display = 'none';
    el.innerHTML = '';
    activityLog.forEach(entry => {
        const div = document.createElement('div');
        div.className = 'log-item';
        const qText = entry.queue
            ? `<span class="log-num">${entry.queue.queue_number}</span> — ${entry.queue.patient_name || ''}`
            : '';
        div.innerHTML = `
            <div class="log-icon" style="background:${entry.conf.bg}">${entry.conf.icon}</div>
            <div class="log-text">
                <div><strong>${entry.conf.label}</strong> ${qText}</div>
                ${entry.queue?.counter_name ? `<div style="font-size:0.7rem;color:var(--text-muted)">${entry.queue.counter_name}</div>` : ''}
                <div class="log-time">${entry.time}</div>
            </div>
        `;
        el.appendChild(div);
    });
}

function clearLog() { activityLog = []; renderLog(); }

// ──────────────────────────────
// All refresh
// ──────────────────────────────
function refreshAll() {
    refreshStats();
    refreshQueues();
    refreshCounters();
}

refreshAll();

// ──────────────────────────────
// WebSocket
// ──────────────────────────────
try {
    const echo = new window.Echo({
        broadcaster: 'reverb',
        key: '{{ env("VITE_REVERB_APP_KEY") }}',
        wsHost: '{{ env("VITE_REVERB_HOST", "localhost") }}',
        wsPort: {{ env("VITE_REVERB_PORT", 8080) }},
        forceTLS: false,
        enabledTransports: ['ws'],
    });

    echo.channel('queue-monitor').listen('.queue.updated', (e) => {
        setWsStatus(true);
        refreshAll();
        addLog(e.action, e.queue);

        // Toast
        const msgs = {
            new:     () => `Mahasiswa baru: ${e.queue?.queue_number} — ${e.queue?.patient_name}`,
            called:  () => `Memanggil: ${e.queue?.queue_number} → ${e.queue?.counter_name}`,
            serving: () => `Melayani: ${e.queue?.queue_number}`,
            done:    () => `Selesai: ${e.queue?.queue_number}`,
            skip:    () => `Dilewati: ${e.queue?.queue_number}`,
            reset:   () => `Antrian hari ini direset`,
        };
        const toastType = { new:'info', called:'warning', serving:'info', done:'success', skip:'error', reset:'error' };
        if (msgs[e.action]) showToast(msgs[e.action](), toastType[e.action] || 'info', 4000);
    });

    echo.channel('queue-display').listen('.queue.called', (e) => {
        lastCalledData = e;
        showUpdateHighlight({ ...e, called_at: new Date().toLocaleTimeString('id-ID') });
    });

    echo.connector.socket.on('connect', () => {
        setWsStatus(true);
        addLog('new', null); // just to update renderer (we use a special case)
    });
    echo.connector.socket.on('disconnect', () => setWsStatus(false));

} catch(err) { console.warn('WS:', err); }

// ──────────────────────────────
// Auto-refresh fallback (every 20s)
// ──────────────────────────────
setInterval(refreshAll, 20000);
</script>
@endpush
