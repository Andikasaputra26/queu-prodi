/**
 * Queue Registration Logic
 */

const Config = window.QueueConfig || {
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
    if (submitBtn) submitBtn.disabled = false;
    if (hintText) hintText.style.display = 'none';
}

function updateStats(data) {
    let total = 0, prodi = 0, ready = 0;
    if (data.services) {
        Object.values(data.services).forEach(s => {
            total += s.total;
            prodi += s.prodi;
            ready += s.ready;
        });
        
        document.querySelectorAll('.service-card').forEach(card => {
            const s = data.services[card.dataset.code];
            if (s) {
                const diproses = s.admin + s.prodi;
                const countEl = card.querySelector('.svc-count');
                if (countEl) countEl.textContent = `Diproses: ${diproses}`;
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

document.addEventListener('DOMContentLoaded', () => {
    fetch('/api/stats').then(r => r.json()).then(updateStats).catch(() => {});

    try {
        if (!window.Echo) return;

        const echo = new window.Echo({
            broadcaster: 'reverb',
            key: Config.reverb.key,
            wsHost: Config.reverb.host,
            wsPort: Config.reverb.port,
            forceTLS: false,
            enabledTransports: ['ws'],
        });

        echo.channel('queue-monitor').listen('.queue.updated', (e) => {
            if (typeof setWsStatus === 'function') setWsStatus(true);
            if (e.stats) updateStats(e.stats);
            if (e.action === 'new' && e.queue) {
                if (typeof showToast === 'function') showToast(`Antrian baru: ${e.queue.queue_number}`, 'info', 3000);
            }
        });

        echo.connector.socket.on('connect',    () => { if (typeof setWsStatus === 'function') setWsStatus(true); });
        echo.connector.socket.on('disconnect', () => { if (typeof setWsStatus === 'function') setWsStatus(false); });
    } catch(err) { console.warn('WebSocket:', err); }
});
