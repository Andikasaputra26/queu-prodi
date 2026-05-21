@extends('layouts.app')

@section('title', 'Pusat Bantuan / Live Chat — SI-PINTER')

@push('styles')
    <style>
        .chat-container {
            display: flex;
            height: calc(100vh - 180px);
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 1rem;
            position: relative;
        }

        .chat-sidebar {
            width: 340px;
            background: #fff;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 10;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .chat-sidebar-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--surface-3);
            font-weight: 800;
            color: var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-box {
            position: relative;
            padding: 0 1.25rem 1rem 1.25rem;
            border-bottom: 1px solid var(--surface-3);
            background: #fff;
        }

        .search-box i {
            position: absolute;
            left: 2rem;
            top: 38%;
            transform: translateY(-50%);
            color: var(--text-muted);
            opacity: 0.6;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.25rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            font-size: 0.85rem;
            color: var(--text);
            outline: none;
            transition: all 0.2s;
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(29, 78, 216, 0.1);
        }

        .filter-tabs {
            display: flex;
            gap: 0.25rem;
            padding: 0.75rem 1.25rem;
            background: var(--surface);
            border-bottom: 1px solid var(--surface-3);
        }

        .filter-tab {
            flex: 1;
            padding: 0.4rem 0.25rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        .filter-tab.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 10px rgba(29, 78, 216, 0.2);
        }

        .chat-session-list {
            flex: 1;
            overflow-y: auto;
            background: #fff;
        }

        .chat-session-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--surface);
            cursor: pointer;
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }

        .chat-session-item:hover {
            background: var(--surface);
        }

        .chat-session-item.active {
            background: rgba(29, 78, 216, 0.04);
            border-left-color: var(--primary);
        }

        .avatar-initials {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 4px 10px rgba(29, 78, 216, 0.15);
        }

        .name-span {
            font-weight: 700;
            color: var(--text);
            font-size: 0.9rem;
        }

        .phone-span {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        .status-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
            border-radius: 99px;
            font-weight: 700;
        }

        .status-open {
            background: rgba(4, 120, 87, 0.1);
            color: var(--success);
        }

        .status-closed {
            background: rgba(107, 114, 128, 0.1);
            color: var(--text-muted);
        }

        .ai-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
        }

        .ai-badge.ai-active {
            background: rgba(29, 78, 216, 0.1);
            color: var(--primary);
        }

        .ai-badge.ai-inactive {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning);
        }

        .unread-count-badge {
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            padding: 0.15rem 0.4rem;
            border-radius: 99px;
            font-weight: 700;
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--surface);
        }

        .chat-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            z-index: 5;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text);
        }

        .ai-status-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--surface);
            padding: 0.4rem 0.75rem;
            border-radius: 99px;
            border: 1px solid var(--border);
            font-size: 0.8rem;
        }

        .ai-status-text {
            color: var(--text-muted);
            font-weight: 500;
        }

        .ai-status-active {
            color: var(--success);
            font-weight: 700;
        }

        .ai-status-inactive {
            color: var(--warning);
            font-weight: 700;
        }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            background: #f8fafc;
            background-image: radial-gradient(var(--border) 0.5px, transparent 0.5px);
            background-size: 12px 12px;
        }

        .message-bubble {
            max-width: 65%;
            padding: 0.85rem 1rem;
            border-radius: 16px;
            font-size: 0.875rem;
            line-height: 1.5;
            position: relative;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .message-admin {
            align-self: flex-end;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 12px rgba(29, 78, 216, 0.15);
        }

        .message-student {
            align-self: flex-start;
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.65rem;
            opacity: 0.75;
            margin-top: 0.35rem;
            text-align: right;
        }

        .canned-responses-wrapper {
            padding: 0.75rem 1.25rem;
            background: #fff;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            overflow-x: auto;
            white-space: nowrap;
        }

        .canned-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .canned-chips {
            display: flex;
            gap: 0.4rem;
            overflow-x: auto;
        }

        .canned-chip {
            padding: 0.35rem 0.75rem;
            border-radius: 99px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .canned-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: rgba(29, 78, 216, 0.04);
            transform: translateY(-1px);
        }

        .chat-input-area {
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--border);
            background: #fff;
            display: flex;
            gap: 0.75rem;
        }

        .chat-input-area input {
            flex: 1;
            padding: 0.75rem 1.25rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            outline: none;
            font-family: inherit;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .chat-input-area input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
        }

        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            background: #fff;
            padding: 2rem;
            text-align: center;
        }

        .empty-chat i {
            font-size: 4.5rem;
            color: var(--surface-3);
            margin-bottom: 1.5rem;
            background: var(--surface);
            padding: 2rem;
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.02);
        }

        .empty-chat h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .empty-chat p {
            font-size: 0.9rem;
            max-width: 320px;
            line-height: 1.5;
        }

        /* Toggle Switch */
        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 22px;
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .3s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
        }

        input:checked + .slider {
            background-color: var(--primary);
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /* Mobile Back Button */
        .btn-mobile-back {
            display: none !important;
        }

        @media (max-width: 768px) {
            .chat-container {
                position: relative;
                height: calc(100vh - 120px);
                border-radius: 0;
                margin-top: 0.5rem;
            }
            
            .chat-sidebar {
                width: 100%;
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 10;
            }
            
            .chat-sidebar.hidden {
                transform: translateX(-100%);
                pointer-events: none;
            }
            
            .chat-main {
                width: 100%;
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 5;
            }

            .btn-mobile-back {
                display: inline-flex !important;
            }

            .chat-header {
                padding: 0.75rem 1rem;
            }

            .chat-messages {
                padding: 1rem;
            }

            .message-bubble {
                max-width: 85%;
            }

            .canned-responses-wrapper {
                padding: 0.5rem 0.75rem;
            }
        }
    </style>
@endpush

@section('content')
<div class="page-header" style="margin-bottom: 0;">
    <div>
        <h1 class="page-title"><i class="fas fa-comments" style="color: var(--primary)"></i> Pusat Bantuan / Live Chat</h1>
        <div class="page-sub">Layanan bantuan dan keluhan mahasiswa secara real-time</div>
    </div>
</div>

<div class="chat-container">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <span>Daftar Obrolan</span>
            <button class="btn btn-ghost" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" onclick="loadSessions()">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="session-search" placeholder="Cari nama mahasiswa..." oninput="filterSessions()">
        </div>
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="setFilter('all', this)">Semua</button>
            <button class="filter-tab" onclick="setFilter('ai', this)"><i class="fas fa-robot"></i> AI</button>
            <button class="filter-tab" onclick="setFilter('manual', this)"><i class="fas fa-user-cog"></i> Manual</button>
        </div>
        <div class="chat-session-list" id="session-list">
            <div style="padding: 1.5rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                Memuat data...
            </div>
        </div>
    </div>

    <div class="chat-main" id="chat-main" style="display: none;">
        <div class="chat-header">
            <div style="display: flex; align-items: center;">
                <button class="btn btn-ghost btn-mobile-back" onclick="backToSidebar()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div>
                    <h3 id="current-chat-name">Nama Mahasiswa</h3>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;" id="current-chat-phone">08123456789</div>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="ai-status-control" id="ai-status-control-container">
                    <span class="ai-status-text"><i class="fas fa-robot"></i> Asisten AI: <strong id="ai-status-label" class="ai-status-active">Aktif</strong></span>
                    <label class="switch">
                        <input type="checkbox" id="ai-toggle-switch" onchange="toggleAiAssistant()">
                        <span class="slider round"></span>
                    </label>
                </div>
                
                <button class="btn btn-danger" style="padding: 0.45rem 0.85rem; font-size: 0.8rem; border-radius: 8px;" onclick="closeCurrentSession()">
                    <i class="fas fa-times-circle"></i> Tutup Sesi
                </button>
            </div>
        </div>
        
        <div class="chat-messages" id="chat-messages">
        </div>

        <div class="canned-responses-wrapper">
            <span class="canned-title">Balasan Cepat:</span>
            <div class="canned-chips">
                <button type="button" class="canned-chip" onclick="useCannedReply('Halo! Ada yang bisa kami bantu terkait persuratan akademik?')">👋 Sapa</button>
                <button type="button" class="canned-chip" onclick="useCannedReply('Baik, mohon tunggu sebentar ya. Dokumen/surat Anda sedang kami periksa.')">⏱️ Tunggu</button>
                <button type="button" class="canned-chip" onclick="useCannedReply('Selamat, pengajuan surat Anda telah selesai diproses! Silakan periksa status di sistem.')">✅ Selesai</button>
                <button type="button" class="canned-chip" onclick="useCannedReply('Mohon maaf, pengajuan ditolak karena berkas persyaratan kurang lengkap. Silakan lengkapi berkas dan ajukan ulang.')">❌ Berkas Kurang</button>
                <button type="button" class="canned-chip" onclick="useCannedReply('Pertanyaan Anda akan kami bantu koordinasikan dengan dosen/staf terkait.')">📞 Koordinasi</button>
                <button type="button" class="canned-chip" onclick="useCannedReply('Silakan unggah berkas persyaratan dalam format PDF yang jelas.')">📄 Format PDF</button>
            </div>
        </div>

        <form class="chat-input-area" id="chat-form">
            <input type="text" id="chat-input" placeholder="Ketik balasan untuk mahasiswa..." autocomplete="off" required>
            <button type="submit" class="btn btn-primary" id="chat-submit-btn" style="border-radius: 12px; padding: 0 1.5rem;">
                <i class="fas fa-paper-plane"></i> Kirim
            </button>
        </form>
    </div>

    <div class="empty-chat" id="empty-chat">
        <i class="far fa-comments"></i>
        <h3>Pilih obrolan</h3>
        <p>Silakan pilih sesi chat dari daftar di sebelah kiri untuk mulai membalas.</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

<script>
    moment.locale('id');

    let currentSessionId = null;
    let echoInstance = null;
    let allSessions = [];
    let activeFilter = 'all';
    let searchQuery = '';

    // Setup Echo
    if (window.Echo !== undefined) {
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

        echoInstance = new window.Echo(echoConfig);
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    async function loadSessions() {
        try {
            const res = await fetch('/admin/chat/list');
            allSessions = await res.json();
            renderSessionsList();
        } catch (e) {
            console.error(e);
        }
    }

    function setFilter(filterType, element) {
        activeFilter = filterType;
        document.querySelectorAll('.filter-tab').forEach(tab => tab.classList.remove('active'));
        element.classList.add('active');
        renderSessionsList();
    }

    function filterSessions() {
        searchQuery = document.getElementById('session-search').value.toLowerCase().trim();
        renderSessionsList();
    }

    function getInitials(name) {
        if (!name) return '?';
        const parts = name.trim().split(' ');
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function renderSessionsList() {
        const listEl = document.getElementById('session-list');
        listEl.innerHTML = '';
        
        let filtered = allSessions;
        
        // Filter by search query
        if (searchQuery) {
            filtered = filtered.filter(s => 
                s.student_name.toLowerCase().includes(searchQuery) || 
                (s.student_phone && s.student_phone.includes(searchQuery))
            );
        }
        
        // Filter by AI/Manual state
        if (activeFilter === 'ai') {
            filtered = filtered.filter(s => s.is_ai_active);
        } else if (activeFilter === 'manual') {
            filtered = filtered.filter(s => !s.is_ai_active);
        }
        
        if (filtered.length === 0) {
            listEl.innerHTML = `
                <div style="padding: 2.5rem 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                    <i class="fas fa-search" style="font-size: 1.8rem; margin-bottom: 0.75rem; opacity: 0.3;"></i>
                    <br>Tidak ada obrolan ditemukan.
                </div>
            `;
            return;
        }

        filtered.forEach(s => {
            const isClosed = s.status === 'closed';
            const isActive = currentSessionId === s.session_id;
            const d = document.createElement('div');
            d.className = `chat-session-item ${isActive ? 'active' : ''}`;
            d.onclick = () => openSession(s.session_id, s.student_name, s.student_phone, s.status, s.is_ai_active);
            
            const time = moment(s.last_activity_at).format('HH:mm');
            
            d.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div class="avatar-initials">${getInitials(s.student_name)}</div>
                        <div style="display: flex; flex-direction: column;">
                            <span class="name-span">${s.student_name}</span>
                            <span class="phone-span"><i class="fas fa-phone-alt" style="font-size: 0.65rem; opacity: 0.7;"></i> ${s.student_phone || '-'}</span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.3rem;">
                        <span class="status-badge ${isClosed ? 'status-closed' : 'status-open'}">${isClosed ? 'Selesai' : 'Aktif'}</span>
                        <span class="ai-badge ${s.is_ai_active ? 'ai-active' : 'ai-inactive'}">
                            ${s.is_ai_active ? '<i class="fas fa-robot"></i> AI' : '<i class="fas fa-user-cog"></i> Manual'}
                        </span>
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.75rem; font-size: 0.7rem; border-top: 1px dashed var(--surface-3); padding-top: 0.5rem; color: var(--text-muted);">
                    <div><i class="far fa-clock"></i> Aktif: ${time}</div>
                    ${s.unread_count > 0 ? `<span class="unread-count-badge">${s.unread_count} baru</span>` : ''}
                </div>
            `;
            listEl.appendChild(d);
        });
    }

    async function openSession(id, name, phone, status, isAiActive) {
        currentSessionId = id;
        document.getElementById('empty-chat').style.display = 'none';
        document.getElementById('chat-main').style.display = 'flex';
        
        // Hide sidebar on mobile screen when a session is active
        if (window.innerWidth <= 768) {
            document.querySelector('.chat-sidebar').classList.add('hidden');
        }

        document.getElementById('current-chat-name').textContent = name;
        document.getElementById('current-chat-phone').textContent = phone || '-';
        
        const input = document.getElementById('chat-input');
        const btn = document.getElementById('chat-submit-btn');
        const switchEl = document.getElementById('ai-toggle-switch');
        const labelEl = document.getElementById('ai-status-label');

        if (switchEl) {
            switchEl.checked = !!isAiActive;
            if (isAiActive) {
                labelEl.textContent = 'Aktif';
                labelEl.className = 'ai-status-active';
            } else {
                labelEl.textContent = 'Nonaktif';
                labelEl.className = 'ai-status-inactive';
            }
        }

        if (status === 'closed') {
            input.disabled = true;
            btn.disabled = true;
            input.placeholder = "Sesi telah ditutup.";
            document.getElementById('ai-status-control-container').style.opacity = '0.5';
            if (switchEl) switchEl.disabled = true;
        } else {
            input.disabled = false;
            btn.disabled = false;
            input.placeholder = "Ketik balasan untuk mahasiswa...";
            document.getElementById('ai-status-control-container').style.opacity = '1';
            if (switchEl) switchEl.disabled = false;
            input.focus();
        }

        // Highlight selected item in the list
        document.querySelectorAll('.chat-session-item').forEach(el => el.classList.remove('active'));
        
        try {
            const res = await fetch(`/chat/${id}/messages?reader=admin`);
            const msgs = await res.json();
            
            const msgsEl = document.getElementById('chat-messages');
            msgsEl.innerHTML = '';
            
            msgs.forEach(m => appendMessage(m));
            scrollToBottom();
        } catch (e) { console.error(e); }
        
        loadSessions();
    }

    function backToSidebar() {
        document.querySelector('.chat-sidebar').classList.remove('hidden');
    }

    function parseMarkdown(text) {
        if (!text) return '';
        
        // Escape HTML first to prevent XSS
        let html = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Replace headers
        html = html.replace(/^### (.*?)$/gm, '<h5 style="margin-top: 0.5rem; margin-bottom: 0.25rem; font-weight: 700;">$1</h5>');
        html = html.replace(/^## (.*?)$/gm, '<h4 style="margin-top: 0.75rem; margin-bottom: 0.25rem; font-weight: 700;">$1</h4>');
        html = html.replace(/^# (.*?)$/gm, '<h3 style="margin-top: 1rem; margin-bottom: 0.25rem; font-weight: 800;">$1</h3>');

        // Bold & Italic & inline code
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.*?)\*/g, '<em>$1</em>');
        html = html.replace(/`(.*?)`/g, '<code style="background: rgba(0,0,0,0.05); padding: 2px 4px; border-radius: 4px; font-family: monospace;">$1</code>');

        // Split by lines to parse lists
        const lines = html.split('\n');
        let inList = false; // false, 'ul', 'ol'
        let result = [];

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i];
            let trimmed = line.trim();
            
            // Bullet points
            if (trimmed.startsWith('* ') || trimmed.startsWith('- ')) {
                if (inList !== 'ul') {
                    if (inList) {
                        result.push(inList === 'ul' ? '</ul>' : '</ol>');
                    }
                    result.push('<ul style="margin-left: 1.25rem; margin-bottom: 0.5rem; padding-left: 0; list-style-type: disc;">');
                    inList = 'ul';
                }
                result.push('<li style="margin-bottom: 0.25rem;">' + trimmed.substring(2) + '</li>');
            }
            // Numbered list
            else if (/^\d+\.\s/.test(trimmed)) {
                if (inList !== 'ol') {
                    if (inList) {
                        result.push(inList === 'ul' ? '</ul>' : '</ol>');
                    }
                    result.push('<ol style="margin-left: 1.25rem; margin-bottom: 0.5rem; padding-left: 0; list-style-type: decimal;">');
                    inList = 'ol';
                }
                const content = trimmed.replace(/^\d+\.\s/, '');
                result.push('<li style="margin-bottom: 0.25rem;">' + content + '</li>');
            }
            // Regular line
            else {
                if (inList) {
                    result.push(inList === 'ul' ? '</ul>' : '</ol>');
                    inList = false;
                }
                result.push(line);
            }
        }
        
        if (inList) {
            result.push(inList === 'ul' ? '</ul>' : '</ol>');
        }

        html = result.join('\n');

        // Replace remaining single newlines (not inside tag blocks) with <br>
        html = html.replace(/\n/g, '<br>');
        
        // Clean up empty lines or duplicate <br>s
        html = html.replace(/(<br>\s*){2,}/g, '<br><br>');

        return html;
    }

    function appendMessage(m) {
        const msgsEl = document.getElementById('chat-messages');
        const isAdmin = m.sender === 'admin';
        const d = document.createElement('div');
        d.className = `message-bubble ${isAdmin ? 'message-admin' : 'message-student'}`;
        
        const time = moment(m.created_at).format('HH:mm');
        d.innerHTML = `
            <div>${parseMarkdown(m.message)}</div>
            <div class="message-time">${time}</div>
        `;
        msgsEl.appendChild(d);
    }

    function scrollToBottom() {
        const msgsEl = document.getElementById('chat-messages');
        msgsEl.scrollTop = msgsEl.scrollHeight;
    }

    function useCannedReply(text) {
        const input = document.getElementById('chat-input');
        if (input) {
            input.value = text;
            input.focus();
        }
    }

    async function toggleAiAssistant() {
        if (!currentSessionId) return;
        const switchEl = document.getElementById('ai-toggle-switch');
        const checked = switchEl.checked;
        
        switchEl.disabled = true;
        
        try {
            const res = await fetch(`/admin/chat/${currentSessionId}/toggle-ai`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
            });
            const data = await res.json();
            
            if (data.success) {
                switchEl.checked = data.is_ai_active;
                const labelEl = document.getElementById('ai-status-label');
                if (labelEl) {
                    if (data.is_ai_active) {
                        labelEl.textContent = 'Aktif';
                        labelEl.className = 'ai-status-active';
                    } else {
                        labelEl.textContent = 'Nonaktif';
                        labelEl.className = 'ai-status-inactive';
                    }
                }
                showToast(`Asisten AI untuk sesi ini berhasil ${data.is_ai_active ? 'diaktifkan' : 'dinonaktifkan'}.`, 'success');
            } else {
                switchEl.checked = !checked;
                showToast('Gagal mengubah status AI.', 'error');
            }
        } catch(e) {
            switchEl.checked = !checked;
            showToast('Terjadi kesalahan.', 'error');
        } finally {
            switchEl.disabled = false;
            loadSessions();
        }
    }

    document.getElementById('chat-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentSessionId) return;
        
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text) return;
        
        input.value = '';
        
        try {
            const res = await fetch(`/chat/${currentSessionId}/send`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ message: text, sender: 'admin' })
            });
            const data = await res.json();
            appendMessage(data); 
            scrollToBottom();
            
            // Auto switch toggle AI to false since admin answered manually
            const switchEl = document.getElementById('ai-toggle-switch');
            const labelEl = document.getElementById('ai-status-label');
            if (switchEl && switchEl.checked) {
                switchEl.checked = false;
                labelEl.textContent = 'Nonaktif';
                labelEl.className = 'ai-status-inactive';
            }
            
            loadSessions();
        } catch(e) { console.error(e); }
    });

    async function closeCurrentSession() {
        if (!currentSessionId) return;
        const result = await Swal.fire({
            title: 'Tutup Sesi?',
            text: 'Obrolan ini akan ditutup dan dihapus secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tutup',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#DC2626',
            reverseButtons: true
        });
        
        if (result.isConfirmed) {
            try {
                await fetch(`/admin/chat/${currentSessionId}/close`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }
                });
                showToast('Sesi ditutup dan dihapus.', 'success');
                currentSessionId = null;
                document.getElementById('chat-main').style.display = 'none';
                document.getElementById('empty-chat').style.display = 'flex';
                
                if (window.innerWidth <= 768) {
                    document.querySelector('.chat-sidebar').classList.remove('hidden');
                }
                
                loadSessions();
            } catch(e) { }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSessions();
        setInterval(loadSessions, 15000);

        if (echoInstance) {
            echoInstance.channel('admin-chat').listen('.chat.message', (e) => {
                if (e.message.sender === 'student') {
                    if (e.session.session_id === currentSessionId) {
                        appendMessage(e.message);
                        scrollToBottom();
                        
                        fetch(`/chat/${currentSessionId}/messages?reader=admin`).then(() => loadSessions());
                    }
                    
                    try {
                        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                        audio.volume = 0.5;
                        audio.play();
                    } catch(err) {}
                }
                
                loadSessions();
            });
        }
    });

</script>
@endpush
