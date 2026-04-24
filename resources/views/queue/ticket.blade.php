@extends('layouts.app')

@section('title', 'Tiket {{ $queue->queue_number }} — Queu')

@push('styles')
<style>
    .ticket-wrapper {
        display: flex; align-items: center; justify-content: center;
        min-height: calc(100vh - 100px);
        padding: 2rem 0;
    }

    .ticket-card {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 24px;
        overflow: hidden;
        width: 100%;
        max-width: 460px;
        box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    }

    .ticket-header {
        padding: 2rem;
        text-align: center;
        background: linear-gradient(135deg, {{ $queue->service->color }}25, transparent);
        border-bottom: 1px solid var(--border);
    }

    .ticket-emoji { font-size: 2.5rem; margin-bottom: 0.75rem; display: block; }

    .ticket-service-tag {
        display: inline-flex; align-items: center; gap: 0.4rem;
        background: {{ $queue->service->color }}20;
        border: 1px solid {{ $queue->service->color }}40;
        color: {{ $queue->service->color }};
        padding: 0.35rem 0.875rem;
        border-radius: 99px;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
    }

    .ticket-number {
        font-family: 'JetBrains Mono', monospace;
        font-size: 5.5rem;
        font-weight: 900;
        color: {{ $queue->service->color }};
        line-height: 1;
        text-shadow: 0 0 40px {{ $queue->service->color }}55;
        letter-spacing: -0.02em;
        margin-bottom: 0.4rem;
    }

    .ticket-num-label {
        font-size: 0.72rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.15em;
    }

    .ticket-dashes {
        padding: 0 1.5rem;
        display: flex; align-items: center;
    }

    .ticket-dashes span {
        flex: 1;
        border-top: 2px dashed var(--border);
        margin: 0 1rem;
    }

    .ticket-circle {
        width: 22px; height: 22px;
        background: var(--surface);
        border-radius: 50%;
        flex-shrink: 0;
    }

    .ticket-body { padding: 1.5rem 2rem; }

    .info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.65rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.875rem;
    }

    .info-row:last-child { border-bottom: none; }
    .info-label { color: var(--text-muted); font-size: 0.8rem; }
    .info-value { font-weight: 600; text-align: right; }

    .ticket-footer {
        background: var(--surface-3);
        border-top: 1px solid var(--border);
        padding: 1.5rem 2rem;
        text-align: center;
    }

    .waiting-num {
        font-family: 'JetBrains Mono', monospace;
        font-size: 3rem; font-weight: 900;
        color: var(--warning); line-height: 1;
    }

    .waiting-label { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem; }

    .ticket-btns { display: flex; gap: 0.75rem; margin-top: 1.25rem; }
    .ticket-btns .btn { flex: 1; justify-content: center; }

    .live-card {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        margin-top: 1.25rem;
        display: flex; align-items: center; gap: 1rem;
    }

    .live-card-icon {
        width: 44px; height: 44px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; flex-shrink: 0;
        background: rgba(99,102,241,0.1);
        transition: all 0.3s;
    }

    /* Calling overlay */
    .calling-overlay {
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.88);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center; justify-content: center;
        z-index: 9999;
    }

    .calling-popup {
        background: var(--surface-2);
        border: 2px solid var(--warning);
        border-radius: 24px;
        padding: 2.5rem 2rem;
        text-align: center;
        max-width: 380px;
        width: 90%;
        animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1);
        box-shadow: 0 0 50px rgba(245,158,11,0.3);
    }

    @keyframes popIn {
        0%   { transform: scale(0.6); opacity: 0; }
        100% { transform: scale(1);   opacity: 1; }
    }

    .calling-num-big {
        font-family: 'JetBrains Mono', monospace;
        font-size: 5rem; font-weight: 900;
        color: var(--warning);
        animation: pulse 0.8s infinite;
        text-shadow: 0 0 30px rgba(245,158,11,0.5);
    }
</style>
@endpush

@section('content')
<div class="ticket-wrapper">
    <div style="width: 100%; max-width: 460px;">
        <div class="ticket-card">
            <div class="ticket-header">
                <span class="ticket-emoji">🎓</span>
                <div>
                    <div class="ticket-service-tag">
                        {{ $queue->service->code }} — {{ $queue->service->name }}
                    </div>
                </div>
                <div class="ticket-number">{{ $queue->queue_number }}</div>
                <div class="ticket-num-label">Nomor Antrian Anda</div>
            </div>

            <div class="ticket-dashes">
                <div class="ticket-circle"></div>
                <span></span>
                <div class="ticket-circle"></div>
            </div>

            <div class="ticket-body">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-user-graduate"></i> Nama</span>
                    <span class="info-value">{{ $queue->patient_name }}</span>
                </div>
                @if($queue->phone)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-id-card"></i> NIM/HP</span>
                    <span class="info-value">{{ $queue->phone }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clipboard-list"></i> Keperluan</span>
                    <span class="info-value" style="max-width: 230px; text-align: right; font-size: 0.8rem;">{{ $queue->service->name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar-alt"></i> Tanggal</span>
                    <span class="info-value">{{ $queue->queue_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock"></i> Waktu Daftar</span>
                    <span class="info-value">{{ $queue->created_at->format('H:i:s') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-circle"></i> Status</span>
                    <span class="info-value">
                        <span class="badge badge-{{ $queue->status }}" id="status-badge">
                            {{ $queue->status_label }}
                        </span>
                    </span>
                </div>
            </div>

            <div class="ticket-dashes">
                <div class="ticket-circle"></div>
                <span></span>
                <div class="ticket-circle"></div>
            </div>

            <div class="ticket-footer">
                <div class="waiting-num" id="waiting-count">{{ $waitingBefore }}</div>
                <div class="waiting-label">antrian di depan Anda</div>
                <div class="ticket-btns">
                    <a href="{{ route('queue.register') }}" class="btn btn-ghost">
                        <i class="fas fa-plus"></i> Daftar Lagi
                    </a>
                    <a href="{{ route('monitor.display') }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-tv"></i> Lihat Display
                    </a>
                </div>
            </div>
        </div>

        <div class="live-card">
            <div class="live-card-icon" id="live-icon">⏳</div>
            <div>
                <div style="font-size: 0.9rem; font-weight: 600;" id="live-text">Menunggu giliran...</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">
                    Sistem akan memberitahu saat nomor Anda dipanggil
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calling Overlay -->
<div class="calling-overlay" id="calling-overlay">
    <div class="calling-popup">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📢</div>
        <div class="calling-num-big">{{ $queue->queue_number }}</div>
        <div style="font-size: 1.1rem; font-weight: 700; margin: 0.875rem 0 0.4rem;">Giliran Anda!</div>
        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;" id="call-room">
            Silakan ke ruangan yang ditunjuk
        </div>
        <button class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1rem;"
                onclick="document.getElementById('calling-overlay').style.display='none'">
            <i class="fas fa-check"></i> Baik, Saya Segera
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    const MY_NUMBER = '{{ $queue->queue_number }}';

    function updateWaiting() {
        fetch('/api/waiting').then(r => r.json()).then(list => {
            const myIdx = list.findIndex(q => q.queue_number === MY_NUMBER);
            const before = myIdx >= 0 ? list.slice(0, myIdx).filter(q => q.status === 'waiting').length : 0;
            document.getElementById('waiting-count').textContent = before;
        }).catch(() => {});
    }

    try {
        const echo = new window.Echo({
            broadcaster: 'reverb',
            key: '{{ env("VITE_REVERB_APP_KEY") }}',
            wsHost: '{{ env("VITE_REVERB_HOST", "localhost") }}',
            wsPort: {{ env("VITE_REVERB_PORT", 8080) }},
            forceTLS: false,
            enabledTransports: ['ws'],
        });

        echo.channel('queue-monitor').listen('.queue.updated', () => {
            setWsStatus(true);
            updateWaiting();
        });

        echo.channel('queue-display').listen('.queue.called', (e) => {
            setWsStatus(true);
            if (e.queue_number === MY_NUMBER) {
                document.getElementById('calling-overlay').style.display = 'flex';
                document.getElementById('call-room').textContent =
                    `Silakan menuju ${e.counter_name || 'ruangan dosen'}`;
                document.getElementById('status-badge').textContent = '📢 Dipanggil';
                document.getElementById('status-badge').className = 'badge badge-calling';
                document.getElementById('live-icon').textContent = '📢';
                document.getElementById('live-text').textContent = 'Anda dipanggil! Segera menuju ruangan.';
                if (navigator.vibrate) navigator.vibrate([300, 100, 300]);
                // Notif suara
                try {
                    const ctx = new AudioContext();
                    [523, 784, 1047].forEach((f, i) => {
                        const o = ctx.createOscillator(), g = ctx.createGain();
                        o.connect(g); g.connect(ctx.destination);
                        o.frequency.value = f; o.type = 'sine';
                        o.start(ctx.currentTime + i * 0.18);
                        o.stop(ctx.currentTime + i * 0.18 + 0.15);
                        g.gain.setValueAtTime(0.35, ctx.currentTime + i * 0.18);
                        g.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + i * 0.18 + 0.14);
                    });
                } catch(e) {}
            }
        });

        echo.connector.socket.on('connect',    () => setWsStatus(true));
        echo.connector.socket.on('disconnect', () => setWsStatus(false));
    } catch(err) { console.warn('WS:', err); }

    updateWaiting();
    setInterval(updateWaiting, 30000);
</script>
@endpush
