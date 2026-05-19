/**
 * Chat Widget Logic for Students
 */

let sipinterChatSessionId = localStorage.getItem('sipinter_chat_session_id');
if (!sipinterChatSessionId) {
    sipinterChatSessionId = 'ses-' + Math.random().toString(36).substr(2, 9) + '-' + Date.now();
    localStorage.setItem('sipinter_chat_session_id', sipinterChatSessionId);
}

let chatEcho = null;

function toggleChat() {
    const chatBox = document.getElementById('chat-widget-box');
    const isHidden = chatBox.style.display === 'none' || chatBox.style.display === '';
    
    if (isHidden) {
        chatBox.style.display = 'flex';
        // Auto start session or load if name is in localStorage
        const name = localStorage.getItem('sipinter_chat_name');
        if (name) {
            document.getElementById('chat-setup-screen').style.display = 'none';
            document.getElementById('chat-active-screen').style.display = 'flex';
            loadStudentMessages();
            initStudentWs();
        } else {
            document.getElementById('chat-setup-screen').style.display = 'flex';
            document.getElementById('chat-active-screen').style.display = 'none';
        }
    } else {
        chatBox.style.display = 'none';
    }
}

async function startStudentChat(e) {
    e.preventDefault();
    const name = document.getElementById('chat-student-name').value.trim();
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
        
        if (data.session.status === 'closed') {
            lockStudentChat();
        }
        
        loadStudentMessages();
        initStudentWs();
    } catch(e) {
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
    } catch(e) { console.error(e); }
}

function appendStudentMessage(m) {
    const container = document.getElementById('chat-widget-messages');
    const isStudent = m.sender === 'student';
    
    const d = document.createElement('div');
    d.style.maxWidth = '85%';
    d.style.padding = '10px 14px';
    d.style.borderRadius = '16px';
    d.style.fontSize = '0.9rem';
    d.style.lineHeight = '1.45';
    d.style.marginBottom = '12px';
    d.style.boxShadow = '0 2px 5px rgba(0,0,0,0.02)';
    d.style.wordBreak = 'break-word';
    
    if (isStudent) {
        d.style.alignSelf = 'flex-end';
        d.style.background = 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%)';
        d.style.color = 'white';
        d.style.borderBottomRightRadius = '4px';
    } else {
        d.style.alignSelf = 'flex-start';
        d.style.background = 'white';
        d.style.border = '1px solid rgba(0,0,0,0.05)';
        d.style.color = 'var(--text)';
        d.style.borderBottomLeftRadius = '4px';
    }
    
    // Convert newlines to br
    d.innerHTML = m.message.replace(/\n/g, '<br>');
    
    // Add time
    const timeDiv = document.createElement('div');
    timeDiv.style.fontSize = '0.65rem';
    timeDiv.style.marginTop = '4px';
    timeDiv.style.textAlign = isStudent ? 'right' : 'left';
    timeDiv.style.opacity = '0.7';
    timeDiv.textContent = new Date(m.created_at || Date.now()).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    d.appendChild(timeDiv);

    container.appendChild(d);
    container.scrollTop = container.scrollHeight;
    
    // Check if this is the closing message
    if (m.message.includes('Sesi obrolan ini telah ditutup')) {
        lockStudentChat();
    }
}

function lockStudentChat() {
    const input = document.getElementById('chat-widget-input');
    const btn = document.querySelector('#chat-widget-form button');
    if (input) {
        input.disabled = true;
        input.placeholder = "Sesi telah ditutup oleh Admin.";
        input.style.background = "#e2e8f0";
    }
    if (btn) btn.disabled = true;

    // Add a button to start a new chat if it doesn't exist yet
    if (!document.getElementById('btn-new-chat-session')) {
        const restartDiv = document.createElement('div');
        restartDiv.style.padding = '10px';
        restartDiv.style.textAlign = 'center';
        restartDiv.innerHTML = `<button id="btn-new-chat-session" onclick="localStorage.removeItem('sipinter_chat_session_id'); localStorage.removeItem('sipinter_chat_name'); window.location.reload();" style="background: var(--primary); color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.8rem;">Mulai Sesi Baru</button>`;
        document.getElementById('chat-active-screen').appendChild(restartDiv);
        
        // Scroll to bottom
        const container = document.getElementById('chat-widget-messages');
        if (container) container.scrollTop = container.scrollHeight;
    }
}

async function sendStudentMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chat-widget-input');
    const text = input.value.trim();
    if (!text) return;
    
    input.value = '';
    
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
            return;
        }
        
        const data = await res.json();
        appendStudentMessage(data);
    } catch(e) { console.error(e); }
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
                appendStudentMessage(e.message);
                const chatBox = document.getElementById('chat-widget-box');
                if (chatBox.style.display === 'none' || chatBox.style.display === '') {
                    toggleChat();
                }
                
                // Play notification sound
                try {
                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    audio.volume = 0.5;
                    audio.play();
                } catch(err) {}

                if (navigator.vibrate) navigator.vibrate(200);
            }
        });
    }
}
