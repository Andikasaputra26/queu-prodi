/**
 * Queue Monitor & Display Core Logic
 */

// Configuration should be provided globally via window.QueueConfig
const Config = window.QueueConfig || {
    broadcaster: 'reverb',
    pusher: { key: '', cluster: '' },
    reverb: { key: '', host: 'localhost', port: 8080 }
};

// ──────────────────────────────
// State
// ──────────────────────────────
let qTab = '';
const LOG_MAX = 50;
let activityLog = [];
let lastCalledData = null;
let dtMonitorTable = null;
let dtTrackingTable = null;
let svcChart = null;

// ──────────────────────────────
// Clock
// ──────────────────────────────
function updateClock() {
    const el = document.getElementById('mon-clock') || document.getElementById('clock');
    if (el) el.textContent = new Date().toLocaleTimeString('id-ID');
    
    const dateEl = document.getElementById('date');
    if (dateEl) {
        dateEl.textContent = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
    }
}
if (document.getElementById('mon-clock') || document.getElementById('clock')) {
    setInterval(updateClock, 1000); 
    updateClock();
}

// ──────────────────────────────
// Number counter animation
// ──────────────────────────────
function setStatNum(id, newVal) {
    const el = document.getElementById(id);
    if (!el) return;
    const current = parseInt(el.textContent) || 0;
    if (current === newVal) return;
    el.textContent = newVal;
    el.classList.remove('bump');
    void el.offsetWidth;
    el.classList.add('bump');
    el.addEventListener('animationend', () => el.classList.remove('bump'), { once: true });
}

// ──────────────────────────────
// Update "calling highlight" (Monitor Page)
// ──────────────────────────────
function showCallingHighlight(data) {
    const highlightEl = document.getElementById('calling-highlight');
    if (!highlightEl) return;

    if (!data) {
        document.getElementById('ch-no-call').style.display = 'flex';
        document.getElementById('ch-has-call').style.display = 'none';
        highlightEl.classList.remove('has-call');
        return;
    }
    document.getElementById('ch-no-call').style.display = 'none';
    document.getElementById('ch-has-call').style.display = 'block';
    highlightEl.classList.add('has-call');

    const numEl = document.getElementById('ch-number');
    numEl.textContent = data.queue_number;
    numEl.classList.remove('pop');
    void numEl.offsetWidth;
    numEl.classList.add('pop');

    document.getElementById('ch-name').textContent    = data.patient_name || '—';
    document.getElementById('ch-time').textContent    = data.called_at || '—';

    const svcEl = document.getElementById('ch-service');
    const color = data.service_color || '#6366F1';
    svcEl.innerHTML = `<span style="display:inline-flex;align-items:center;gap:0.4rem;background:${color}20;color:${color};padding:0.2rem 0.7rem;border-radius:99px;font-size:0.78rem;font-weight:700">${data.service_name || ''}</span>`;

    const roomEl = document.getElementById('ch-room');
    roomEl.innerHTML = `<i class="fas fa-door-open" style="color:${color}"></i> <span style="color:${color}">${data.counter_name || '—'}</span>`;
}

// ──────────────────────────────
// Service breakdown (Chart.js)
// ──────────────────────────────
function renderBreakdown(services) {
    const el = document.getElementById('svc-chart');
    if (!el) return;

    let totalDone = 0;
    
    if (!services || !Object.keys(services).length) {
        if(svcChart) {
            svcChart.destroy();
            svcChart = null;
        }
        const emptyEl = document.getElementById('svc-chart-empty');
        if (emptyEl) emptyEl.style.display = 'flex';
        const doneTodayEl = document.getElementById('done-today');
        if (doneTodayEl) doneTodayEl.textContent = 0;
        return;
    }

    const emptyEl = document.getElementById('svc-chart-empty');
    if (emptyEl) emptyEl.style.display = 'none';

    let categories = [];
    let totalData = [];
    let bgColors = [];

    Object.entries(services).forEach(([code, s]) => {
        totalDone += s.done;
        categories.push(s.service_name.length > 20 ? s.service_name.substring(0, 20) + '...' : s.service_name);
        totalData.push(s.total);
        bgColors.push(s.color || '#3B82F6');
    });

    if (!svcChart) {
        svcChart = new Chart(el, {
            type: 'doughnut',
            data: {
                labels: categories,
                datasets: [
                    { 
                        label: 'Total Pengajuan', 
                        data: totalData, 
                        backgroundColor: bgColors,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%', // Menyesuaikan ukuran lubang donat
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { font: { family: "'Inter', sans-serif", size: 11 } }
                    },
                    tooltip: {
                        titleFont: { family: "'Inter', sans-serif" },
                        bodyFont: { family: "'Inter', sans-serif" }
                    }
                }
            }
        });
    } else {
        svcChart.data.labels = categories;
        svcChart.data.datasets[0].data = totalData;
        svcChart.data.datasets[0].backgroundColor = bgColors;
        svcChart.data.datasets[0].borderColor = '#ffffff';
        svcChart.update();
    }

    const doneTodayEl = document.getElementById('done-today');
    if (doneTodayEl) doneTodayEl.textContent = totalDone;
}

// ──────────────────────────────
// Stats
// ──────────────────────────────
function refreshStats() {
    let url = '/api/stats?';
    const dateEl = document.getElementById('chart-date');
    const catEl = document.getElementById('chart-category');
    
    if (dateEl && dateEl.value) {
        url += `date=${dateEl.value}&`;
    }
    if (catEl && catEl.value) {
        url += `service_id=${catEl.value}&`;
    }

    fetch(url).then(r => r.json()).then(data => {
        // Shared logic for both pages
        let total = 0, pending = 0, admin = 0, done = 0;

        if (data.services) {
            Object.values(data.services).forEach(s => {
                total   += s.total;
                pending += s.pending;
                admin   += s.admin;
                done    += s.done;
            });
        }

        // Monitor page elements
        setStatNum('st-total',   total);
        setStatNum('st-pending', pending);
        setStatNum('st-admin',   admin);
        setStatNum('st-done',    done);

        // Display page elements (if they exist)
        const fAdmin = document.getElementById('f-admin');
        const fDone  = document.getElementById('f-done');
        if (fAdmin) fAdmin.textContent = admin;
        if (fDone)  fDone.textContent  = done;

        renderBreakdown(data.services);
        showCallingHighlight(data.calling);
        
        const lastUpdateEl = document.getElementById('last-update');
        if (lastUpdateEl) lastUpdateEl.textContent = 'Update: ' + new Date().toLocaleTimeString('id-ID');
    }).catch(() => {});
}

// ──────────────────────────────
// Queue list (Monitor Admin)
// ──────────────────────────────
function switchTab(tab, btn) {
    qTab = tab;
    if (btn) {
        document.querySelectorAll('.t-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }
    refreshQueues();
}

function refreshQueues() {
    const tbody = document.getElementById('q-tbody');
    if (!tbody) return; // Only for monitor page

    let url = '/api/admin/today?';
    const dateEl = document.getElementById('chart-date');
    const catEl = document.getElementById('chart-category');
    
    if (dateEl && dateEl.value) {
        url += `date=${dateEl.value}&`;
    }
    if (catEl && catEl.value) {
        url += `service_id=${catEl.value}&`;
    }
    if (qTab) {
        url += `status=${qTab}&`;
    }

    fetch(url).then(r => r.json()).then(renderQueues).catch(() => {});
}

function renderQueues(list) {
    const countLbl = document.getElementById('q-count-lbl');
    if (countLbl) countLbl.textContent = `${list.length} antrian`;
    
    if (dtMonitorTable) {
        dtMonitorTable.clear();
        dtMonitorTable.rows.add(list);
        dtMonitorTable.draw();
    }
}

// ──────────────────────────────
// Tracking list (Display Public)
// ──────────────────────────────
function refreshTracking() {
    const tbody = document.getElementById('tracking-list');
    if (!tbody) return;

    const svcFilter = document.getElementById('filter-service');
    const dateFilter = document.getElementById('filter-date');
    const svcId = svcFilter ? svcFilter.value : '';
    const date = dateFilter ? dateFilter.value : '';
    
    let url = '/api/waiting?';
    if (svcId) url += `service_id=${svcId}&`;
    if (date)  url += `date=${date}&`;

    fetch(url).then(r => r.json()).then(list => {
        const empty = document.getElementById('empty-msg');
        
        if (list.length === 0) {
            if (dtTrackingTable) dtTrackingTable.clear().draw();
            if (empty) empty.style.display = 'flex';
            return;
        }
        
        if (empty) empty.style.display = 'none';
        
        if (dtTrackingTable) {
            dtTrackingTable.clear().rows.add(list).draw();
        } else {
            // Fallback if DataTable not initialized
            tbody.innerHTML = '';
            list.forEach(q => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${q.queue_number}</td>
                    <td style="font-weight: 700;">${q.patient_name}</td>
                    <td style="font-size: 0.9rem; color: var(--text-muted);">${q.service_name}</td>
                    <td><span class="badge badge-${q.status}">${q.status_label}</span></td>
                    <td>${q.created_at}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    });
}

// ──────────────────────────────
// Counters grid
// ──────────────────────────────
function refreshCounters() {
    const el = document.getElementById('cg-grid');
    if (!el) return;

    fetch('/api/counters').then(r => r.json()).then(list => {
        el.innerHTML = '';
        list.forEach(c => {
            const color = c.current_queue?.service_color || '#64748B';
            const div = document.createElement('div');
            div.className = 'cg-card' + (c.current_queue ? ' active' : '');
            div.innerHTML = `
                <div class="cg-label">${c.name}</div>
                ${c.current_queue
                    ? `<div class="cg-num" style="color:${color}">${c.current_queue.queue_number}</div>
                       <div class="cg-name">${c.current_queue.patient_name}</div>
                       <div class="cg-off"><span class="badge badge-${c.current_queue.status}" style="font-size:0.65rem">${c.current_queue.status_label}</span></div>`
                    : `<div class="cg-empty">Kosong</div>`
                }
                ${c.officer_name ? `<div class="cg-off" style="margin-top:0.4rem">${c.officer_name}</div>` : ''}
            `;
            el.appendChild(div);
        });
    });
}

// ──────────────────────────────
// Activity log
// ──────────────────────────────
const LOG_ICONS = {
    pending: { icon: '🟢', bg: 'rgba(16,185,129,0.15)', label: 'Pengajuan Baru' },
    admin:   { icon: '🔍', bg: 'rgba(245,158,11,0.15)', label: 'Verif Admin' },
    prodi:   { icon: '🏛️', bg: 'rgba(99,102,241,0.15)', label: 'Kirim Prodi' },
    ready:   { icon: '🎁', bg: 'rgba(16,185,129,0.2)',  label: 'Siap Ambil' },
    done:    { icon: '✅', bg: 'rgba(16,185,129,0.1)',  label: 'Selesai' },
    rejected:{ icon: '❌', bg: 'rgba(239,68,68,0.1)',   label: 'Ditolak' },
    reset:   { icon: '🔄', bg: 'rgba(99,102,241,0.1)', label: 'Reset' },
};

function addLog(action, queue) {
    const el = document.getElementById('activity-log');
    if (!el) return;

    const conf = LOG_ICONS[action] || LOG_ICONS.new;
    const time = new Date().toLocaleTimeString('id-ID');
    activityLog.unshift({ action, queue, time, conf });
    if (activityLog.length > LOG_MAX) activityLog.pop();
    renderLog();
}

function renderLog() {
    const el = document.getElementById('activity-log');
    if (!el) return;

    const empty = document.getElementById('log-empty');
    if (!activityLog.length) { if (empty) empty.style.display = 'block'; return; }
    if (empty) empty.style.display = 'none';
    el.innerHTML = '';
    activityLog.forEach(entry => {
        const div = document.createElement('div');
        div.className = 'log-item';
        const qText = entry.queue
            ? `<span class="log-num">${entry.queue.queue_number}</span> — ${entry.queue.patient_name || ''}`
            : '';
        div.innerHTML = `
            <div class="log-icon" style="background:${entry.conf.bg}">${entry.conf.icon}</div>
            <div class="log-text">
                <div><strong>${entry.conf.label}</strong> ${qText}</div>
                ${entry.queue?.counter_name ? `<div style="font-size:0.7rem;color:var(--text-muted)">${entry.queue.counter_name}</div>` : ''}
                <div class="log-time">${entry.time}</div>
            </div>
        `;
        el.appendChild(div);
    });
}

function clearLog() { activityLog = []; renderLog(); }

// ──────────────────────────────
// All refresh
// ──────────────────────────────
function refreshAll() {
    refreshStats();
    refreshQueues();
    refreshCounters();
    refreshTracking();
}

// ──────────────────────────────
// WebSocket Helper
// ──────────────────────────────
function setWsStatus(connected) {
    const dot = document.getElementById('ws-dot') || document.getElementById('ws-d');
    const text = document.getElementById('ws-text') || document.getElementById('ws-t');
    
    if (dot) {
        dot.className = connected ? (dot.id === 'ws-dot' ? 'ws-dot' : 'ws-d on') : (dot.id === 'ws-dot' ? 'ws-dot offline' : 'ws-d');
        if (dot.id === 'ws-dot') dot.style.background = connected ? 'var(--success)' : 'var(--danger)';
    }
    if (text) text.textContent = connected ? (text.id === 'ws-text' ? 'Live — Terhubung' : 'Live — Terhubung') : 'Terputus';
}

// ──────────────────────────────
// Init
// ──────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // initialize DataTables for monitor page if it exists
    const qTable = $('#monitor-q-table');
    if (qTable.length) {
        dtMonitorTable = qTable.DataTable({
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
            order: [[0, 'desc']], // Waktu desc
            columns: [
                { data: 'created_at', render: (data) => `<span style="color:var(--text-muted);font-size:0.75rem;font-family:'JetBrains Mono',monospace">${data}</span>` },
                { data: 'queue_number', render: (data, type, row) => `<span style="color:${row.service_color || '#6366F1'}; font-weight: 700; font-family: 'JetBrains Mono', monospace; font-size: 0.95rem;">${data}</span>` },
                { data: 'patient_name', render: (data) => `<span style="font-weight:600">${data}</span>` },
                { data: 'phone', render: (data) => `<span style="color:var(--text-muted);font-size:0.78rem;font-family:'JetBrains Mono',monospace">${data || '—'}</span>` },
                { data: 'service_name', render: (data, type, row) => `<span style="background:${row.service_color || '#6366F1'}18;color:${row.service_color || '#6366F1'};padding:0.2rem 0.6rem;border-radius:99px;font-size:0.72rem;font-weight:700;white-space:nowrap">${data}</span>` },
                { data: 'status', render: (data, type, row) => `<span class="badge badge-${data}">${row.status_label}</span>` }
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.status === 'calling') {
                    $(row).addClass('row-calling');
                }
            }
        });
    }

    // initialize DataTables for tracking display page if it exists
    const tTable = $('#tracking-table');
    if (tTable.length) {
        dtTrackingTable = tTable.DataTable({
            paging: false,
            searching: false,
            info: false,
            order: [], // Handled by server-side orderByRaw
            columns: [
                { data: 'queue_number', render: (data) => `<span style="color:var(--warning); font-weight:900; font-family:'JetBrains Mono',monospace; font-size:1.4rem;">${data}</span>` },
                { data: 'patient_name', render: (data) => `<span style="font-weight:700">${data}</span>` },
                { data: 'service_name', render: (data) => `<span style="font-size:0.95rem; color:var(--text-muted)">${data}</span>` },
                { data: 'status', render: (data, type, row) => `<span class="badge badge-${data}">${row.status_label}</span>` },
                { data: 'created_at', render: (data) => `<span style="font-size:0.9rem; color:var(--text-muted); font-family:'JetBrains Mono',monospace">${data}</span>` }
            ]
        });
    }

    refreshAll();

    try {
        if (!window.Echo) return;

        const isPusher = Config.broadcaster === 'pusher';
        if (isPusher && typeof Pusher !== 'undefined') {
            window.Pusher = Pusher;
        }

        let echoConfig = {
            broadcaster: isPusher ? 'pusher' : 'reverb',
            key: isPusher ? (Config.pusher ? Config.pusher.key : '') : (Config.reverb ? Config.reverb.key : Config.reverbKey),
            forceTLS: isPusher ? true : false,
            enabledTransports: ['ws', 'wss'],
        };

        if (isPusher) {
            echoConfig.cluster = Config.pusher ? Config.pusher.cluster : '';
        } else {
            echoConfig.wsHost = Config.reverb ? Config.reverb.host : Config.reverbHost;
            echoConfig.wsPort = Config.reverb ? Config.reverb.port : Config.reverbPort;
        }

        const echo = new window.Echo(echoConfig);

        echo.channel('queue-monitor').listen('.queue.updated', (e) => {
            setWsStatus(true);
            refreshAll();
            addLog(e.action, e.queue);

            // Toast notifications (if showToast is available)
            if (typeof showToast === 'function') {
                const msgs = {
                    new:     () => `Mahasiswa baru: ${e.queue?.queue_number} — ${e.queue?.patient_name}`,
                    called:  () => `Memanggil: ${e.queue?.queue_number} → ${e.queue?.counter_name}`,
                    serving: () => `Melayani: ${e.queue?.queue_number}`,
                    done:    () => `Selesai: ${e.queue?.queue_number}`,
                    skip:    () => `Dilewati: ${e.queue?.queue_number}`,
                    reset:   () => `Antrian hari ini direset`,
                };
                const toastType = { new:'info', called:'warning', serving:'info', done:'success', skip:'error', reset:'error' };
                if (msgs[e.action]) showToast(msgs[e.action](), toastType[e.action] || 'info', 4000);
            }
        });

        echo.channel('queue-display').listen('.queue.called', (e) => {
            refreshAll();
            // Handle highlight if on monitor page
            if (typeof showUpdateHighlight === 'function') {
                showUpdateHighlight({ ...e, called_at: new Date().toLocaleTimeString('id-ID') });
            }
        });

        // Reverb/Pusher connection events
        if (echo.connector.pusher && echo.connector.pusher.connection) {
            echo.connector.pusher.connection.bind('connected', () => setWsStatus(true));
            echo.connector.pusher.connection.bind('disconnected', () => setWsStatus(false));
            echo.connector.pusher.connection.bind('unavailable', () => setWsStatus(false));
            echo.connector.pusher.connection.bind('failed', () => setWsStatus(false));
        } else if (echo.connector.socket) {
            echo.connector.socket.on('connect', () => setWsStatus(true));
            echo.connector.socket.on('disconnect', () => setWsStatus(false));
        }

    } catch(err) { console.warn('WS:', err); }

    // Auto-refresh fallback (every 20s)
    setInterval(refreshAll, 20000);
});
