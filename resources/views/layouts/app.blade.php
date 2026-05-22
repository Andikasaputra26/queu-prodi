<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SI-PINTER') — Antrian Konsultasi Prodi</title>
    <meta name="description" content="Sistem antrian konsultasi digital Program Studi berbasis WebSocket real-time">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --primary: #1D4ED8;
            --primary-dark: #1E3A8A;
            --secondary: #2563EB;
            --accent: #0369A1;
            --success: #047857;
            --warning: #B45309;
            --danger: #DC2626;
            --surface: #F8FAFC;
            --surface-2: #FFFFFF;
            --surface-3: #E2E8F0;
            --border: #CBD5E1;
            --text: #020617;
            --text-muted: #334155;
            --glass: rgba(255, 255, 255, 0.85);
            --glow: 0 4px 20px rgba(37, 99, 235, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(ellipse at 20% 20%, rgba(37, 99, 235, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(56, 189, 248, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0.875rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
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
            display: flex;
            gap: 0.25rem;
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
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--text);
            background: rgba(99, 102, 241, 0.15);
        }

        .main-content {
            position: relative;
            z-index: 1;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .card:hover {
            border-color: rgba(99, 102, 241, 0.25);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.625rem 1.25rem;
            border-radius: 10px;
            border: none;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            color: var(--text);
            border-color: var(--primary);
        }

        .btn:active {
            transform: scale(0.97);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 99px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge-waiting {
            background: rgba(100, 116, 139, 0.3);
            color: #94A3B8;
        }

        .badge-calling {
            background: rgba(245, 158, 11, 0.2);
            color: #FCD34D;
            animation: pulse-badge 1.5s infinite;
        }

        .badge-serving {
            background: rgba(59, 130, 246, 0.2);
            color: #60A5FA;
        }

        .badge-done {
            background: rgba(16, 185, 129, 0.2);
            color: #34D399;
        }

        .badge-skip {
            background: rgba(239, 68, 68, 0.2);
            color: #F87171;
        }

        @keyframes pulse-badge {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

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
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 280px;
            max-width: 360px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            animation: slideIn 0.3s ease;
        }

        .footer {
            margin-top: auto;
            text-align: center;
            padding: 2rem 1.5rem;
            background: var(--surface-2);
            border-top: 1px solid var(--border);
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.6;
            width: 100%;
        }

        .footer p {
            margin: 0;
            color: var(--text-muted);
        }

        .footer .footer-brand {
            font-weight: 700;
            color: var(--primary);
        }


        @keyframes slideIn {
            from {
                transform: translateX(100px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-success {
            border-left: 3px solid var(--success);
        }

        .toast-warning {
            border-left: 3px solid var(--warning);
        }

        .toast-error {
            border-left: 3px solid var(--danger);
        }

        .toast-info {
            border-left: 3px solid var(--primary);
        }

        /* Pulse */
        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        /* WS indicator */
        .ws-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .ws-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--text-muted);
            transition: background 0.3s;
        }

        .ws-dot.connected {
            background: var(--success);
            box-shadow: 0 0 8px var(--success);
            animation: pulse 2s infinite;
        }

        .ws-dot.error {
            background: var(--danger);
            box-shadow: 0 0 8px var(--danger);
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--surface-3);
            border-radius: 99px;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--text);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.2s;
            align-items: center;
            justify-content: center;
        }

        .nav-toggle:hover {
            background: rgba(99, 102, 241, 0.1);
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 0.875rem 1rem;
                position: relative;
            }

            .main-content {
                padding: 1rem;
            }

            .nav-toggle {
                display: flex;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background: var(--surface-2);
                border-bottom: 1px solid var(--border);
                flex-direction: column;
                padding: 1rem;
                gap: 0.5rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                z-index: 99;
            }

            .nav-links.active {
                display: flex;
                animation: slideDown 0.2s ease-out;
            }

            .nav-links li {
                width: 100%;
            }

            .nav-links a {
                width: 100%;
                padding: 0.75rem 1rem;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="{{ route('queue.register') }}" class="navbar-brand">
            <div class="brand-icon">🎓</div>
            <div>
                <div class="brand-text">SI-PINTER</div>
                <span class="brand-sub">Sistem pelayanan persuratan terintegrasi</span>
            </div>
        </a>
        <ul class="nav-links">
            <li><a href="{{ route('queue.register') }}"
                    class="{{ request()->routeIs('queue.register') ? 'active' : '' }}">
                    <i class="fas fa-ticket-alt"></i> Ambil Nomor
                </a></li>
            <li><a href="{{ route('monitor.display') }}"
                    class="{{ request()->routeIs('monitor.display') ? 'active' : '' }}" target="_blank">
                    <i class="fas fa-tv"></i> Display Antrian
                </a></li>

            @auth
                <li><a href="{{ route('monitor.monitor') }}"
                        class="{{ request()->routeIs('monitor.monitor') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Monitor
                    </a></li>

                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-user-cog"></i> Panel Dosen / Staf
                    </a></li>

                <li><a href="{{ route('admin.chat') }}" class="{{ request()->routeIs('admin.chat') ? 'active' : '' }}">
                        <i class="fas fa-comments"></i> Pusat Bantuan
                    </a></li>

                <li>
                    <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </a>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a></li>
            @endauth
        </ul>
        <div class="navbar-right">
            <div class="ws-status">
                <div class="ws-dot" id="ws-dot"></div>
                <span id="ws-text">Live</span>
            </div>
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="main-content">
        @yield('content')
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 <span class="footer-brand">SI-PINTER</span> | Sistem Pelayanan Persuratan Terintegrasi</p>
            <p style="margin-top: 0.25rem; font-size: 0.75rem;">Fakultas Ilmu Sosial dan Hukum, Universitas Negeri Makassar</p>
        </div>
    </footer>   
    
    <div class="toast-container" id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    @stack('scripts')
    <script>
        function showToast(message, type = 'info', duration = 4000) {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: duration,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
            Toast.fire({
                icon: type,
                title: message
            });
        }

        function setWsStatus(connected) {
            const dot = document.getElementById('ws-dot');
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

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif

            const toggleBtn = document.getElementById('nav-toggle');
            const navLinks = document.querySelector('.nav-links');
            if (toggleBtn && navLinks) {
                toggleBtn.addEventListener('click', () => {
                    navLinks.classList.toggle('active');
                    const icon = toggleBtn.querySelector('i');
                    if (icon) {
                        if (navLinks.classList.contains('active')) {
                            icon.className = 'fas fa-times';
                        } else {
                            icon.className = 'fas fa-bars';
                        }
                    }
                });
            }
        });
    </script>
    
    @if(request()->routeIs('admin.*'))
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isPusher = '{{ env("BROADCAST_CONNECTION") }}' === 'pusher';
            if (isPusher && typeof Pusher !== 'undefined') {
                window.Pusher = Pusher;
            }
            
            let echoConfig = {
                broadcaster: isPusher ? 'pusher' : 'reverb',
                key: isPusher ? '{{ env("PUSHER_APP_KEY") }}' : '{{ env("VITE_REVERB_APP_KEY") }}',
                forceTLS: isPusher ? true : false,
                enabledTransports: ['ws', 'wss'],
            };

            if (isPusher) {
                echoConfig.cluster = '{{ env("PUSHER_APP_CLUSTER") }}';
            } else {
                echoConfig.wsHost = '{{ env("VITE_REVERB_HOST", "localhost") }}';
                echoConfig.wsPort = {{ env("VITE_REVERB_PORT", 8080) }};
            }

            const adminEcho = new window.Echo(echoConfig);

            adminEcho.channel('admin-chat').listen('.chat.message', (e) => {
                if (e.message.sender === 'student' && !window.location.pathname.includes('/admin/chat')) {
                    try {
                        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        audio.volume = 0.5;
                        audio.play();
                    } catch(err) {}

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: `Pesan Baru: ${e.session.student_name}`,
                        text: e.message.message.length > 50 ? e.message.message.substring(0, 50) + '...' : e.message.message,
                        showConfirmButton: true,
                        confirmButtonText: 'Balas',
                        showCancelButton: true,
                        cancelButtonText: 'Tutup',
                        timer: 10000,
                        timerProgressBar: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/admin/chat';
                        }
                    });
                }
            });
        });
    </script>
    @endif
    @if(!request()->routeIs('admin.*') && !request()->routeIs('login') && !request()->routeIs('monitor.display'))
    <div id="chat-widget-container">
        <!-- Floating chat button with notification badge if unread -->
        <button id="chat-widget-btn" onclick="toggleChat()" aria-label="Buka Chat">
            <i class="fas fa-comments chat-icon"></i>
            <i class="fas fa-times close-icon" style="display: none;"></i>
        </button>

        <div id="chat-widget-box" class="chat-closed">
            <!-- Header -->
            <div id="chat-widget-header">
                <div class="chat-header-profile">
                    <div class="chat-avatar-wrapper">
                        <i class="fas fa-robot"></i>
                        <span class="online-indicator"></span>
                    </div>
                    <div class="chat-header-text">
                        <strong>SI-PINTER</strong>
                        <span>Asisten AI</span>
                    </div>
                </div>
                <div class="chat-header-actions" style="display: flex; align-items: center; gap: 8px;">
                    <button id="chat-header-reset-btn" class="chat-header-action-btn" onclick="confirmResetChat()" title="Mulai Ulang Obrolan" style="display: none;">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button class="chat-header-close" onclick="toggleChat()" aria-label="Tutup Chat">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <!-- Setup Screen -->
            <div id="chat-setup-screen">
                <div class="setup-welcome">
                    <div class="setup-icon-box animate__animated animate__bounceIn">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3>Halo, Mahasiswa! 👋</h3>
                    <p>Ada yang bisa kami bantu? Silakan masukkan nama Anda untuk memulai konsultasi real-time terkait persuratan akademik.</p>
                </div>

                <form onsubmit="startStudentChat(event)">
                    <div class="chat-input-wrapper">
                        <i class="fas fa-user-graduate input-icon"></i>
                        <input type="text" id="chat-student-name" placeholder="Nama Lengkap / NIM" required class="form-control" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary chat-submit-btn">
                        Mulai Obrolan <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                    </button>
                </form>

                <div class="chat-quick-actions">
                    <span class="quick-title">Bantuan Cepat:</span>
                    <div class="quick-btn-list">
                        <button type="button" class="quick-btn" onclick="startWithQuery('Saya ingin cek status pengajuan saya')">
                            <i class="fas fa-search"></i> Cek Status Pengajuan
                        </button>
                        <button type="button" class="quick-btn" onclick="startWithQuery('Apa saja syarat pengajuan surat?')">
                            <i class="fas fa-file-alt"></i> Syarat Berkas Seminar
                        </button>
                        <button type="button" class="quick-btn" onclick="startWithQuery('Jam berapa pelayanan akademik buka?')">
                            <i class="fas fa-clock"></i> Jam Operasional
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Chat Screen -->
            <div id="chat-active-screen" style="display: none; flex-direction: column; flex: 1; overflow: hidden;">
                <div id="chat-widget-messages">
                    <!-- Messages will be injected here -->
                </div>
                
                <!-- Typing Indicator (hidden by default) -->
                <div id="chat-typing-indicator" style="display: none;">
                    <div class="typing-bubble">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                    </div>
                </div>

                <form id="chat-widget-form" onsubmit="sendStudentMessage(event)">
                    <input type="text" id="chat-widget-input" placeholder="Tulis pesan Anda di sini..." required autocomplete="off">
                    <button type="submit" class="chat-send-btn" aria-label="Kirim Pesan">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        #chat-widget-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        /* Floating Chat Button */
        #chat-widget-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            box-shadow: 0 10px 25px rgba(29, 78, 216, 0.35);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            outline: none;
        }
        #chat-widget-btn:hover {
            transform: scale(1.08) translateY(-3px);
            box-shadow: 0 14px 30px rgba(29, 78, 216, 0.45);
        }
        #chat-widget-btn:active {
            transform: scale(0.95);
        }
        #chat-widget-btn::after {
            content: '';
            position: absolute;
            width: 14px;
            height: 14px;
            background: #10B981;
            border: 3px solid #fff;
            border-radius: 50%;
            top: 2px;
            right: 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Chat Widget Box */
        #chat-widget-box {
            position: absolute;
            bottom: 76px;
            right: 0;
            width: 380px;
            height: 560px;
            max-height: calc(100vh - 120px);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transform-origin: bottom right;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        
        #chat-widget-box.chat-closed {
            opacity: 0;
            transform: scale(0.85) translateY(20px);
            pointer-events: none;
        }

        /* Header */
        #chat-widget-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 1.1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            z-index: 10;
        }
        .chat-header-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .chat-avatar-wrapper {
            position: relative;
            width: 38px;
            height: 38px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .online-indicator {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #10B981;
            border: 2px solid var(--primary);
            border-radius: 50%;
            bottom: -2px;
            right: -2px;
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .chat-header-text {
            display: flex;
            flex-direction: column;
        }
        .chat-header-text strong {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            line-height: 1.2;
            color: white;
        }
        .chat-header-text span {
            font-size: 0.75rem;
            opacity: 0.9;
            margin-top: 2px;
            color: rgba(255, 255, 255, 0.9);
        }
        .chat-header-close {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            cursor: pointer;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .chat-header-close:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.05);
        }
        .chat-header-action-btn {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            cursor: pointer;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
        .chat-header-action-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: scale(1.05);
        }

        /* Setup Screen */
        #chat-setup-screen {
            padding: 2rem 1.75rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.03) 0%, rgba(248, 250, 252, 1) 100%);
            overflow-y: auto;
        }
        .setup-welcome {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .setup-icon-box {
            width: 64px;
            height: 64px;
            background: rgba(29, 78, 216, 0.1);
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1.25rem;
        }
        .setup-welcome h3 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .setup-welcome p {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }

        .chat-input-wrapper {
            position: relative;
            margin-bottom: 1rem;
        }
        .chat-input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.95rem;
        }
        .chat-input-wrapper input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.6rem !important;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
            outline: none;
            background: white;
            transition: all 0.25s ease;
        }
        .chat-input-wrapper input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
        }
        .chat-submit-btn {
            width: 100%;
            padding: 0.8rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2);
            transition: all 0.2s;
        }
        .chat-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(29, 78, 216, 0.3);
        }

        .chat-quick-actions {
            margin-top: 1.5rem;
            border-top: 1px solid #edf2f7;
            padding-top: 1.25rem;
        }
        .quick-title {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .quick-btn-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .quick-btn {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 0.65rem 1rem;
            border-radius: 10px;
            font-size: 0.825rem;
            color: #475569;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        .quick-btn i {
            color: var(--primary);
            font-size: 0.8rem;
        }
        .quick-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateX(4px);
            color: var(--primary);
        }

        /* Active Chat Screen */
        #chat-active-screen {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
            background: radial-gradient(circle at bottom left, rgba(99, 102, 241, 0.02) 0%, rgba(248, 250, 252, 1) 100%);
        }
        #chat-widget-messages {
            flex: 1;
            padding: 1.25rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            scroll-behavior: smooth;
        }

        /* Message Bubbles Container */
        .chat-message-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            margin-bottom: 14px;
            max-width: 85%;
        }
        .chat-message-row.student-row {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .chat-message-row.admin-row {
            align-self: flex-start;
        }

        /* Avatar in Chat bubbles */
        .chat-bubble-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .student-row .chat-bubble-avatar {
            background: #e2e8f0;
            color: #475569;
        }

        /* Bubbles */
        .chat-bubble {
            padding: 10px 14px;
            font-size: 0.875rem;
            line-height: 1.45;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            word-break: break-word;
            position: relative;
        }
        .student-row .chat-bubble {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 16px 16px 4px 16px;
        }
        .admin-row .chat-bubble {
            background: white;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            border-radius: 16px 16px 16px 4px;
        }

        .chat-bubble-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 0.65rem;
            margin-top: 5px;
            opacity: 0.7;
        }
        .student-row .chat-bubble-meta {
            color: rgba(255, 255, 255, 0.9);
        }
        .admin-row .chat-bubble-meta {
            color: #64748b;
        }
        .chat-bubble-badge {
            background: rgba(29, 78, 216, 0.1);
            color: var(--primary);
            padding: 1px 4.5px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.6rem;
            text-transform: uppercase;
        }
        .student-row .chat-bubble-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        /* Typing Indicator Styling */
        #chat-typing-indicator {
            padding: 0 1.25rem 0.75rem 3.5rem;
            align-self: flex-start;
        }
        .typing-bubble {
            background: #e2e8f0;
            padding: 10px 14px;
            border-radius: 16px 16px 16px 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .typing-bubble .dot {
            width: 6px;
            height: 6px;
            background: #64748b;
            border-radius: 50%;
            animation: bounce-typing 1.4s infinite ease-in-out both;
        }
        .typing-bubble .dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-bubble .dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes bounce-typing {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1.0); }
        }

        /* Form */
        #chat-widget-form {
            display: flex;
            border-top: 1px solid #e2e8f0;
            background: white;
            padding: 0.8rem 1rem;
            gap: 8px;
            align-items: center;
        }
        #chat-widget-input {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 0.7rem 1.1rem;
            outline: none;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: #f8fafc;
        }
        #chat-widget-input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.08);
        }
        .chat-send-btn {
            background: var(--primary);
            color: white;
            border: none;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(29, 78, 216, 0.2);
        }
        .chat-send-btn:hover {
            transform: scale(1.08);
            background: var(--secondary);
            box-shadow: 0 6px 14px rgba(29, 78, 216, 0.3);
        }
        .chat-send-btn:active {
            transform: scale(0.95);
        }

        /* Responsive Breakpoints */
        @media (max-width: 480px) {
            #chat-widget-box {
                position: fixed;
                bottom: 84px;
                right: 16px;
                left: 16px;
                width: auto;
                height: calc(100vh - 120px);
                max-height: none;
                border-radius: 20px;
            }
            #chat-widget-container {
                bottom: 16px;
                right: 16px;
            }
            #chat-widget-btn {
                width: 55px;
                height: 55px;
            }
        }
    </style>
    <script src="{{ asset('js/chat-widget.js') }}"></script>
    @endif
</body>

</html>