
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
let tab = '';
let allQueues = [];
let dtTable;

function openModal() { document.getElementById('manual-modal').classList.add('open'); }
function closeModal() { document.getElementById('manual-modal').classList.remove('open'); }

const modalEl = document.getElementById('manual-modal');
if (modalEl) {
    modalEl.addEventListener('click', function (e) {
        if (e.target === this) closeModal();
    });
}

async function submitManual() {
    const svcId = document.getElementById('manual-service').value;
    const name = document.getElementById('manual-name').value.trim();
    const phone = document.getElementById('manual-phone').value.trim();

    if (!svcId) { showToast('Pilih jenis surat terlebih dahulu', 'error'); return; }
    if (!name) { showToast('Nama mahasiswa harus diisi', 'error'); return; }

    try {
        const res = await fetch(Config.routes.addManual, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ service_id: svcId, patient_name: name, phone }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            closeModal();
            document.getElementById('manual-name').value = '';
            document.getElementById('manual-phone').value = '';
            document.getElementById('manual-service').value = '';
            refreshQueues();
        }
    } catch (e) { showToast('Terjadi kesalahan', 'error'); }
}

function switchTab(s, btn) {
    tab = s;
    if (btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    refreshQueues();
}

function filterQueues() {
}

async function refreshQueues() {
    const dateEl = document.getElementById('filter-date');
    const svcEl = document.getElementById('filter-service');
    const statusEl = document.getElementById('filter-status');

    const date = dateEl ? dateEl.value : '';
    const svc = svcEl ? svcEl.value : '';
    const statusVal = statusEl ? statusEl.value : '';

    let url = '/api/admin/today?';
    if (statusVal) url += `status=${statusVal}&`;
    else if (tab) url += `status=${tab}&`;
    if (date) url += `date=${date}&`;
    if (svc) url += `service_id=${svc}&`;

    try {
        const res = await fetch(url);
        allQueues = await res.json();
        renderQueues(allQueues);
    } catch (e) { }
}

function exportReport() {
    const dateEl = document.getElementById('filter-date');
    const svcEl = document.getElementById('filter-service');

    const date = dateEl ? dateEl.value : '';
    const svc = svcEl ? svcEl.value : '';

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

async function callNext(counterId) {
    const svcEl = document.getElementById(`svc-${counterId}`);
    const svcId = svcEl ? svcEl.value : null;

    const btn = event.target.closest('button');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }

    try {
        const res = await fetch(Config.routes.callNext, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ counter_id: counterId, service_id: svcId || null }),
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'warning');
        if (data.success) setTimeout(() => location.reload(), 1200);
    } catch (e) { showToast('Terjadi kesalahan', 'error'); }
    finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell"></i> Panggil';
        }
    }
}

async function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('status', status);
    formData.append('_method', 'PATCH');

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
        if (reason === undefined) return;
        formData.append('reject_reason', reason);
    } else if (status === 'done') {
        const { value: formValues } = await Swal.fire({
            title: 'Selesaikan Surat',
            html: `
                <div style="text-align: left; font-family: inherit;">
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                        Unggah berkas final yang telah diisi, ditandatangani, dan distempel resmi. Berkas ini akan langsung dapat diunduh oleh mahasiswa.
                    </p>
                    <label for="swal-file" style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem; color: var(--text);">Pilih Berkas Final (PDF/Word/Gambar)</label>
                    <input id="swal-file" type="file" class="swal2-file" style="width: 100%; margin: 0; box-sizing: border-box; border-radius: 8px; border: 1px dashed #ccc; padding: 0.5rem;" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Selesai & Unggah',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10B981',
            preConfirm: () => {
                const fileInput = document.getElementById('swal-file');
                const file = fileInput.files[0];

                if (!file) {
                    Swal.showValidationMessage('Berkas surat wajib dipilih!');
                    return false;
                }

                return { file: file };
            }
        });
        if (formValues === undefined) return;
        if (formValues.file) {
            formData.append('file', formValues.file);
        }
    }

    try {
        const res = await fetch(`/admin/queue/${id}/status`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: formData,
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) { refreshQueues(); setTimeout(() => location.reload(), 1500); }
    } catch (e) { showToast('Terjadi kesalahan', 'error'); }
}

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
        const res = await fetch(`/admin/queue/${id}/requeue`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) { refreshQueues(); setTimeout(() => location.reload(), 1500); }
    } catch (e) { showToast('Terjadi kesalahan', 'error'); }
}

async function recall(id) {
    try {
        const res = await fetch(`/admin/queue/${id}/recall`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
    } catch (e) { showToast('Terjadi kesalahan', 'error'); }
}

async function deleteQueue(id) {
    const result = await Swal.fire({
        title: 'Hapus Antrian?',
        text: 'Apakah Anda yakin ingin menghapus antrian ini? Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/queue/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) {
            refreshQueues();
            setTimeout(() => location.reload(), 1500);
        }
    } catch (e) {
        showToast('Terjadi kesalahan', 'error');
    }
}

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
        const res = await fetch(Config.routes.resetToday, {
            method: 'DELETE', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        showToast(data.message, data.success ? 'success' : 'error');
        if (data.success) setTimeout(() => location.reload(), 1500);
    } catch (e) { showToast('Terjadi kesalahan', 'error'); }
}

function setWsStatus(connected) {
    const dot = document.getElementById('ws-dot');
    const text = document.getElementById('ws-text');
    if (dot) dot.style.background = connected ? 'var(--success)' : 'var(--danger)';
    if (text) text.textContent = connected ? 'Live' : 'Terputus';
}

document.addEventListener('DOMContentLoaded', () => {
    dtTable = $('#queues-table').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'created_at' },
            {
                data: 'queue_number',
                render: (data, type, row) => `<span style="color:${row.service_color || '#6366F1'}; font-weight: 900; font-family: 'JetBrains Mono', monospace;">${data}</span>`
            },
            {
                data: 'patient_name',
                render: (data) => {
                    const initial = data ? data.charAt(0).toUpperCase() : '?';
                    return `<div style="display:flex; align-items:center; gap:0.5rem;">
                        <span style="width:28px; height:28px; border-radius:50%; background:#EFF6FF; color:#1D4ED8; font-weight:700; display:inline-flex; align-items:center; justify-content:center; font-size:0.75rem; border: 1px solid #BFDBFE;">${initial}</span>
                        <span style="font-weight: 700; color: var(--text);">${data}</span>
                    </div>`;
                }
            },
            {
                data: 'phone',
                render: (data, type, row) => {
                    if (!data) return '-';
                    const num = data.startsWith('0') ? '62' + data.substring(1) : data.replace(/[^0-9]/g, '');
                    return `<a href="https://wa.me/${num}?text=Halo ${encodeURIComponent(row.patient_name)}, surat Anda (${row.service_name}) saat ini berstatus: ${row.status_label}." target="_blank" class="wa-badge">
                        <i class="fab fa-whatsapp"></i> ${data}
                    </a>`;
                }
            },
            {
                data: 'service_name',
                render: (data, type, row) => {
                    let html = `<span style="color:${row.service_color || '#6366F1'}; font-weight: 600;">${data || '-'}</span>`;
                    if (row.file_path && row.status !== 'done' && row.status !== 'picked_up') {
                        const isWord = row.file_path.toLowerCase().endsWith('.doc') || row.file_path.toLowerCase().endsWith('.docx');
                        const icon = isWord ? 'fa-file-word' : 'fa-file-pdf';
                        const text = isWord ? 'Unduh Word' : 'Lihat PDF';
                        html += `<br><a href="${row.file_path}" target="_blank" style="font-size: 0.72rem; color: #1D4ED8; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.3rem; margin-top: 0.25rem;"><i class="fas ${icon}"></i> ${text}</a>`;
                    }
                    return html;
                }
            },
            {
                data: 'file_path',
                render: (data, type, row) => {
                    if ((row.status === 'done' || row.status === 'picked_up') && data) {
                        const isWord = data.toLowerCase().endsWith('.doc') || data.toLowerCase().endsWith('.docx');
                        const icon = isWord ? 'fa-file-word' : 'fa-file-pdf';
                        const text = isWord ? 'Unduh Word' : 'Lihat PDF';
                        let html = `<a href="${data}" target="_blank" style="font-size: 0.72rem; color: #1D4ED8; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.3rem;"><i class="fas ${icon}"></i> ${text}</a>`;
                        if (row.document_number) {
                            html += `<br><span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 600;">No: ${row.document_number}</span>`;
                        }
                        return html;
                    }
                    return '<span style="color:#94a3b8; font-style:italic;">Belum diunggah</span>';
                }
            },
            {
                data: 'status',
                render: (data, type, row) => {
                    let color = 'waiting';
                    if (data === 'admin') color = 'calling';
                    else if (data === 'done' || data === 'picked_up') color = 'done';
                    else if (data === 'rejected') color = 'skip';
                    return `<span class="badge badge-${color}">${row.status_label}</span>`;
                }
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
                    } else if (row.status === 'rejected') {
                        actions += `<button class="btn btn-warning" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="requeue(${data})"><i class="fas fa-redo"></i> Ulang</button>`;
                    }
                    if (row.status !== 'done' && row.status !== 'rejected' && row.status !== 'picked_up') {
                        actions += `<button class="btn btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.72rem;" onclick="updateStatus(${data},'rejected')">❌ Tolak</button>`;
                    }
                    actions += `<button class="btn btn-danger" style="padding: 0.3rem 0.5rem; font-size: 0.72rem; background-color: #DC2626; border-color: #DC2626;" onclick="deleteQueue(${data})"><i class="fas fa-trash"></i> Hapus</button>`;
                    actions += '</div>';
                    return actions;
                }
            }
        ]
    });

    refreshQueues();

    $('#search-input').on('keyup', function () {
        dtTable.search(this.value).draw();
    });

    try {
        if (!window.Echo) return;

        const appConfig = window.AppConfig || {};
        const isPusher = appConfig.broadcastConnection === 'pusher';

        const echo = new window.Echo({
            broadcaster: isPusher ? 'pusher' : 'reverb',
            key: isPusher ? appConfig.pusherAppKey : appConfig.reverbAppKey,
            cluster: isPusher ? appConfig.pusherCluster : undefined,
            wsHost: isPusher ? undefined : appConfig.reverbHost,
            wsPort: isPusher ? undefined : (parseInt(appConfig.reverbPort) || 8080),
            forceTLS: isPusher ? true : false,
            enabledTransports: ['ws', 'wss'],
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

        echo.connector.pusher.connection.bind('connected', () => setWsStatus(true));
        echo.connector.pusher.connection.bind('disconnected', () => setWsStatus(false));
        echo.connector.pusher.connection.bind('unavailable', () => setWsStatus(false));
        echo.connector.pusher.connection.bind('failed', () => setWsStatus(false));
    } catch (e) { console.warn(e); }

    setInterval(refreshQueues, 15000);
});
