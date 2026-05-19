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

        @media (max-width: 768px) {
            .navbar {
                padding: 0.875rem 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .nav-links {
                display: none;
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
        <div class="ws-status">
            <div class="ws-dot" id="ws-dot"></div>
            <span id="ws-text">Live</span>
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

        // Tampilkan pesan sukses/error dari backend otomatis
        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                showToast("{{ session('success') }}", 'success');
            @endif
            @if(session('error'))
                showToast("{{ session('error') }}", 'error');
            @endif
        });
    </script>
    
    <!-- Global Admin Chat Notifications -->
    @if(request()->routeIs('admin.*'))
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const isPusher = '{{ env("BROADCAST_CONNECTION") }}' === 'pusher';
            
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
                    // Play notification sound
                    try {
                        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        audio.volume = 0.5;
                        audio.play();
                    } catch(err) {}

                    // Show toast notification using SweetAlert2
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
    <!-- Chat Widget (Public Only) -->
    @if(!request()->routeIs('admin.*') && !request()->routeIs('login') && !request()->routeIs('monitor.display'))
    <div id="chat-widget-container">
        <button id="chat-widget-btn" onclick="toggleChat()"><i class="fas fa-comment-dots"></i></button>
        <div id="chat-widget-box" style="display: none;">
            <div id="chat-widget-header">
                <div>
                    <strong style="display: block; font-size: 0.95rem;">Live Chat Bantuan</strong>
                    <span style="font-size: 0.75rem; opacity: 0.8;">Admin aktif membalas</span>
                </div>
                <button onclick="toggleChat()" style="background: none; border: none; color: white; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            
            <div id="chat-setup-screen">
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; text-align: center;">Silakan masukkan nama Anda untuk memulai obrolan dengan Admin Prodi.</p>
                <form onsubmit="startStudentChat(event)" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <input type="text" id="chat-student-name" placeholder="Nama Mahasiswa / NIM" required class="form-control" style="padding: 0.6rem; border-radius: 8px; border: 1px solid var(--border);">
                    <button type="submit" class="btn btn-primary" style="padding: 0.6rem; border-radius: 8px;">Mulai Chat</button>
                </form>
            </div>

            <div id="chat-active-screen" style="display: none; flex-direction: column; flex: 1; overflow: hidden;">
                <div id="chat-widget-messages">
                    <!-- Messages go here -->
                </div>
                <form id="chat-widget-form" onsubmit="sendStudentMessage(event)">
                    <input type="text" id="chat-widget-input" placeholder="Ketik pesan..." required autocomplete="off">
                    <button type="submit"><i class="fas fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        #chat-widget-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: 'Inter', sans-serif; }
        
        #chat-widget-btn { 
            width: 65px; height: 65px; border-radius: 50%; 
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white; border: none; box-shadow: 0 8px 24px rgba(29, 78, 216, 0.4); 
            font-size: 1.75rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex; align-items: center; justify-content: center; position: relative;
        }
        #chat-widget-btn:hover { transform: scale(1.08) translateY(-4px); box-shadow: 0 12px 28px rgba(29, 78, 216, 0.5); }
        #chat-widget-btn::after { 
            content: ''; position: absolute; width: 14px; height: 14px; background: #10B981; 
            border: 3px solid white; border-radius: 50%; top: 2px; right: 2px; 
        }

        #chat-widget-box { 
            position: absolute; bottom: 85px; right: 0; width: 360px; height: 500px; 
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(16px);
            border-radius: 20px; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.2), 0 0 0 1px rgba(0,0,0,0.05); 
            display: flex; flex-direction: column; overflow: hidden; 
            transform-origin: bottom right; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        #chat-widget-header { 
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); 
            color: white; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 10;
        }
        #chat-widget-header strong { display: block; font-size: 1.05rem; font-weight: 700; letter-spacing: 0.01em; }
        #chat-widget-header span { font-size: 0.75rem; opacity: 0.9; display: flex; align-items: center; gap: 0.3rem; margin-top: 0.2rem; }
        #chat-widget-header span::before { content: ''; display: inline-block; width: 6px; height: 6px; background: #10B981; border-radius: 50%; }

        #chat-setup-screen { padding: 2rem; display: flex; flex-direction: column; justify-content: center; flex: 1; text-align: center; background: #f8fafc; }
        #chat-setup-screen i { font-size: 3rem; color: var(--primary); margin-bottom: 1rem; opacity: 0.8; }
        
        #chat-widget-messages { 
            flex: 1; padding: 1.25rem; overflow-y: auto; display: flex; flex-direction: column; 
            background: #f8fafc; scroll-behavior: smooth; 
        }
        
        #chat-widget-form { display: flex; border-top: 1px solid rgba(0,0,0,0.05); background: white; padding: 0.75rem; gap: 0.5rem; align-items: center; }
        #chat-widget-input { 
            flex: 1; border: 1px solid var(--border); border-radius: 99px; padding: 0.75rem 1.25rem; 
            outline: none; font-size: 0.9rem; transition: all 0.2s; background: #f8fafc;
        }
        #chat-widget-input:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1); }
        #chat-widget-form button { 
            background: var(--primary); color: white; border: none; width: 42px; height: 42px; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 1rem; transition: all 0.2s; flex-shrink: 0;
        }
        #chat-widget-form button:hover { transform: scale(1.05); background: var(--secondary); }

        @media (max-width: 480px) {
            #chat-widget-box { width: calc(100vw - 40px); height: calc(100vh - 120px); max-height: 600px; bottom: 80px; }
            #chat-widget-container { bottom: 20px; right: 20px; }
        }
    </style>
    <script src="{{ asset('js/chat-widget.js') }}"></script>
    @endif
</body>

</html>