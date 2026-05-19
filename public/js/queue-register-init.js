document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
            title: '<span style="font-size: clamp(1.5rem, 5vw, 2rem); font-weight: 900; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Selamat Datang! 🎓</span>',
            html: `
                <p style="color: var(--text-muted); font-size: clamp(0.85rem, 3vw, 0.95rem); margin-bottom: 1.25rem; line-height: 1.6;">
                    Anda berada di portal <strong>SI-PINTER</strong>.<br>Sistem Pelayanan Persuratan Terintegrasi Jurusan Ilmu Administrasi.
                </p>
                <div style="text-align: left; background: var(--surface); padding: clamp(1rem, 4vw, 1.5rem); border-radius: 16px; border: 1px solid rgba(99, 102, 241, 0.2); box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                    <h4 style="font-size: clamp(0.85rem, 3vw, 0.9rem); font-weight: 800; margin-bottom: 1rem; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em;"><i class="fas fa-magic" style="margin-right: 0.4rem;"></i> Cara Cepat Pengajuan</h4>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.8rem;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; flex-shrink: 0;">1</div>
                        <span style="font-size: clamp(0.8rem, 2.5vw, 0.9rem); color: var(--text); line-height: 1.4; margin-top: 0.15rem;">Gunakan kotak <strong>Pencarian</strong> untuk menemukan Jenis Surat.</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.8rem;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; flex-shrink: 0;">2</div>
                        <span style="font-size: clamp(0.8rem, 2.5vw, 0.9rem); color: var(--text); line-height: 1.4; margin-top: 0.15rem;">Isi form dengan lengkap, sertakan file format Word jika diminta.</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(99, 102, 241, 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800; flex-shrink: 0;">3</div>
                        <span style="font-size: clamp(0.8rem, 2.5vw, 0.9rem); color: var(--text); line-height: 1.4; margin-top: 0.15rem;">Dapatkan Notifikasi WA dan <strong>Lacak Progres</strong> secara real-time.</span>
                    </div>
                </div>
            `,
            backdrop: `rgba(15, 23, 42, 0.85)`,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-rocket" style="margin-right: 0.5rem;"></i> Mulai Pengajuan',
            confirmButtonColor: '#1D4ED8',
            background: '#ffffff',
            padding: window.innerWidth < 600 ? '1.5em' : '2.5em',
            width: window.innerWidth < 600 ? '92%' : '550px',
            customClass: {
                popup: 'animate__animated animate__zoomIn',
                confirmButton: 'btn btn-primary',
            },
            didOpen: () => {
                const popup = Swal.getPopup();
                popup.classList.remove('swal2-show');
                popup.style.borderRadius = '24px';
                popup.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.25)';
            },
            willClose: () => {
                const popup = Swal.getPopup();
                popup.classList.remove('animate__zoomIn');
                popup.classList.add('animate__zoomOut');
            }
        }).then(() => {
            // Auto focus to search input after closing modal
            setTimeout(() => {
                const searchInput = document.getElementById('search-service-input');
                if (searchInput) searchInput.focus();
            }, 500);
        });

    // Search/Filter Service Cards
    const searchInput = document.getElementById('search-service-input');
    const serviceCards = document.querySelectorAll('.service-card');
    const noServiceFound = document.getElementById('no-service-found');

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let visibleCount = 0;

            serviceCards.forEach(card => {
                const name = card.getAttribute('data-name');
                const code = card.getAttribute('data-code').toLowerCase();
                
                if (name.includes(searchTerm) || code.includes(searchTerm)) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (visibleCount === 0) {
                noServiceFound.style.display = 'block';
            } else {
                noServiceFound.style.display = 'none';
            }
        });
    }
});
