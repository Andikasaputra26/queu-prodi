/**
 * Admin Dashboard Logic
 */

const Config = window.AdminConfig || {
    routes: {
        addManual: '',
        callNext: '',
        resetToday: ''
    },
    reverb: {
        key: '',
        host: 'localhost',
        port: 8080
    }
};

const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let tab = 'pending';
let allQueues = [];

// ─── Modal ───
function openModal()  { document.getElementById('manual-modal').classList.add('open'); }
function closeModal() { document.getElementById('manual-modal').classList.remove('open'); }

const modalEl = document.getElementById('manual-modal');
if (modalEl) {
    modalEl.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
}

// ─── Submit manual ───
async function submitManual() {
    const svcId = document.getElementById('manual-service').value;
    const name  = document.getElementById('manual-name').value.trim();
    const phone = document.getElementById('manual-phone').value.trim();

    if (!svcId)  { showToast('Pilih jenis surat terlebih dahulu', 'error'); return; }
    if (!name)   { showToast('Nama mahasiswa harus diisi', 'error'); return; }

    try {
        const res  = await fetch(Config.routes.addManual, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ service_id: svcId, patient_name: name, phone }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            closeModal();
            document.getElementById('manual-name').value  = '';
            document.getElementById('manual-phone').value = '';
            document.getElementById('manual-service').value = '';
            refreshQueues();
        }
    } catch(e) { showToast('Terjadi kesalahan', 'error'); }
}

// ─── Tab & search ───
function switchTab(s, btn) {
    tab = s;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    refreshQueues();
}

function filterQueues() {
    const searchEl = document.getElementById('q-search');
    if (!searchEl) return;
    
    const q = searchEl.value.toLowerCase();
    renderQueues(allQueues.filter(item =>
        item.patient_name.toLowerCase().includes(q) ||
        item.queue_number.toLowerCase().includes(q) ||
        (item.phone && item.phone.toLowerCase().includes(q))
    ));
}

// ─── Queue list ───
async function refreshQueues() {
    const dateEl = document.getElementById('filter-date');
    const svcEl  = document.getElementById('filter-service');
    
    const date = dateEl ? dateEl.value : '';
    const svc  = svcEl ? svcEl.value : '';
    
    let url = '/api/admin/today?';
    if (tab) url += `status=${tab}&`;
    if (date) url += `date=${date}&`;
    if (svc) url += `service_id=${svc}&`;

    try {
        const res = await fetch(url);
        allQueues = await res.json();
        renderQueues(allQueues);
    } catch(e) {}
}

function renderQueues(list) {
    const totalLbl = document.getElementById('q-total-lbl');
    if (totalLbl) totalLbl.textContent = `${list.length} antrian`;
    
    const el = document.getElementById('q-list');
    if (!el) return;

    if (!list.length) {
        el.innerHTML = `<div class="empty-state"><div class="empty-icon">📭</div>Tidak ada antrian</div>`;
        return;
    }
    el.innerHTML = '';
    list.forEach(q => {
        const color = q.service_color || '#6366F1';
        const div   = document.createElement('div');
        div.className = 'q-row';
        
        let actions = '';
        if (q.status === 'pending') {
            actions = `<button class="btn btn-warning" onclick="updateStatus(${q.id},'admin')">Proses & Input No Surat</button>`;
        } else if (q.status === 'admin') {
            actions = `<button class="btn btn-success" onclick="updateStatus(${q.id},'done')">Selesai</button>`;
        } else if (q.status === 'rejected') {
            actions = `<button class="btn btn-warning" onclick="requeue(${q.id})"><i class="fas fa-redo"></i> Antri Ulang</button>`;
        }

        div.innerHTML = `
            <div class="q-num" style="color:${color}">${q.queue_number}</div>
            <div style="flex:1; min-width:0">
                <div class="q-name">${q.patient_name}</div>
                <div class="q-meta">
                    <span style="color:${color};font-weight:600">${q.service_name}</span>
                    ${q.phone ? ` · ${q.phone}` : ''}
                    ${q.document_number ? ` · <span style="font-weight:700; color:var(--text)">Surat: ${q.document_number}</span>` : ''}
                    · ${q.created_at}
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem">
                <span class="badge badge-${q.status}">${q.status_label}</span>
                <div style="display:flex; gap:0.3rem">
                    ${actions}
                    <button class="btn btn-ghost" style="padding:0.3rem 0.5rem; font-size:0.72rem" title="Tolak" onclick="updateStatus(${q.id},'rejected')">❌</button>
                    ${q.phone ? `
                    <a href="https://wa.me/${q.phone.startsWith('0') ? '62' + q.phone.substring(1) : q.phone.replace(/[^0-9]/g,'')}?text=Halo ${encodeURIComponent(q.patient_name)}, surat Anda (${q.service_name}) saat ini berstatus: ${q.status_label}."
                       target="_blank" class="btn btn-success" style="padding:0.3rem 0.5rem; font-size:0.72rem" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>` : ''}
                </div>
            </div>
        `;
        el.appendChild(div);
    });
}

// ─── Print ticket ───
function printTicket(id, num, name, svc, counter) {
    const now = new Date().toLocaleString('id-ID');
    const printArea = document.getElementById('print-area');
    if (!printArea) return;

    printArea.innerHTML = `
        <div style="font-family:monospace; text-align:center; padding:20px; max-width:300px; margin:0 auto; border:2px solid #000">
            <div style="font-size:18px; font-weight:bold">PRODI — TIKET ANTRIAN</div>
            <div style="border-top:1px dashed #000; margin:10px 0"></div>
            <div style="font-size:60px; font-weight:900; letter-spacing:-2px">${num}</div>
            <div style="border-top:1px dashed #000; margin:10px 0"></div>
            <div style="font-size:13px; text-align:left; line-height:1.8">
                <div><strong>Nama:</strong> ${name}</div>
                <div><strong>Keperluan:</strong> ${svc}</div>
                ${counter ? `<div><strong>Ruangan:</strong> ${counter}</div>` : ''}
                <div><strong>Waktu:</strong> ${now}</div>
            </div>
            <div style="border-top:1px dashed #000; margin:10px 0"></div>
            <div style="font-size:11px">Harap standby dan perhatikan layar display</div>
        </div>
    `;
    window.print();
}

// ─── Call next ───
async function callNext(counterId) {
    const svcEl = document.getElementById(`svc-${counterId}`);
    const svcId = svcEl ? svcEl.value : null;
    
    const btn = event.target.closest('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    try {
        const res  = await fetch(Config.routes.callNext, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ counter_id: counterId, service_id: svcId || null }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'warning');
        if (data.success) setTimeout(() => location.reload(), 1200);
    } catch(e) { showToast('Terjadi kesalahan', 'error'); }
    finally { 
        if (btn) {
            btn.disabled = false; 
            btn.innerHTML = '<i class="fas fa-bell"></i> Panggil'; 
        }
    }
}

// ─── Update status ───
async function updateStatus(id, status) {
    let payload = { status };
    if (status === 'rejected') {
        const reason = prompt('Masukkan alasan penolakan (kosongkan jika tidak ada):');
        if (reason === null) return; // User cancelled
        payload.reject_reason = reason;
    } else if (status === 'admin') {
        const docNum = prompt('Masukkan Nomor Surat:');
        if (docNum === null) return; // User cancelled
        payload.document_number = docNum;
    }

    try {
        const res  = await fetch(`/admin/queue/${id}/status`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) { refreshQueues(); setTimeout(() => location.reload(), 1500); }
    } catch(e) { showToast('Terjadi kesalahan', 'error'); }
}

// ─── Requeue ───
async function requeue(id) {
    if (!confirm('Mahasiswa sudah memperbaiki dokumen dan antrikan ulang surat ini?')) return;
    try {
        const res  = await fetch(`/admin/queue/${id}/requeue`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) { refreshQueues(); setTimeout(() => location.reload(), 1500); }
    } catch(e) { showToast('Terjadi kesalahan', 'error'); }
}

// ─── Recall ───
async function recall(id) {
    try {
        const res  = await fetch(`/admin/queue/${id}/recall`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
    } catch(e) { showToast('Terjadi kesalahan', 'error'); }
}

// ─── Reset ───
async function resetToday() {
    if (!confirm('⚠️ Reset semua antrian hari ini?\nTindakan ini tidak bisa dibatalkan.')) return;
    try {
        const res  = await fetch(Config.routes.resetToday, {
            method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1500);
    } catch(e) { showToast('Terjadi kesalahan', 'error'); }
}

// ─── WebSocket Helper ───
function setWsStatus(connected) {
    const dot = document.getElementById('ws-dot');
    const text = document.getElementById('ws-text');
    if (dot) dot.style.background = connected ? 'var(--success)' : 'var(--danger)';
    if (text) text.textContent = connected ? 'Live' : 'Terputus';
}

// ─── Init ───
document.addEventListener('DOMContentLoaded', () => {
    refreshQueues();

    // WebSocket
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
            setWsStatus(true);
            refreshQueues();
            if (e.action === 'new' && e.queue) {
                showToast(`Antrian baru: ${e.queue.queue_number} — ${e.queue.patient_name}`, 'info');
            } else if (e.action === 'called' && e.queue) {
                showToast(`Dipanggil: ${e.queue.queue_number} → ${e.queue.counter_name || 'ruangan'}`, 'warning');
            }
        });

        // Reverb/Pusher connection events
        echo.connector.pusher.connection.bind('connected', () => setWsStatus(true));
        echo.connector.pusher.connection.bind('disconnected', () => setWsStatus(false));
        echo.connector.pusher.connection.bind('unavailable', () => setWsStatus(false));
        echo.connector.pusher.connection.bind('failed', () => setWsStatus(false));
    } catch(e) { console.warn(e); }

    setInterval(refreshQueues, 15000);
});
