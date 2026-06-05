/**
 * Chat Widget Logic for Students
 */

let sipinterChatSessionId = localStorage.getItem('sipinter_chat_session_id');
if (!sipinterChatSessionId) {
    sipinterChatSessionId = 'ses-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now();
    localStorage.setItem('sipinter_chat_session_id', sipinterChatSessionId);
}

let chatEcho = null;
let typingTimeout = null;

function toggleChat() {
    const chatBox = document.getElementById('chat-widget-box');
    const chatBtn = document.getElementById('chat-widget-btn');
    if (!chatBox || !chatBtn) return;

    const chatIcon = chatBtn.querySelector('.chat-icon');
    const closeIcon = chatBtn.querySelector('.close-icon');

    const isClosed = chatBox.classList.contains('chat-closed');
    const resetBtn = document.getElementById('chat-header-reset-btn');

    if (isClosed) {
        chatBox.classList.remove('chat-closed');
        if (chatIcon) chatIcon.style.display = 'none';
        if (closeIcon) closeIcon.style.display = 'block';

        // Auto start session or load if name is in localStorage
        const name = localStorage.getItem('sipinter_chat_name');
        if (name) {
            document.getElementById('chat-setup-screen').style.display = 'none';
            document.getElementById('chat-active-screen').style.display = 'flex';
            if (resetBtn) resetBtn.style.display = 'flex';
            loadStudentMessages();
            initStudentWs();
        } else {
            document.getElementById('chat-setup-screen').style.display = 'flex';
            document.getElementById('chat-active-screen').style.display = 'none';
            if (resetBtn) resetBtn.style.display = 'none';
        }
    } else {
        chatBox.classList.add('chat-closed');
        if (chatIcon) chatIcon.style.display = 'block';
        if (closeIcon) closeIcon.style.display = 'none';
    }
}

function startWithQuery(query) {
    const nameInput = document.getElementById('chat-student-name');
    if (!nameInput) return;

    let name = nameInput.value.trim();
    if (!name) {
        name = 'Mahasiswa';
        nameInput.value = name;
    }

    window.pendingQuery = query;

    // Call startStudentChat directly by mimicking submit event
    startStudentChat({ preventDefault: () => { } });
}

async function startStudentChat(e) {
    if (e && e.preventDefault) e.preventDefault();
    const nameInput = document.getElementById('chat-student-name');
    if (!nameInput) return;

    const name = nameInput.value.trim();
    if (!name) return;

    localStorage.setItem('sipinter_chat_name', name);

    try {
        const res = await fetch('/chat/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                session_id: sipinterChatSessionId,
                student_name: name
            })
        });
        const data = await res.json();

        document.getElementById('chat-setup-screen').style.display = 'none';
        document.getElementById('chat-active-screen').style.display = 'flex';

        const resetBtn = document.getElementById('chat-header-reset-btn');
        if (resetBtn) resetBtn.style.display = 'flex';

        if (data.session.status === 'closed') {
            lockStudentChat();
        }

        await loadStudentMessages();
        initStudentWs();

        // If there's a pending query, send it!
        if (window.pendingQuery) {
            const query = window.pendingQuery;
            window.pendingQuery = null;

            const input = document.getElementById('chat-widget-input');
            if (input && !input.disabled) {
                input.value = query;
                sendStudentMessage({ preventDefault: () => { } });
            }
        }
    } catch (e) {
        console.error(e);
    }
}

async function loadStudentMessages() {
    try {
        const res = await fetch(`/chat/${sipinterChatSessionId}/messages?reader=student`);

        if (res.status === 404) {
            lockStudentChat();
            return;
        }

        const msgs = await res.json();

        const container = document.getElementById('chat-widget-messages');
        container.innerHTML = '';

        msgs.forEach(m => appendStudentMessage(m));
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 100);
    } catch (e) { console.error(e); }
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

function appendStudentMessage(m) {
    const container = document.getElementById('chat-widget-messages');
    if (!container) return;

    const isStudent = m.sender === 'student';

    // Hide typing indicator
    const typingIndicator = document.getElementById('chat-typing-indicator');
    if (typingIndicator) {
        typingIndicator.style.display = 'none';
    }
    if (typingTimeout) {
        clearTimeout(typingTimeout);
        typingTimeout = null;
    }

    // Create row
    const row = document.createElement('div');
    row.className = `chat-message-row ${isStudent ? 'student-row animate__animated animate__fadeInUp' : 'admin-row animate__animated animate__fadeInUp'}`;
    row.style.animationDuration = '0.25s';

    // Avatar
    const avatar = document.createElement('div');
    avatar.className = 'chat-bubble-avatar';
    if (isStudent) {
        avatar.innerHTML = '<i class="fas fa-user-graduate"></i>';
    } else {
        avatar.innerHTML = '<i class="fas fa-robot"></i>';
    }

    // Bubble
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble';

    // Text
    const textSpan = document.createElement('span');
    textSpan.innerHTML = parseMarkdown(m.message);
    bubble.appendChild(textSpan);

    // Meta (Timestamp and Badge)
    const meta = document.createElement('div');
    meta.className = 'chat-bubble-meta';

    const badge = document.createElement('span');
    badge.className = 'chat-bubble-badge';
    badge.textContent = isStudent ? 'MHS' : 'SI-PINTER';
    meta.appendChild(badge);

    const timeSpan = document.createElement('span');
    timeSpan.textContent = new Date(m.created_at || Date.now()).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    meta.appendChild(timeSpan);

    bubble.appendChild(meta);

    row.appendChild(avatar);
    row.appendChild(bubble);

    container.appendChild(row);
    container.scrollTop = container.scrollHeight;

    // Check if this is the closing message
    if (m.message.includes('Sesi obrolan ini telah ditutup')) {
        lockStudentChat();
    }
}

function lockStudentChat() {
    const input = document.getElementById('chat-widget-input');
    const btn = document.querySelector('#chat-widget-form button');
    const resetBtn = document.getElementById('chat-header-reset-btn');
    if (resetBtn) resetBtn.style.display = 'none';

    if (input) {
        input.disabled = true;
        input.placeholder = "Sesi telah ditutup oleh Admin.";
        input.style.background = "#cbd5e1";
    }
    if (btn) btn.disabled = true;

    // Add a button to start a new chat if it doesn't exist yet
    if (!document.getElementById('btn-new-chat-session')) {
        const restartDiv = document.createElement('div');
        restartDiv.style.padding = '15px';
        restartDiv.style.textAlign = 'center';
        restartDiv.style.width = '100%';
        restartDiv.innerHTML = `<button id="btn-new-chat-session" onclick="localStorage.removeItem('sipinter_chat_session_id'); localStorage.removeItem('sipinter_chat_name'); window.location.reload();" style="background: var(--primary); color: white; border: none; padding: 8px 16px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 600; box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2); transition: all 0.2s;">Mulai Sesi Baru</button>`;
        document.getElementById('chat-active-screen').appendChild(restartDiv);

        // Scroll to bottom
        const container = document.getElementById('chat-widget-messages');
        if (container) container.scrollTop = container.scrollHeight;
    }
}

async function sendStudentMessage(e) {
    if (e && e.preventDefault) e.preventDefault();
    const input = document.getElementById('chat-widget-input');
    if (!input) return;

    const text = input.value.trim();
    if (!text) return;

    input.value = '';
    input.style.height = 'auto';

    // Tampilkan pesan mahasiswa secara instan di UI (Optimistic UI Update)
    const tempMessage = {
        sender: 'student',
        message: text,
        created_at: new Date().toISOString()
    };
    appendStudentMessage(tempMessage);

    const typingIndicator = document.getElementById('chat-typing-indicator');
    if (typingIndicator) {
        typingIndicator.style.display = 'block';
        const container = document.getElementById('chat-widget-messages');
        if (container) container.scrollTop = container.scrollHeight;
    }

    if (typingTimeout) clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        const indicator = document.getElementById('chat-typing-indicator');
        if (indicator) indicator.style.display = 'none';
    }, 15000);

    try {
        const res = await fetch(`/chat/${sipinterChatSessionId}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: text, sender: 'student' })
        });

        if (res.status === 403) {
            lockStudentChat();
            if (typingIndicator) typingIndicator.style.display = 'none';
            return;
        }

        // Response JSON dari server tidak perlu di-append lagi karena sudah di-append secara instan di atas
        await res.json();
    } catch (e) {
        console.error(e);
        if (typingIndicator) typingIndicator.style.display = 'none';
    }
}

function initStudentWs() {
    if (chatEcho) return;

    let config = null;

    // Try to get config from QueueConfig if available
    if (typeof window.QueueConfig !== 'undefined') {
        config = window.QueueConfig;
    } else if (typeof window.TicketConfig !== 'undefined') {
        config = window.TicketConfig;
    }

    if (window.Echo !== undefined && config) {
        const isPusher = config.broadcaster === 'pusher';

        let echoOptions = {
            broadcaster: isPusher ? 'pusher' : 'reverb',
            key: isPusher ? config.pusher.key : config.reverb.key,
            forceTLS: isPusher ? true : false,
            enabledTransports: ['ws', 'wss'],
        };

        if (isPusher) {
            echoOptions.cluster = config.pusher.cluster;
        } else {
            echoOptions.wsHost = config.reverb.host;
            echoOptions.wsPort = config.reverb.port;
        }

        chatEcho = new window.Echo(echoOptions);
    }

    if (chatEcho) {
        chatEcho.channel(`chat.${sipinterChatSessionId}`).listen('.chat.message', (e) => {
            if (e.message.sender === 'admin') {
                // Hide typing indicator
                const typingIndicator = document.getElementById('chat-typing-indicator');
                if (typingIndicator) typingIndicator.style.display = 'none';
                if (typingTimeout) {
                    clearTimeout(typingTimeout);
                    typingTimeout = null;
                }

                appendStudentMessage(e.message);

                // Toggle open if closed
                const chatBox = document.getElementById('chat-widget-box');
                if (chatBox && chatBox.classList.contains('chat-closed')) {
                    toggleChat();
                }

                // Play notification sound
                try {
                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    audio.volume = 0.4;
                    audio.play();
                } catch (err) { }

                if (navigator.vibrate) navigator.vibrate(200);
            }
        });
    }
}

function confirmResetChat() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Mulai Ulang Obrolan?',
            text: 'Sesi chat saat ini akan diakhiri dan riwayat chat Anda akan direset secara lokal.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'var(--primary)',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Mulai Ulang',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                resetChatSession();
            }
        });
    } else {
        if (confirm('Apakah Anda yakin ingin mengakhiri sesi chat ini dan memulai ulang?')) {
            resetChatSession();
        }
    }
}

function resetChatSession() {
    localStorage.removeItem('sipinter_chat_session_id');
    localStorage.removeItem('sipinter_chat_name');
    window.location.reload();
}

// Auto-grow textarea height and submit message on Enter key press
function initChatInputListeners() {
    const input = document.getElementById('chat-widget-input');
    if (!input) return;

    // Auto-grow textarea based on text height
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Enter key to send (without Shift)
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendStudentMessage(e);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatInputListeners);
} else {
    initChatInputListeners();
}
