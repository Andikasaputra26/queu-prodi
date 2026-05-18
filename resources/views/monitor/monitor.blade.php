@extends('layouts.app')

@section('title', 'Monitoring Real-time — SI-PINTER')

@push('styles')
    <style>
        .stat-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 900px) {
            .stat-bar {
                grid-template-columns: repeat(3, 1fr);
            }
        }

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

        .stat-box:hover {
            transform: translateY(-2px);
        }

        .stat-box::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
        }

        .s-total::after {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .s-pending::after {
            background: #94A3B8;
        }

        .s-admin::after {
            background: var(--warning);
        }

        .s-done::after {
            background: var(--success);
        }

        .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.4rem;
        }

        .stat-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.75rem;
            font-weight: 900;
            line-height: 1;
            transition: all 0.4s;
        }

        .s-total .stat-num {
            color: var(--primary);
        }

        .s-pending .stat-num {
            color: #94A3B8;
        }

        .s-admin .stat-num {
            color: var(--warning);
        }

        .s-done .stat-num {
            color: var(--success);
        }

        .stat-lbl {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-top: 0.3rem;
        }

        .stat-num.bump {
            animation: statBump 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes statBump {
            0% {
                transform: scale(1.3);
            }

            100% {
                transform: scale(1);
            }
        }

        /* ── Layout ── */
        .monitor-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 1.5rem;
        }

        @media (max-width: 1100px) {
            .monitor-layout {
                grid-template-columns: 1fr;
            }
        }

        .left-col {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .right-col {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        /* ── Currently called highlight ── */
        .calling-highlight {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.05));
            border: 1px solid rgba(245, 158, 11, 0.3);
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.4s;
        }

        .calling-highlight.has-call {
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.2);
        }

        .call-badge {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--warning);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-bottom: 0.4rem;
        }

        .call-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--warning);
            box-shadow: 0 0 8px var(--warning);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1)
            }

            50% {
                opacity: 0.5;
                transform: scale(0.8)
            }
        }

        .call-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 3.5rem;
            font-weight: 900;
            line-height: 1;
            color: var(--warning);
            text-shadow: 0 0 30px rgba(245, 158, 11, 0.4);
            transition: all 0.3s;
        }

        .call-num.pop {
            animation: statBump 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .call-divider {
            width: 1px;
            height: 60px;
            background: var(--border);
            flex-shrink: 0;
        }

        .call-meta {
            flex: 1;
            min-width: 0;
        }

        .call-name {
            font-size: 1.1rem;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .call-service {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .call-room {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: 0.4rem;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .no-call-msg {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .svc-row {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.7rem 0;
            border-bottom: 1px solid var(--border);
        }

        .svc-row:last-child {
            border-bottom: none;
        }

        .svc-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .svc-info {
            flex: 1;
            min-width: 0;
        }

        .svc-name-text {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .svc-code-text {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .svc-bar-wrap {
            width: 120px;
        }

        .svc-bar-bg {
            height: 5px;
            background: var(--surface-3);
            border-radius: 99px;
            overflow: hidden;
        }

        .svc-bar-fill {
            height: 100%;
            border-radius: 99px;
            transition: width 0.6s ease;
        }

        .svc-nums {
            display: flex;
            gap: 0.625rem;
            font-size: 0.75rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .svc-w {
            color: #94A3B8;
        }

        .svc-a {
            color: var(--warning);
        }

        .svc-d {
            color: var(--success);
        }

        /* ── Queue table ── */
        .queue-table-wrap {
            overflow-x: auto;
            max-height: 380px;
            overflow-y: auto;
        }

        .queue-table-wrap::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .queue-table-wrap::-webkit-scrollbar-thumb {
            background: var(--surface-3);
            border-radius: 99px;
        }

        .q-table {
            width: 100%;
            border-collapse: collapse;
        }

        .q-table th {
            padding: 0.6rem 0.875rem;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            text-align: left;
            white-space: nowrap;
            position: sticky;
            top: 0;
            background: var(--surface);
            z-index: 1;
        }

        .q-table td {
            padding: 0.6rem 0.875rem;
            font-size: 0.82rem;
            border-bottom: 1px solid var(--border);
            transition: background 0.3s;
        }

        .q-table tr:last-child td {
            border-bottom: none;
        }

        .q-table tr:hover td {
            background: rgba(37, 99, 235, 0.04);
        }

        .q-table tr.row-new td {
            animation: rowHighlight 2.5s ease forwards;
        }

        @keyframes rowHighlight {
            0% {
                background: rgba(37, 99, 235, 0.25);
            }

            100% {
                background: transparent;
            }
        }

        .q-table tr.row-calling td {
            background: rgba(245, 158, 11, 0.08);
        }

        .qnum-cell {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .log-list {
            max-height: 340px;
            overflow-y: auto;
        }

        .log-list::-webkit-scrollbar {
            width: 4px;
        }

        .log-list::-webkit-scrollbar-thumb {
            background: var(--surface-3);
            border-radius: 99px;
        }

        .log-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.65rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.8rem;
        }

        .log-item:last-child {
            border-bottom: none;
        }

        .log-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .log-text {
            flex: 1;
            line-height: 1.4;
        }

        .log-num {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
        }

        .log-time {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
            margin-top: 0.1rem;
        }

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
            border-color: rgba(245, 158, 11, 0.4);
            background: rgba(245, 158, 11, 0.05);
            box-shadow: 0 0 15px rgba(245, 158, 11, 0.1);
        }

        .cg-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 0.4rem;
        }

        .cg-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.6rem;
            font-weight: 900;
            line-height: 1;
        }

        .cg-name {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cg-off {
            font-size: 0.65rem;
            color: var(--text-muted);
            opacity: 0.6;
            margin-top: 0.2rem;
        }

        .cg-empty {
            font-size: 0.78rem;
            color: var(--text-muted);
            font-style: italic;
        }

        /* ── Section header ── */
        .sec-hdr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .sec-title {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .live-pill {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--success);
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.25);
            padding: 0.2rem 0.6rem;
            border-radius: 99px;
        }

        .live-pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--success);
            animation: pulse 2s infinite;
        }

        .tab-strip {
            display: flex;
            gap: 0.35rem;
            margin-bottom: 1rem;
        }

        .t-btn {
            padding: 0.35rem 0.75rem;
            border-radius: 7px;
            border: none;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            color: var(--text-muted);
            background: transparent;
            transition: all 0.2s;
        }

        .t-btn:hover {
            color: var(--text);
            background: var(--surface-3);
        }

        .t-btn.active {
            color: var(--text);
            background: rgba(37, 99, 235, 0.15);
        }

        .wait-bar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--surface-3);
            border-radius: 10px;
            margin-top: 0.75rem;
        }

        .wait-bar-icon {
            font-size: 1.25rem;
        }

        .wait-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--accent);
        }

        .wait-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 0.1rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
        }

        .empty-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        /* DataTable Overrides */
        .dataTables_wrapper {
            padding: 0.5rem;
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

    <div
        style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
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
            <div class="stat-lbl">Diproses</div>
        </div>
        <div class="stat-box s-done">
            <div class="stat-icon">✅</div>
            <div class="stat-num" id="st-done">0</div>
            <div class="stat-lbl">Selesai</div>
        </div>
    </div>

    <div class="calling-highlight card" id="calling-highlight" style="margin-bottom:1.5rem;">
        <div id="ch-no-call" style="display:flex; align-items:center; gap:0.75rem; color:var(--text-muted);">
            <span style="font-size:1.5rem">📄</span>
            <div class="no-call-msg">Update terbaru akan muncul di sini secara real-time.</div>
        </div>
        <div id="ch-has-call" style="display:none; width:100%">
            <div style="display:flex; align-items:center; gap:1.5rem; flex-wrap:wrap;">
                <div>
                    <div class="call-badge">
                        <div class="call-dot"></div> UPDATE TERBARU
                    </div>
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
                    <div style="font-family:'JetBrains Mono',monospace; font-size:1.1rem; font-weight:700" id="ch-time">—
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="monitor-layout">

        <div class="left-col">

            <div class="card">
                <div class="sec-hdr">
                    <div class="sec-title"><i class="fas fa-layer-group"></i> Rekap Per Jenis Surat</div>
                    <div style="display:flex; gap:0.5rem; align-items:center;">
                        <input type="date" id="chart-date" class="form-control" style="font-size:0.75rem; padding:0.2rem 0.5rem; border:1px solid var(--border); border-radius:4px;" onchange="refreshStats()">
                        <select id="chart-category" class="form-control" style="font-size:0.75rem; padding:0.2rem 0.5rem; border:1px solid var(--border); border-radius:4px; max-width: 150px;" onchange="refreshStats()">
                            <option value="">Semua Kategori</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                            @endforeach
                        </select>
                        <div class="live-pill">
                            <div class="live-pill-dot"></div>Auto-update
                        </div>
                    </div>
                </div>
                <div style="min-height: 250px; margin-top: 1rem; position: relative;">
                    <canvas id="svc-chart"></canvas>
                    <div class="empty-state" id="svc-chart-empty"
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                        <div class="empty-icon">⏳</div>Memuat Grafik...
                    </div>
                </div>
                <div class="wait-bar" style="background: rgba(16,185,129,0.05); border: 1px solid rgba(16,185,129,0.1);">
                    <div class="wait-bar-icon">✅</div>
                    <div>
                        <div class="wait-num" style="color: var(--success);" id="done-today">0</div>
                        <div class="wait-label">Surat diselesaikan (sesuai filter)</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="sec-hdr">
                    <div class="sec-title"><i class="fas fa-list"></i> Daftar Pengajuan</div>
                    <span id="q-count-lbl" style="font-size:0.75rem; color:var(--text-muted)"></span>
                </div>
                <div class="table-responsive">
                    <table class="q-table display" id="monitor-q-table" style="width:100%">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>ID Surat</th>
                                <th>Mahasiswa</th>
                                <th>NIM/HP</th>
                                <th>Jenis Surat</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="q-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="right-col">

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

            <div class="card" id="ws-card" style="padding:0.875rem 1.25rem;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.5rem;">
                    <div style="display:flex; align-items:center; gap:0.625rem;">
                        <div class="ws-dot" id="ws-dot"></div>
                        <div>
                            <div style="font-size:0.82rem; font-weight:600;" id="ws-text">Menghubungkan WebSocket...</div>
                            <div style="font-size:0.7rem; color:var(--text-muted)">Reverb — ws://localhost:8080</div>
                        </div>
                    </div>
                    <div style="font-size:0.72rem; color:var(--text-muted); font-family:'JetBrains Mono',monospace"
                        id="last-update">—</div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        window.QueueConfig = {
            reverbKey: '{{ env("VITE_REVERB_APP_KEY") }}',
            reverbHost: '{{ env("VITE_REVERB_HOST", "localhost") }}',
            reverbPort: {{ env("VITE_REVERB_PORT", 8080) }},
        };
    </script>
    <script src="{{ asset('js/queue-monitor.js') }}"></script>
@endpush