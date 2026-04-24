<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Queu') — Antrian Konsultasi Prodi</title>
    <meta name="description" content="Sistem antrian konsultasi digital Program Studi berbasis WebSocket real-time">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('styles')
    <style>
        :root {
            --primary: #6366F1;
            --primary-dark: #4F46E5;
            --secondary: #8B5CF6;
            --accent: #06B6D4;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --surface: #0F172A;
            --surface-2: #1E293B;
            --surface-3: #334155;
            --border: rgba(255,255,255,0.08);
            --text: #F1F5F9;
            --text-muted: #94A3B8;
            --glass: rgba(30, 41, 59, 0.8);
            --glow: 0 0 30px rgba(99, 102, 241, 0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(99,102,241,0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(139,92,246,0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .navbar {
            position: sticky; top: 0; z-index: 100;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: flex; align-items: center; gap: 0.75rem;
            text-decoration: none;
        }

        .brand-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            box-shadow: var(--glow);
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .brand-sub {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 400;
            -webkit-text-fill-color: initial;
            display: block;
            margin-top: -2px;
        }

        .nav-links {
            display: flex; gap: 0.25rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.5rem 0.875rem;
            border-radius: 8px;
            font-size: 0.825rem;
            font-weight: 500;
            transition: all 0.2s;
            display: flex; align-items: center; gap: 0.4rem;
        }

        .nav-links a:hover, .nav-links a.active {
            color: var(--text);
            background: rgba(99, 102, 241, 0.15);
        }

        .main-content {
            position: relative; z-index: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Cards */
        .card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .card:hover { border-color: rgba(99,102,241,0.25); }

        .card-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            display: flex; align-items: center; gap: 0.5rem;
        }

        /* Buttons */
        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            border: none;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex; align-items: center; gap: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(99,102,241,0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99,102,241,0.4);
        }

        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: white; }
        .btn-danger  { background: var(--danger);  color: white; }
        .btn-ghost   {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover { color: var(--text); border-color: var(--primary); }

        .btn:active { transform: scale(0.97); }

        /* Status badges */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge-waiting  { background: rgba(100,116,139,0.3); color: #94A3B8; }
        .badge-calling  { background: rgba(245,158,11,0.2);  color: #FCD34D; animation: pulse-badge 1.5s infinite; }
        .badge-serving  { background: rgba(59,130,246,0.2);  color: #60A5FA; }
        .badge-done     { background: rgba(16,185,129,0.2);  color: #34D399; }
        .badge-skip     { background: rgba(239,68,68,0.2);   color: #F87171; }

        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Form elements */
        .form-group { margin-bottom: 1.25rem; }
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--surface-3);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: inherit;
            font-size: 0.925rem;
            transition: all 0.2s;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.15);
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 1.5rem; right: 1.5rem;
            z-index: 9999;
            display: flex; flex-direction: column; gap: 0.75rem;
        }

        .toast {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            display: flex; align-items: center; gap: 0.75rem;
            min-width: 280px; max-width: 360px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }

        .toast-success { border-left: 3px solid var(--success); }
        .toast-warning { border-left: 3px solid var(--warning); }
        .toast-error   { border-left: 3px solid var(--danger);  }
        .toast-info    { border-left: 3px solid var(--primary); }

        /* Pulse */
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .pulse { animation: pulse 2s infinite; }

        /* WS indicator */
        .ws-status {
            display: flex; align-items: center; gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .ws-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--text-muted);
            transition: background 0.3s;
        }

        .ws-dot.connected { background: var(--success); box-shadow: 0 0 8px var(--success); animation: pulse 2s infinite; }
        .ws-dot.error     { background: var(--danger);  box-shadow: 0 0 8px var(--danger);  }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 99px; }

        @media (max-width: 768px) {
            .navbar { padding: 0.875rem 1rem; }
            .main-content { padding: 1rem; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="{{ route('queue.register') }}" class="navbar-brand">
            <div class="brand-icon">🎓</div>
            <div>
                <div class="brand-text">Queu</div>
                <span class="brand-sub">Antrian Konsultasi Program Studi</span>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="{{ route('queue.register') }}" class="{{ request()->routeIs('queue.register') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt"></i> Ambil Nomor
            </a></li>
            <li><a href="{{ route('monitor.display') }}" class="{{ request()->routeIs('monitor.display') ? 'active' : '' }}" target="_blank">
                <i class="fas fa-tv"></i> Display Antrian
            </a></li>
            <li><a href="{{ route('monitor.monitor') }}" class="{{ request()->routeIs('monitor.monitor') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> Monitor
            </a></li>
            <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="fas fa-user-cog"></i> Panel Dosen / Staf
            </a></li>
        </ul>
        <div class="ws-status">
            <div class="ws-dot" id="ws-dot"></div>
            <span id="ws-text">Live</span>
        </div>
    </nav>

    <div class="main-content">
        @yield('content')
    </div>

    <div class="toast-container" id="toast-container"></div>

    @stack('scripts')
    <script>
        function showToast(message, type = 'info', duration = 4000) {
            const icons = { success: '✅', warning: '⚠️', error: '❌', info: 'ℹ️' };
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<span style="font-size:1.1rem">${icons[type]}</span><span style="font-size:0.875rem">${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        function setWsStatus(connected) {
            const dot  = document.getElementById('ws-dot');
            const text = document.getElementById('ws-text');
            if (!dot) return;
            if (connected) {
                dot.className = 'ws-dot connected';
                text.textContent = 'Live';
            } else {
                dot.className = 'ws-dot error';
                text.textContent = 'Offline';
            }
        }
    </script>
</body>
</html>
