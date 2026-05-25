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
    <link rel="stylesheet" href="{{ asset('css/app-layout.css') }}">
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
        window.AppConfig = {
            sessionSuccess: @json(session('success')),
            sessionError: @json(session('error')),
            isAdminRoute: @json(request()->routeIs('admin.*')),
            broadcastConnection: @json(env('BROADCAST_CONNECTION')),
            pusherAppKey: @json(env('PUSHER_APP_KEY')),
            reverbAppKey: @json(env('VITE_REVERB_APP_KEY')),
            pusherCluster: @json(env('PUSHER_APP_CLUSTER')),
            reverbHost: @json(env('VITE_REVERB_HOST', 'localhost')),
            reverbPort: @json(env('VITE_REVERB_PORT', 8080))
        };
    </script>

    @if(request()->routeIs('admin.*'))
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    @endif

    <script src="{{ asset('js/app-layout.js') }}"></script>

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

            <div id="chat-active-screen" style="display: none; flex-direction: column; flex: 1; overflow: hidden;">
                <div id="chat-widget-messages">
                </div>
                
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
    
    <link rel="stylesheet" href="{{ asset('css/chat-widget.css') }}">
    <script src="{{ asset('js/chat-widget.js') }}"></script>
    @endif
</body>

</html>