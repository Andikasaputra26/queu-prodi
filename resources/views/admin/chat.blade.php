@extends('layouts.app')

@section('title', 'Pusat Bantuan / Live Chat — SI-PINTER')

@push('styles')
    <style>
        .chat-container {
            display: flex;
            height: calc(100vh - 180px);
            background: var(--surface-2);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-top: 1rem;
        }

        .chat-sidebar {
            width: 320px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .chat-sidebar-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            color: var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-session-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-session-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--surface-3);
            cursor: pointer;
            transition: all 0.2s;
        }

        .chat-session-item:hover {
            background: var(--surface-3);
        }

        .chat-session-item.active {
            background: rgba(29, 78, 216, 0.1);
            border-left: 4px solid var(--primary);
        }

        .chat-session-item .student-name {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.25rem;
        }

        .chat-session-item .student-phone {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .chat-session-item .status-badge {
            font-size: 0.65rem;
            padding: 0.2rem 0.5rem;
            border-radius: 99px;
            font-weight: 700;
        }

        .chat-session-item .status-open {
            background: rgba(16, 185, 129, 0.2);
            color: #047857;
        }

        .chat-session-item .status-closed {
            background: rgba(107, 114, 128, 0.2);
            color: #374151;
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--surface-2);
        }

        .chat-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text);
        }

        .chat-messages {
            flex: 1;
            padding: 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #f8fafc;
        }

        .message-bubble {
            max-width: 75%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            line-height: 1.5;
            position: relative;
        }

        .message-admin {
            align-self: flex-end;
            background: var(--primary);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-student {
            align-self: flex-start;
            background: white;
            color: var(--text);
            border: 1px solid var(--border);
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 0.25rem;
            text-align: right;
        }

        .chat-input-area {
            padding: 1.25rem;
            border-top: 1px solid var(--border);
            background: #fff;
            display: flex;
            gap: 0.5rem;
        }

        .chat-input-area input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
        }

        .chat-input-area input:focus {
            border-color: var(--primary);
        }

        .chat-input-area button {
            padding: 0 1.5rem;
            border-radius: 8px;
        }

        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }

        .empty-chat i {
            font-size: 4rem;
            color: var(--border);
            margin-bottom: 1rem;
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
        <div class="chat-session-list" id="session-list">
            <div style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
                Memuat data...
            </div>
        </div>
    </div>

    <div class="chat-main" id="chat-main" style="display: none;">
        <div class="chat-header">
            <div>
                <h3 id="current-chat-name">Nama Mahasiswa</h3>
                <div style="font-size: 0.8rem; color: var(--text-muted);" id="current-chat-phone">08123456789</div>
            </div>
            <button class="btn btn-danger" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="closeCurrentSession()">
                <i class="fas fa-times-circle"></i> Tutup Sesi
            </button>
        </div>
        
        <div class="chat-messages" id="chat-messages">
        </div>

        <form class="chat-input-area" id="chat-form">
            <input type="text" id="chat-input" placeholder="Ketik balasan untuk mahasiswa..." autocomplete="off" required>
            <button type="submit" class="btn btn-primary" id="chat-submit-btn">
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

<script>
    let currentSessionId = null;
    let echoInstance = null;
    let activeChannel = null;

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
            const sessions = await res.json();
            
            const listEl = document.getElementById('session-list');
            listEl.innerHTML = '';
            
            if (sessions.length === 0) {
                listEl.innerHTML = '<div style="padding: 1rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">Belum ada riwayat obrolan.</div>';
                return;
            }

            sessions.forEach(s => {
                const isClosed = s.status === 'closed';
                const d = document.createElement('div');
                d.className = `chat-session-item ${currentSessionId === s.session_id ? 'active' : ''}`;
                d.onclick = () => openSession(s.session_id, s.student_name, s.student_phone, s.status);
                
                const time = moment(s.last_activity_at).format('DD/MM HH:mm');
                
                d.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div class="student-name" style="display: flex; align-items: center; gap: 0.5rem;">
                            ${s.student_name}
                            ${s.unread_count > 0 ? `<span style="background: var(--danger); color: white; border-radius: 50%; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: bold;">${s.unread_count}</span>` : ''}
                        </div>
                        <span class="status-badge ${isClosed ? 'status-closed' : 'status-open'}">${isClosed ? 'Selesai' : 'Aktif'}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem;">
                        <div class="student-phone"><i class="fas fa-phone-alt" style="font-size: 0.7rem;"></i> ${s.student_phone || '-'}</div>
                        <div style="font-size: 0.7rem; color: var(--border);">${time}</div>
                    </div>
                `;
                listEl.appendChild(d);
            });
        } catch (e) {
            console.error(e);
        }
    }

    async function openSession(id, name, phone, status) {
        currentSessionId = id;
        document.getElementById('empty-chat').style.display = 'none';
        document.getElementById('chat-main').style.display = 'flex';
        
        document.getElementById('current-chat-name').textContent = name;
        document.getElementById('current-chat-phone').textContent = phone || '-';
        
        const input = document.getElementById('chat-input');
        const btn = document.getElementById('chat-submit-btn');
        if (status === 'closed') {
            input.disabled = true;
            btn.disabled = true;
            input.placeholder = "Sesi telah ditutup.";
        } else {
            input.disabled = false;
            btn.disabled = false;
            input.placeholder = "Ketik balasan untuk mahasiswa...";
            input.focus();
        }

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

    function appendMessage(m) {
        const msgsEl = document.getElementById('chat-messages');
        const isAdmin = m.sender === 'admin';
        const d = document.createElement('div');
        d.className = `message-bubble ${isAdmin ? 'message-admin' : 'message-student'}`;
        
        const time = moment(m.created_at).format('HH:mm');
        d.innerHTML = `
            <div>${m.message}</div>
            <div class="message-time">${time}</div>
        `;
        msgsEl.appendChild(d);
    }

    function scrollToBottom() {
        const msgsEl = document.getElementById('chat-messages');
        msgsEl.scrollTop = msgsEl.scrollHeight;
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
            loadSessions();
        } catch(e) { console.error(e); }
    });

    async function closeCurrentSession() {
        if (!currentSessionId) return;
        const result = await Swal.fire({
            title: 'Tutup Sesi?',
            text: 'Mahasiswa tidak akan bisa membalas lagi di sesi ini.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tutup',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#DC2626'
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
                loadSessions();
            } catch(e) { }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSessions();
        setInterval(() => {
            if (!currentSessionId) loadSessions();
        }, 30000);

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
    });

</script>
@endpush
