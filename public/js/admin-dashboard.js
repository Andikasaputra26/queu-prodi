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
let tab = ''; // default empty to fetch all
let allQueues = [];
let dtTable;

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

// ─── Tab & search (Tabs are removed, this is a fallback if needed) ───
function switchTab(s, btn) {
    tab = s;
    if (btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    refreshQueues();
}

function filterQueues() {
    // DataTable handles global search
}

// ─── Queue list ───
async function refreshQueues() {
    const dateEl = document.getElementById('filter-date');
    const svcEl  = document.getElementById('filter-service');
    const statusEl = document.getElementById('filter-status');
    
    const date = dateEl ? dateEl.value : '';
    const svc  = svcEl ? svcEl.value : '';
    const statusVal = statusEl ? statusEl.value : '';
    
    let url = '/api/admin/today?';
    if (statusVal) url += `status=${statusVal}&`;
    else if (tab) url += `status=${tab}&`; // fallback to tab if used
    if (date) url += `date=${date}&`;
    if (svc) url += `service_id=${svc}&`;

    try {
        const res = await fetch(url);
        allQueues = await res.json();
        renderQueues(allQueues);
    } catch(e) {}
}

function exportReport() {
    const dateEl = document.getElementById('filter-date');
    const svcEl  = document.getElementById('filter-service');
    
    const date = dateEl ? dateEl.value : '';
    const svc  = svcEl ? svcEl.value : '';
    
    let url = '/admin/export?';
    if (tab) url += `status=${tab}&`;
    if (date) url += `date=${date}&`;
    if (svc) url += `service_id=${svc}&`;

    window.location.href = url;
}

function renderQueues(list) {
    const totalLbl = document.getElementById('q-total-lbl');
    if (totalLbl) totalLbl.textContent = `${list.length} antrian`;
    
    if (dtTable) {
        dtTable.clear();
        dtTable.rows.add(list);
        dtTable.draw();
    }
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
        const { value: reason } = await Swal.fire({
            title: 'Tolak Pengajuan',
            input: 'textarea',
            inputLabel: 'Masukkan alasan penolakan (kosongkan jika tidak ada):',
            showCancelButton: true,
            confirmButtonText: 'Tolak',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#DC2626'
        });
        if (reason === undefined) return; // User cancelled
        payload.reject_reason = reason;
    } else if (status === 'done') {
        const { value: docNum } = await Swal.fire({
            title: 'Selesaikan Surat',
            input: 'text',
            inputLabel: 'Masukkan Nomor Surat / Dokumen:',
            inputValue: allQueues.find(q => q.id === id).document_number || '',
            showCancelButton: true,
            confirmButtonText: 'Selesai',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10B981'
        });
        if (docNum === undefined) return; // User cancelled
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
    const result = await Swal.fire({
        title: 'Antrikan Ulang',
        text: 'Mahasiswa sudah memperbaiki dokumen dan antrikan ulang surat ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#F59E0B',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Antrikan Ulang'
    });
    if (!result.isConfirmed) return;
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
    const result = await Swal.fire({
        title: 'Reset Antrian?',
        text: 'Semua antrian hari ini akan di-reset. Tindakan ini tidak bisa dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Reset Semua'
    });
    if (!result.isConfirmed) return;
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
    dtTable = $('#queues-table').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        order: [[0, 'desc']], // Sort by time desc
        columns: [
            { data: 'created_at' },
            { 
                data: 'queue_number', 
                render: (data, type, row) => `<span style="color:${row.service_color || '#6366F1'}; font-weight: 900; font-family: 'JetBrains Mono', monospace;">${data}</span>` 
            },
            { data: 'patient_name', render: (data) => `<span style="font-weight: 600;">${data}</span>` },
            { 
                data: 'phone', 
                render: (data, type, row) => {
                    if (!data) return '-';
                    const num = data.startsWith('0') ? '62' + data.substring(1) : data.replace(/[^0-9]/g,'');
                    return `<a href="https://wa.me/${num}?text=Halo ${encodeURIComponent(row.patient_name)}, surat Anda (${row.service_name}) saat ini berstatus: ${row.status_label}." target="_blank" class="btn btn-success" style="padding:0.3rem 0.5rem; font-size:0.72rem; border-radius: 6px;"><i class="fab fa-whatsapp"></i> ${data}</a>`;
                } 
            },
            { 
                data: 'service_name', 
                render: (data, type, row) => {
                    let html = `<span style="color:${row.service_color || '#6366F1'}; font-weight: 600;">${data || '-'}</span>`;
                    if (row.file_path) {
                        const isWord = row.file_path.toLowerCase().endsWith('.doc') || row.file_path.toLowerCase().endsWith('.docx');
                        const icon = isWord ? 'fa-file-word' : 'fa-file-pdf';
                        const color = isWord ? '#2B579A' : '#F40F02';
                        const text = isWord ? 'Unduh Word' : 'Lihat PDF';
                        html += `<br><a href="${row.file_path}" target="_blank" class="btn btn-ghost" style="padding: 0.2rem 0.4rem; font-size: 0.65rem; margin-top: 0.3rem; border-color: ${color}; color: ${color};"><i class="fas ${icon}"></i> ${text}</a>`;
                    }
                    return html;
                }
            },
            { 
                data: 'document_number', 
                render: data => data ? `<span style="font-weight: 700;">${data}</span>` : '-' 
            },
            { 
                data: 'status', 
                render: (data, type, row) => `<span class="badge badge-${data}">${row.status_label}</span>` 
            },
            { 
                data: 'id', 
                orderable: false,
                render: (data, type, row) => {
                    let actions = '<div style="display: flex; gap: 0.3rem; flex-wrap: wrap;">';
                    if (row.status === 'pending') {
                        actions += `<button class="btn btn-warning" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="updateStatus(${data},'admin')">Proses</button>`;
                    } else if (row.status === 'admin') {
                        actions += `<button class="btn btn-success" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="updateStatus(${data},'done')">Selesai</button>`;
                    } else if (row.status === 'done') {
                        actions += `<button class="btn btn-primary" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="updateStatus(${data},'picked_up')"><i class="fas fa-hand-holding"></i> Diambil</button>`;
                    } else if (row.status === 'rejected') {
                        actions += `<button class="btn btn-warning" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="requeue(${data})"><i class="fas fa-redo"></i> Ulang</button>`;
                    }
                    if (row.status !== 'done' && row.status !== 'rejected' && row.status !== 'picked_up') {
                        actions += `<button class="btn btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="updateStatus(${data},'rejected')">❌ Tolak</button>`;
                    }
                    actions += '</div>';
                    return actions;
                }
            }
        ]
    });

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
