/**
 * Queue Registration Logic
 */

const Config = window.QueueConfig || {
    broadcaster: 'reverb',
    pusher: { key: '', cluster: '' },
    reverb: { key: '', host: 'localhost', port: 8080 }
};

let selectedService = null;

function selectService(el) {
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedService = {
        id: el.dataset.id, 
        name: el.dataset.name,
        code: el.dataset.code, 
        desc: el.dataset.desc, 
        color: el.dataset.color,
    };
    
    const serviceIdEl = document.getElementById('service_id');
    if (serviceIdEl) serviceIdEl.value = selectedService.id;

    const preview = document.getElementById('service-preview');
    const previewCode = document.getElementById('preview-code');
    const previewName = document.getElementById('preview-name');
    const previewDesc = document.getElementById('preview-desc');
    
    if (previewCode) {
        previewCode.textContent = selectedService.code;
        previewCode.style.background = selectedService.color + '20';
        previewCode.style.color = selectedService.color;
    }
    if (previewName) previewName.textContent = selectedService.name;
    if (previewDesc) previewDesc.textContent = selectedService.desc;
    if (preview) preview.classList.add('show');

    const submitBtn = document.getElementById('submit-btn');
    const hintText = document.getElementById('hint-text');
    const formInputs = document.getElementById('form-inputs');
    const fileGroup = document.getElementById('file-upload-group');
    const fileInput = document.getElementById('file');

    if (submitBtn) submitBtn.disabled = false;
    if (hintText) hintText.style.display = 'none';
    if (formInputs) formInputs.style.display = 'block';

    if (fileGroup) {
        const isSeminar = ['SP', 'SH', 'ST'].includes(selectedService.code);
        fileGroup.style.display = isSeminar ? 'block' : 'none';
        if (fileInput) fileInput.required = isSeminar;
    }

    // Auto scroll ke form pada layar mobile/HP
    if (window.innerWidth <= 992) {
        const formCard = document.getElementById('form-card');
        if (formCard) {
            setTimeout(() => {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
}

function updateStats(data) {
    let total = 0, diproses = 0;
    if (data.services) {
        Object.values(data.services).forEach(s => {
            total += s.total;
            diproses += s.admin;
        });
        
        document.querySelectorAll('.service-card').forEach(card => {
            const s = data.services[card.dataset.code];
            if (s) {
                const countEl = card.querySelector('.svc-count');
                if (countEl) countEl.textContent = `Diproses: ${s.admin}`;
            }
        });
    }
    
    const totalEl = document.getElementById('stat-total');
    const prodiEl = document.getElementById('stat-prodi');
    const readyEl = document.getElementById('stat-ready');
    
    if (totalEl) totalEl.textContent = total;
    if (prodiEl) prodiEl.textContent = prodi;
    if (readyEl) readyEl.textContent = ready;
}

function refreshPublicTracking() {
    const tbody = document.getElementById('public-tracking-list');
    if (!tbody) return;

    fetch('/api/waiting')
        .then(r => r.json())
        .then(list => {
            if (list.length === 0) {
                tbody.innerHTML = `<tr><td colspan="3" style="text-align: center; padding: 1.5rem; color: var(--text-muted);">Belum ada antrian yang sedang diproses.</td></tr>`;
                return;
            }
            
            tbody.innerHTML = '';
            list.forEach(q => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border); font-family: 'JetBrains Mono', monospace; font-weight: 700; color: ${q.service_color || 'var(--primary)'};">${q.queue_number}</td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border);">
                        <div style="font-weight: 600;">${q.patient_name}</div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.2rem;">${q.service_name}</div>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid var(--border);">
                        <span class="badge badge-${q.status}">${q.status_label}</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(() => {});
}

document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/stats').then(r => r.json()).then(updateStats).catch(() => {});
    refreshPublicTracking();

    try {
        if (!window.Echo) return;

        const isPusher = Config.broadcaster === 'pusher';
        if (isPusher && typeof Pusher !== 'undefined') {
            window.Pusher = Pusher;
        }

        let echoConfig = {
            broadcaster: isPusher ? 'pusher' : 'reverb',
            key: isPusher ? Config.pusher.key : Config.reverb.key,
            forceTLS: isPusher ? true : false,
            enabledTransports: ['ws', 'wss'],
        };

        if (isPusher) {
            echoConfig.cluster = Config.pusher.cluster;
        } else {
            echoConfig.wsHost = Config.reverb.host;
            echoConfig.wsPort = Config.reverb.port;
        }

        const echo = new window.Echo(echoConfig);

        echo.channel('queue-monitor').listen('.queue.updated', (e) => {
            if (typeof setWsStatus === 'function') setWsStatus(true);
            if (e.stats) updateStats(e.stats);
            refreshPublicTracking();
            if (e.action === 'new' && e.queue) {
                if (typeof showToast === 'function') showToast(`Antrian baru: ${e.queue.queue_number}`, 'info', 3000);
            }
        });

        if (echo.connector.pusher && echo.connector.pusher.connection) {
            echo.connector.pusher.connection.bind('connected', () => { if (typeof setWsStatus === 'function') setWsStatus(true); });
            echo.connector.pusher.connection.bind('disconnected', () => { if (typeof setWsStatus === 'function') setWsStatus(false); });
            echo.connector.pusher.connection.bind('unavailable', () => { if (typeof setWsStatus === 'function') setWsStatus(false); });
            echo.connector.pusher.connection.bind('failed', () => { if (typeof setWsStatus === 'function') setWsStatus(false); });
        } else if (echo.connector.socket) {
            echo.connector.socket.on('connect',    () => { if (typeof setWsStatus === 'function') setWsStatus(true); });
            echo.connector.socket.on('disconnect', () => { if (typeof setWsStatus === 'function') setWsStatus(false); });
        }
    } catch(err) { console.warn('WebSocket:', err); }
});
