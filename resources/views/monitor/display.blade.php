<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Pelacakan Surat Mahasiswa — SI-PINTER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&family=JetBrains+Mono:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --surface: #F8FAFC;
            --surface-2: #FFFFFF;
            --surface-3: #F1F5F9;
            --border: #E2E8F0;
            --text: #0F172A;
            --text-muted: #64748B;
            --primary: #2563EB;
            --success: #10B981;
            --warning: #F59E0B;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text);
            height: 100vh;
            overflow: hidden;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        /* Header */
        .header {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2.5rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        .header-left { display: flex; align-items: center; gap: 1rem; }

        .brand-logo {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, var(--primary), #3B82F6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #FFFFFF;
        }

        .header-title { font-size: 1.15rem; font-weight: 800; }
        .header-sub   { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.1rem; }

        .clock-time {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.75rem; font-weight: 900; line-height: 1;
        }

        .clock-date { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; text-align: right; }

        /* Main */
        .display-main {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            overflow: hidden;
        }

        /* Calling panel */
        .calling-panel {
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            padding: 2rem 3rem;
            position: relative; overflow: hidden;
            background: radial-gradient(ellipse at center, rgba(37,99,235,0.06) 0%, transparent 70%);
        }

        .calling-label {
            font-size: 0.72rem; font-weight: 700;
            letter-spacing: 0.25em; text-transform: uppercase;
            color: var(--text-muted);
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .live-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--warning);
            box-shadow: 0 0 10px var(--warning);
        }

        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.8); } }
        .live-dot { animation: pulse 1s infinite; }

        .calling-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(7rem, 14vw, 13rem);
            font-weight: 900; line-height: 0.9;
            background: linear-gradient(135deg, #FCD34D, #F59E0B);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .calling-number.animate {
            animation: callIn 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes callIn {
            0%   { transform: scale(0.4) rotate(-5deg); opacity: 0; }
            100% { transform: scale(1)   rotate(0deg);  opacity: 1; }
        }

        .calling-service {
            display: inline-flex; align-items: center; gap: 0.5rem;
            border-radius: 99px; padding: 0.5rem 1.25rem;
            font-size: 0.95rem; font-weight: 700;
            margin-top: 1.25rem;
            transition: all 0.3s;
        }

        .calling-room {
            font-size: 1.5rem; font-weight: 700;
            color: var(--text-muted);
            margin-top: 0.75rem;
            display: flex; align-items: center; gap: 0.5rem;
        }

        .calling-name {
            font-size: 1.1rem; color: var(--text-muted);
            margin-top: 0.5rem; text-align: center;
        }

        /* No call / Welcome */
        .welcome-panel {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; color: var(--text-muted);
        }
        .welcome-icon { font-size: 5rem; margin-bottom: 1rem; }
        .welcome-text { font-size: 1.5rem; font-weight: 800; color: var(--text); }
        .welcome-sub { font-size: 1rem; margin-top: 0.5rem; }

        /* Tracking Table */
        .tracking-wrap {
            padding: 1.5rem 2.5rem; flex: 1; overflow: hidden;
            display: flex; flex-direction: column;
        }
        .tracking-table {
            width: 100%; border-collapse: separate; border-spacing: 0 0.75rem;
        }
        .tracking-table th {
            text-align: left; padding: 0.5rem 1rem; font-size: 0.75rem;
            color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em;
        }
        .tracking-table td {
            background: var(--surface-2); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);
            padding: 1.25rem 1rem; font-size: 1.1rem;
        }
        .tracking-table td:first-child { border-left: 1px solid var(--border); border-radius: 12px 0 0 12px; font-family: 'JetBrains Mono', monospace; font-weight: 900; color: var(--warning); width: 120px; }
        .tracking-table td:last-child { border-right: 1px solid var(--border); border-radius: 0 12px 12px 0; }
        
        .badge {
            display: inline-block; padding: 0.35rem 0.875rem; border-radius: 99px;
            font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
        }
        .badge-pending { background: rgba(148,163,184,0.15); color: #64748B; }
        .badge-admin   { background: rgba(245,158,11,0.15); color: var(--warning); }
        .badge-prodi   { background: rgba(37,99,235,0.15); color: var(--primary); }
        .badge-ready   { background: rgba(16,185,129,0.15); color: var(--success); }
        .badge-done    { background: rgba(16,185,129,0.25); color: var(--success); opacity: 0.7; }

        /* Right panel: Counters */
        .right-panel {
            display: flex; flex-direction: column;
            overflow: hidden;
        }

        .counters-wrap {
            padding: 1.5rem;
            overflow-y: auto; flex: 1;
        }

        .panel-label {
            font-size: 0.68rem; font-weight: 700;
            letter-spacing: 0.15em; text-transform: uppercase;
            color: var(--text-muted); margin-bottom: 1rem;
        }

        .counter-row {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0.875rem 1rem;
            margin-bottom: 0.6rem;
            display: flex; align-items: center; gap: 0.875rem;
            transition: all 0.3s;
        }

        .counter-row.active {
            border-color: rgba(245,158,11,0.35);
            background: rgba(245,158,11,0.04);
        }

        .counter-dot {
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--text-muted); flex-shrink: 0;
        }

        .counter-row.active .counter-dot {
            background: var(--warning);
            box-shadow: 0 0 8px var(--warning);
            animation: pulse 1.5s infinite;
        }

        .counter-title { font-size: 0.72rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
        .counter-queue { font-family: 'JetBrains Mono', monospace; font-size: 1.6rem; font-weight: 900; line-height: 1; }
        .counter-patient { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; }
        .counter-empty { font-size: 0.82rem; color: var(--text-muted); font-style: italic; }

        /* Footer */
        .footer {
            background: var(--surface-2);
            border-top: 1px solid var(--border);
            padding: 0.7rem 2.5rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        .foot-stats { display: flex; gap: 2.5rem; }

        .foot-stat { display: flex; align-items: center; gap: 0.5rem; }

        .foot-num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.35rem; font-weight: 900;
        }

        .foot-label { font-size: 0.78rem; color: var(--text-muted); }

        .ws-ind { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; color: var(--text-muted); }
        .ws-d { width: 8px; height: 8px; border-radius: 50%; background: var(--text-muted); }
        .ws-d.on { background: var(--success); box-shadow: 0 0 6px var(--success); animation: pulse 2s infinite; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="brand-logo">🎓</div>
            <div>
                <div class="header-title">SI-PINTER</div>
                <div class="header-sub">Sistem Informasi Progres Administrasi Program Studi</div>
            </div>
        </div>
        <div style="text-align: right">
            <div class="clock-time" id="clock"></div>
            <div class="clock-date"  id="date"></div>
        </div>
    </div>

    <div class="display-main" style="grid-template-columns: 1fr;">
        <!-- Tracking List -->
        <div class="tracking-wrap">
            <div class="panel-label" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <div><i class="fas fa-sync-alt fa-spin"></i> &nbsp;Live Update Progres Surat</div>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="date" id="filter-date" onchange="refreshTracking()" style="background: var(--surface-3); border: 1px solid var(--border); color: var(--text); font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 6px; outline: none; cursor: pointer;">
                    <select id="filter-service" onchange="refreshTracking()" style="background: var(--surface-3); border: 1px solid var(--border); color: var(--text); font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 6px; outline: none; cursor: pointer;">
                        <option value="">— Semua Kategori —</option>
                        @foreach($services as $svc)
                        <option value="{{ $svc->id }}">{{ $svc->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="overflow-y: auto; flex: 1;">
                <table class="tracking-table">
                    <thead>
                        <tr>
                            <th>ID Surat</th>
                            <th>Tanggal</th>
                            <th>Nama Mahasiswa</th>
                            <th>Jenis Surat</th>
                            <th>Status Terkini</th>
                        </tr>
                    </thead>
                    <tbody id="tracking-list">
                        <!-- Data loaded via JS -->
                    </tbody>
                </table>
                <div id="empty-msg" class="welcome-panel" style="display: none;">
                    <div class="welcome-icon">📄</div>
                    <div class="welcome-text">Belum ada pengajuan surat</div>
                    <div class="welcome-sub">Data akan muncul secara otomatis saat mahasiswa mendaftar</div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="foot-stats">
            <div class="foot-stat">
                <div class="foot-num" id="f-prodi">0</div>
                <div class="foot-label">Di Prodi</div>
            </div>
            <div class="foot-stat">
                <div class="foot-num" id="f-ready">0</div>
                <div class="foot-label">Siap Ambil</div>
            </div>
            <div class="foot-stat">
                <div class="foot-num" id="f-done">0</div>
                <div class="foot-label">Selesai</div>
            </div>
        </div>
        <div class="ws-ind">
            <div class="ws-d" id="ws-d"></div>
            <span id="ws-t">Menghubungkan...</span>
        </div>
    </div>

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
</body>
</html>
