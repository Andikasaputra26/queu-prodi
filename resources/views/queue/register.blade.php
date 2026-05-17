@extends('layouts.app')

@section('title', 'Pengajuan Surat Mahasiswa — SI-PINTER')

@push('styles')
    <style>
        .hero-section {
            text-align: center;
            padding: 2.5rem 1rem 2rem;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            color: #A5B4FC;
            padding: 0.4rem 1rem;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1.25rem;
        }

        .hero-title {
            font-size: clamp(1.8rem, 4.5vw, 3rem);
            font-weight: 900;
            line-height: 1.15;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.75rem;
        }

        .hero-sub {
            font-size: 1rem;
            color: var(--text-muted);
            max-width: 520px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }

        .register-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.75rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        @media (max-width: 800px) {
            .register-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .service-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.7rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 500px) {
            .service-cards {
                grid-template-columns: 1fr;
            }
        }

        .service-card {
            position: relative;
            background: var(--surface-2);
            border: 2px solid var(--border);
            border-radius: 14px;
            padding: 1.1rem;
            cursor: pointer;
            transition: all 0.22s;
            overflow: hidden;
        }

        .service-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--svc-color, var(--primary));
            border-radius: 14px 14px 0 0;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .service-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.35);
        }

        .service-card.selected {
            border-color: var(--svc-color, var(--primary)) !important;
            background: color-mix(in srgb, var(--svc-color, var(--primary)) 8%, var(--surface-2));
        }

        .service-card.selected::after {
            opacity: 1;
        }

        .svc-code {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 900;
            margin-bottom: 0.65rem;
            font-family: 'JetBrains Mono', monospace;
            flex-shrink: 0;
        }

        .svc-name {
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 0.2rem;
            line-height: 1.3;
        }

        .svc-desc {
            font-size: 0.72rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .svc-meta {
            position: absolute;
            top: 0.65rem;
            right: 0.65rem;
            display: flex;
            gap: 0.3rem;
            align-items: center;
        }

        .svc-count {
            font-size: 0.68rem;
            color: var(--text-muted);
            background: var(--surface-3);
            padding: 0.15rem 0.5rem;
            border-radius: 99px;
        }

        .svc-check {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .service-card.selected .svc-count {
            display: none;
        }

        .service-card.selected .svc-check {
            display: flex;
            background: var(--svc-color, var(--primary));
            color: white;
        }

        /* ---- Stats ---- */
        .live-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.7rem;
            margin-top: 0;
        }

        .stat-card {
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }

        .stat-number {
            font-size: 1.9rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 0.2rem;
        }

        /* ---- Form ---- */
        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .input-icon .form-control {
            padding-left: 2.75rem;
        }

        .selected-service-preview {
            background: var(--surface-3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1.25rem;
            display: none;
            align-items: center;
            gap: 0.875rem;
        }

        .selected-service-preview.show {
            display: flex;
        }

        .preview-code {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            flex-shrink: 0;
        }

        .preview-name {
            font-size: 0.9rem;
            font-weight: 700;
        }

        .preview-desc {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        /* Info box */
        .info-box {
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 10px;
            padding: 0.875rem 1rem;
            font-size: 0.8rem;
            color: #A5B4FC;
            margin-top: 1rem;
            line-height: 1.6;
        }

        .info-box i {
            margin-right: 0.3rem;
        }
    </style>
@endpush

@section('content')
    <div class="hero-section">
        <div class="hero-badge">
            <span class="pulse">🟢</span> Administrasi Prodi — {{ now()->format('l, d F Y') }}
        </div>
        <h1 class="hero-title">Layanan Administrasi<br>Kemahasiswaan</h1>
        <p class="hero-sub">Layanan Administrasi Kemahasiswaan
            Jurusan Ilmu Administrasi
            Fakultas Ilmu Sosial dan Hukum
            Universitas Negeri Makassar.</p>
    </div>

    <div class="register-grid">
        <div>
            <div class="section-label"><i class="fas fa-file-alt"></i> Pilih Jenis Surat</div>
            <div class="service-cards" id="service-cards">
                @foreach($services as $service)
                    <div class="service-card {{ !$service->is_active ? 'disabled' : '' }}"
                        style="--svc-color: {{ $service->color }}; {{ !$service->is_active ? 'opacity: 0.6; cursor: not-allowed;' : '' }}"
                        data-id="{{ $service->id }}" data-name="{{ $service->name }}" data-code="{{ $service->code }}"
                        data-desc="{{ $service->description }}" data-color="{{ $service->color }}"
                        data-active="{{ $service->is_active ? '1' : '0' }}"
                        onclick="{{ $service->is_active ? 'selectService(this)' : '' }}">
                        <div class="svc-code" style="background: {{ $service->color }}20; color: {{ $service->color }}">
                            {{ $service->code }}
                        </div>
                        <div class="svc-name">{{ $service->name }} {{ !$service->is_active ? '(Tidak Aktif)' : '' }}</div>
                        <div class="svc-desc">{{ $service->description }}</div>
                        <div class="svc-meta">
                            @if($service->is_active)
                                <span class="svc-count">Diproses: —</span>
                                <div class="svc-check">✓</div>
                            @else
                                <span class="svc-count" style="background: var(--surface-4);">Tutup</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="section-label"><i class="fas fa-chart-line"></i> Statistik Hari Ini</div>
            <div class="live-stats">
                <div class="stat-card">
                    <div class="stat-number" id="stat-total">0</div>
                    <div class="stat-label">Total Pengajuan</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="stat-prodi">0</div>
                    <div class="stat-label">Di Prodi</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="stat-ready">0</div>
                    <div class="stat-label">Siap Ambil</div>
                </div>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Setelah mengambil nomor, Anda akan mendapat <strong>tiket digital</strong>.
                Sistem akan memberitahu otomatis saat nomor Anda dipanggil.
            </div>
        </div>

        <div>
            <div class="card" style="position: sticky; top: 80px;">
                <div class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-id-badge" style="color: var(--primary)"></i>
                        Data Mahasiswa
                    </h2>
                </div>

                <div class="selected-service-preview" id="service-preview">
                    <div class="preview-code" id="preview-code"></div>
                    <div>
                        <div class="preview-name" id="preview-name"></div>
                        <div class="preview-desc" id="preview-desc"></div>
                    </div>
                </div>

                <form action="{{ route('queue.store') }}" method="POST" id="register-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="service_id" id="service_id">

                    <div id="form-inputs" style="display: none;">
                        <div class="form-group">
                            <label class="form-label" for="patient_name">
                                Nama Lengkap <span style="color: var(--danger)">*</span>
                            </label>
                            <div class="input-icon">
                                <i class="fas fa-user-graduate"></i>
                                <input type="text" class="form-control" id="patient_name" name="patient_name"
                                    placeholder="Masukkan nama lengkap" value="{{ old('patient_name') }}" required>
                            </div>
                            @error('patient_name')
                                <small style="color: var(--danger); margin-top: 0.3rem; display: block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">No.HP/WA</label>
                            <div class="input-icon">
                                <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    placeholder="Contoh: 08123456789 (Menerima Notifikasi WA)" value="{{ old('phone') }}"
                                    required>
                            </div>
                            @error('phone')
                                <small style="color: var(--danger); margin-top: 0.3rem; display: block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group" id="file-upload-group" style="display: none;">
                            <label class="form-label" for="file">
                                Upload File (PDF/Word) <span style="color: var(--danger)">*</span>
                            </label>
                            <div class="input-icon">
                                <i class="fas fa-file-upload" style="color: var(--primary);"></i>
                                <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx">
                            </div>
                            <small
                                style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.4rem; display: block;">
                                Wajib upload file proposal/hasil/tutup dalam format <strong>PDF atau Word</strong>. (Jika Word, sisipkan teks <strong>${NOMOR_SURAT}</strong> agar nomor terisi otomatis).
                            </small>
                            @error('file')
                                <small style="color: var(--danger); margin-top: 0.3rem; display: block">{{ $message }}</small>
                            @enderror
                        </div>

                        @error('service_id')
                            <div
                                style="color: var(--danger); font-size: 0.85rem; margin-bottom: 1rem; padding: 0.75rem; background: rgba(239,68,68,0.08); border-radius: 8px; border: 1px solid rgba(239,68,68,0.25);">
                                <i class="fas fa-exclamation-triangle"></i> {{ $message }}
                            </div>
                        @enderror

                        <button type="submit" class="btn btn-primary" id="submit-btn"
                            style="width: 100%; justify-content: center; padding: 0.9rem; font-size: 1rem; margin-top: 0.25rem;"
                            disabled>
                            <i class="fas fa-paper-plane"></i> Kirim Pengajuan Surat
                        </button>
                    </div>

                    <div id="hint-text" style="text-align: center; padding: 2.5rem 1rem;">
                        <i class="fas fa-hand-pointer"
                            style="font-size: 2.5rem; color: var(--border); margin-bottom: 1rem; display: block;"></i>
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 0;">Silakan pilih
                            <strong>Jenis Surat</strong> di sebelah kiri terlebih dahulu untuk mulai mengisi form.
                        </p>
                    </div>
                </form>
                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header">
                        <h2 class="card-title">
                            <i class="fas fa-search" style="color: var(--warning)"></i>
                            Lacak Antrian Saya
                        </h2>
                    </div>
                    <form action="{{ route('queue.search') }}" method="POST" style="padding: 1.25rem;">
                        @csrf
                        <div class="form-group" style="margin-bottom: 0.875rem;">
                            <label class="form-label" style="font-size: 0.75rem;">Masukkan Nomor Antrian / ID Surat</label>
                            <div class="input-icon">
                                <i class="fas fa-ticket-alt"></i>
                                <input type="text" name="queue_number" class="form-control" placeholder="Contoh: A001"
                                    style="text-transform: uppercase;">
                            </div>
                            @error('queue_number')
                                <small style="color: var(--danger); margin-top: 0.3rem; display: block">{{ $message }}</small>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-ghost"
                            style="width: 100%; justify-content: center; border-color: var(--warning); color: var(--warning);">
                            Lihat Progres Surat
                        </button>
                    </form>
                </div>

                <div class="card" style="margin-top: 1.5rem;">
                    <div class="card-header" style="margin-bottom: 1rem;">
                        <h2 class="card-title">
                            <i class="fas fa-list-ul" style="color: var(--success)"></i>
                            Monitoring Antrian
                        </h2>
                    </div>

                    <div style="overflow-x: auto; max-height: 400px; overflow-y: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                            <thead style="position: sticky; top: 0; background: var(--surface-2); z-index: 1;">
                                <tr>
                                    <th
                                        style="padding: 0.75rem; text-align: left; color: var(--text-muted); border-bottom: 1px solid var(--border);">
                                        No. Surat</th>
                                    <th
                                        style="padding: 0.75rem; text-align: left; color: var(--text-muted); border-bottom: 1px solid var(--border);">
                                        Nama / Jenis</th>
                                    <th
                                        style="padding: 0.75rem; text-align: left; color: var(--text-muted); border-bottom: 1px solid var(--border);">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody id="public-tracking-list">
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 1.5rem; color: var(--text-muted);">
                                        Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
@endsection

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.3.0/dist/web/pusher.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
        <script>
            window.QueueConfig = {
                reverb: {
                    key: '{{ env("VITE_REVERB_APP_KEY") }}',
                    host: '{{ env("VITE_REVERB_HOST", "localhost") }}',
                    port: {{ env("VITE_REVERB_PORT", 8080) }}
                }
            };
        </script>
        <script src="{{ asset('js/queue-register.js') }}"></script>
    @endpush