@extends('layouts.app')

@section('title', 'Tiket ' . $queue->queue_number . ' — SI-PINTER')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/queue-ticket.css') }}">
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
                    <span class="info-value">{{ $queue->created_at->format('H:i:s') }} WITA</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-circle"></i> Status</span>
                    <span class="info-value">
                        <span class="badge badge-{{ $queue->status }}" id="status-badge">
                            {{ $queue->status_label }}
                        </span>
                    </span>
                </div>
                @if($queue->status === 'rejected' && $queue->reject_reason)
                <div class="info-row" style="flex-direction: column; align-items: flex-start; gap: 0.5rem; background: rgba(239,68,68,0.05); padding: 1rem; border-radius: 8px; margin-top: 1rem; border: 1px solid rgba(239,68,68,0.2);">
                    <span class="info-label" style="color: var(--danger); font-weight: 700;"><i class="fas fa-exclamation-triangle"></i> Alasan Penolakan</span>
                    <span style="font-size: 0.85rem; color: var(--text);">{{ $queue->reject_reason }}</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">* Harap perbaiki dokumen Anda lalu temui admin untuk diantrikan ulang.</span>
                </div>
                @endif

                @if(($queue->status === 'done' || $queue->status === 'picked_up') && $queue->file_path)
                <div class="download-section animate__animated animate__fadeInUp" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.12)); padding: 1.25rem; border-radius: 12px; margin-top: 1.25rem; border: 1px solid rgba(16, 185, 129, 0.2); text-align: center; width: 100%; box-sizing: border-box;">
                    <div style="font-size: 1.8rem; margin-bottom: 0.5rem;">📄</div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.25rem; color: #065F46;">Dokumen Selesai & Terstempel</h4>
                    <p style="font-size: 0.75rem; color: #047857; margin-bottom: 1rem; line-height: 1.4;">
                        Silakan unduh berkas yang telah ditandatangani dan distempel resmi di bawah ini.
                    </p>
                    <a href="{{ route('queue.download', $queue->id) }}" onclick="setTimeout(() => window.location.reload(), 1500)" class="btn btn-success" style="width: 100%; justify-content: center; font-size: 0.9rem; padding: 0.6rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15); text-decoration: none; font-weight: 600; box-sizing: border-box;">
                        <i class="fas fa-download"></i> Unduh Dokumen
                    </a>
                </div>
                @endif
            </div>

            <div class="ticket-dashes">
                <div class="ticket-circle"></div>
                <span></span>
                <div class="ticket-circle"></div>
            </div>

            <div class="ticket-footer">
                <div class="waiting-card">
                    <div class="waiting-num" id="waiting-count">{{ $waitingBefore }}</div>
                    <div class="waiting-label">antrian di depan Anda</div>
                </div>
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

<div class="calling-overlay" id="calling-overlay">
    <div class="calling-popup">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📢</div>
        <div class="calling-num-big">{{ $queue->queue_number }}</div>
        <div style="font-size: 1.1rem; font-weight: 700; margin: 0.875rem 0 0.4rem;">Giliran Anda!</div>
        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;" id="call-room">
            Silahkan ke ruangan yang ditunjuk
        </div>
        <button class="btn btn-primary" style="width: 100%; justify-content: center; font-size: 1rem;"
                onclick="document.getElementById('calling-overlay').style.display='none'">
            <i class="fas fa-check"></i> Baik, Saya Segera
        </button>
    </div>
</div>

<div class="calling-overlay" id="done-overlay">
    <div class="calling-popup" style="border-color: var(--success); box-shadow: 0 0 50px rgba(16,185,129,0.3);">
        <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">✅</div>
        <div style="font-size: 1.25rem; font-weight: 700; margin: 0.875rem 0 0.4rem; color: var(--success);">Surat Selesai!</div>
        <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1.5rem;">
            Surat Anda sudah dapat diambil. Mohon kesediaannya untuk mengisi survei kepuasan layanan kami.
        </div>
        <a id="survey-btn" href="#" target="_blank" class="btn btn-success" style="width: 100%; justify-content: center; font-size: 1rem; margin-bottom: 0.5rem;">
            <i class="fas fa-star"></i> Isi Survei Kepuasan
        </a>
        <button class="btn btn-ghost" style="width: 100%; justify-content: center;"
                onclick="window.location.reload()">
            Tutup & Lihat Berkas
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
window.TicketConfig = {
    myNumber: '{{ $queue->queue_number }}',
    surveyLink: '{{ env("SURVEY_LINK", "https://docs.google.com/forms/d/e/1FAIpQLSeSnOxJIgkuIeVblX9QDYnJcLvqt3NjbK2VYKg9Oa95_EIKWg/viewform?usp=publish-editor") }}',
    reverb: {
        key: '{{ env("VITE_REVERB_APP_KEY") }}',
        host: '{{ env("VITE_REVERB_HOST", "localhost") }}',
        port: {{ env("VITE_REVERB_PORT", 8080) }}
    }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
<script>
    @if(session('queue_success'))
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Pengajuan Berhasil!',
                html: 'Tunggu nomor antrian Anda dipanggil.<br>Nomor Anda: <strong>{{ $queue->queue_number }}</strong>',
                confirmButtonText: 'Baik, Mengerti',
                confirmButtonColor: '#10B981',
                timer: 5000,
                timerProgressBar: true,
                customClass: {
                    popup: 'animate__animated animate__zoomIn'
                }
            });
        });
    @endif
</script>
<script src="{{ asset('js/queue-ticket.js') }}"></script>
@endpush
