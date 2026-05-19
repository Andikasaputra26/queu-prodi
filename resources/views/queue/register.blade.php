@extends('layouts.app')

@section('title', 'Pengajuan Surat Mahasiswa — SI-PINTER')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="{{ asset('css/queue-register.css') }}">
@endpush

@section('content')
    <div class="hero-section">
        <div class="hero-badge">
            <span class="pulse">🟢</span> Administrasi Jurusan — {{ now()->format('l, d F Y') }}
        </div>
        <h1 class="hero-title">Layanan Administrasi<br>Kemahasiswaan</h1>
        <p class="hero-sub">Layanan Administrasi Kemahasiswaan
            Jurusan Ilmu Administrasi
            Fakultas Ilmu Sosial dan Hukum
            Universitas Negeri Makassar.</p>
    </div>

    <div class="register-grid">
        <div>
            <div class="section-label" style="display: flex; justify-content: space-between; align-items: center;">
                <span><i class="fas fa-file-alt"></i> Pilih Jenis Surat</span>
                <span class="badge" style="background: var(--primary); color: white; padding: 0.2rem 0.6rem; font-size: 0.65rem;">{{ count($services) }} Layanan</span>
            </div>
            
            <div class="service-cards-wrapper">
                <div class="search-service">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-service-input" placeholder="Cari jenis surat yang Anda butuhkan..." autocomplete="off">
                </div>
                
                <div class="service-cards-container">
                    <div class="service-cards" id="service-cards">
                        @foreach($services as $service)
                            <div class="service-card {{ !$service->is_active ? 'disabled' : '' }}"
                                style="--svc-color: {{ $service->color }}; {{ !$service->is_active ? 'opacity: 0.6; cursor: not-allowed;' : '' }}"
                                data-id="{{ $service->id }}" data-name="{{ strtolower($service->name) }}" data-code="{{ $service->code }}"
                                data-desc="{{ $service->description }}" data-color="{{ $service->color }}"
                                data-active="{{ $service->is_active ? '1' : '0' }}"
                                onclick="{{ $service->is_active ? 'selectService(this)' : '' }}">
                                <div class="svc-code" style="background: {{ $service->color }}20; color: {{ $service->color }}">
                                    {{ $service->code }}
                                </div>
                                <div class="svc-name" style="text-transform: capitalize;">{{ $service->name }} {{ !$service->is_active ? '(Tidak Aktif)' : '' }}</div>
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
                    <div id="no-service-found" style="display: none; text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <p>Jenis surat tidak ditemukan.</p>
                    </div>
                </div>
            </div>

            <!-- <div class="section-label"><i class="fas fa-chart-line"></i> Statistik Hari Ini</div>
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
            </div> -->
        </div>

        <div>
            <div class="card" id="form-card" style="position: sticky; top: 80px;">
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
                                Upload File (Word) <span style="color: var(--danger)">*</span>
                            </label>
                            <div class="input-icon">
                                <i class="fas fa-file-upload" style="color: var(--primary);"></i>
                                <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx">
                            </div>
                            <small
                                style="color: var(--text-muted); font-size: 0.75rem; margin-top: 0.4rem; display: block;">
                                Wajib upload file dalam format <strong>Word</strong>. (Sisipkan teks <strong>${nmr}</strong> agar nomor terisi otomatis).
                            </small>
                            <a href="{{ asset('template/FormatSurat.docx') }}" target="_blank" class="btn-template">
                                <i class="fas fa-file-word"></i>Download Template Surat
                            </a>
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
        <script>
            window.QueueConfig = {
                reverb: {
                    key: '{{ env("VITE_REVERB_APP_KEY") }}',
                    host: '{{ env("VITE_REVERB_HOST", "localhost") }}',
                    port: {{ env("VITE_REVERB_PORT", 8080) }}
                }
            };

        <script src="{{ asset('js/queue-register-init.js') }}"></script>
        <script src="{{ asset('js/queue-register.js') }}"></script>
    @endpush