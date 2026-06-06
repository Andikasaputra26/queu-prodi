const Config = window.TicketConfig || {
    myNumber: '',
    broadcaster: 'reverb',
    pusher: { key: '', cluster: '' },
    reverb: { key: '', host: 'localhost', port: 8080 }
};

function updateWaiting() {
    fetch('/api/waiting').then(r => r.json()).then(list => {
        const myIdx = list.findIndex(q => q.queue_number === Config.myNumber);
        if (myIdx >= 0) {
            const myQueue = list[myIdx];
            const before = list.slice(0, myIdx).filter(q => q.service_name === myQueue.service_name && (q.status === 'pending' || q.status === 'admin')).length;
            const countEl = document.getElementById('waiting-count');
            if (countEl) countEl.textContent = before;
        }
    }).catch(() => { });
}

document.addEventListener('DOMContentLoaded', () => {
    updateWaiting();
    setInterval(updateWaiting, 30000);

    try {
        if (!window.Echo) return;

        const appConfig = window.AppConfig || {};
        const pageConfig = window.TicketConfig || {};
        
        const broadcaster = pageConfig.broadcaster || appConfig.broadcastConnection || 'reverb';
        const isPusher = broadcaster === 'pusher';
        
        if (isPusher && typeof Pusher !== 'undefined') {
            window.Pusher = Pusher;
        }

        const echoConfig = {
            broadcaster: broadcaster,
            key: isPusher ? (pageConfig.pusher?.key || appConfig.pusherAppKey) : (pageConfig.reverb?.key || appConfig.reverbAppKey),
            cluster: isPusher ? (pageConfig.pusher?.cluster || appConfig.pusherCluster) : undefined,
            wsHost: isPusher ? undefined : (pageConfig.reverb?.host || appConfig.reverbHost || window.location.hostname),
            wsPort: isPusher ? undefined : (parseInt(pageConfig.reverb?.port || appConfig.reverbPort) || 8080),
            forceTLS: isPusher ? true : false,
            enabledTransports: ['ws', 'wss'],
        };

        const echo = new window.Echo(echoConfig);

        echo.channel('queue-monitor').listen('.queue.updated', (e) => {
            if (typeof setWsStatus === 'function') setWsStatus(true);
            updateWaiting();

            if (e.action === 'done' && e.queue && e.queue.queue_number === Config.myNumber) {
                const doneOverlay = document.getElementById('done-overlay');
                if (doneOverlay) {
                    doneOverlay.style.display = 'flex';
                    const surveyBtn = document.getElementById('survey-btn');
                    if (surveyBtn) surveyBtn.href = Config.surveyLink;
                }

                const badgeEl = document.getElementById('status-badge');
                if (badgeEl) {
                    badgeEl.textContent = 'Selesai';
                    badgeEl.className = 'badge badge-done';
                }

                const liveIcon = document.getElementById('live-icon');
                if (liveIcon) liveIcon.textContent = '✅';

                const liveText = document.getElementById('live-text');
                if (liveText) liveText.textContent = 'Pelayanan selesai. Terima kasih!';

                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
            }
        });

        echo.channel('queue-display').listen('.queue.called', (e) => {
            if (typeof setWsStatus === 'function') setWsStatus(true);
            if (e.queue_number === Config.myNumber) {
                const overlay = document.getElementById('calling-overlay');
                if (overlay) overlay.style.display = 'flex';

                const roomEl = document.getElementById('call-room');
                if (roomEl) roomEl.textContent = `Silakan menuju ${e.counter_name || 'ruangan dosen'}`;

                const badgeEl = document.getElementById('status-badge');
                if (badgeEl) {
                    badgeEl.textContent = '📢 Dipanggil';
                    badgeEl.className = 'badge badge-calling';
                }

                const liveIcon = document.getElementById('live-icon');
                if (liveIcon) liveIcon.textContent = '📢';

                const liveText = document.getElementById('live-text');
                if (liveText) liveText.textContent = 'Anda dipanggil! Segera menuju ruangan.';

                if (navigator.vibrate) navigator.vibrate([300, 100, 300]);

                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    [523, 784, 1047].forEach((f, i) => {
                        const o = ctx.createOscillator(), g = ctx.createGain();
                        o.connect(g); g.connect(ctx.destination);
                        o.frequency.value = f; o.type = 'sine';
                        o.start(ctx.currentTime + i * 0.18);
                        o.stop(ctx.currentTime + i * 0.18 + 0.15);
                        g.gain.setValueAtTime(0.35, ctx.currentTime + i * 0.18);
                        g.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.18 + 0.14);
                    });
                } catch (err) { }
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
    } catch(err) { console.warn('WS:', err); }
});
