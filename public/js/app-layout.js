function showToast(message, type = 'info', duration = 4000) {
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 (Swal) is not loaded.');
        return;
    }
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: duration,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });
    Toast.fire({
        icon: type,
        title: message
    });
}

function setWsStatus(connected) {
    const dot = document.getElementById('ws-dot');
    const text = document.getElementById('ws-text');
    if (!dot) return;
    if (connected) {
        dot.className = 'ws-dot connected';
        text.textContent = 'Live';
    } else {
        dot.className = 'ws-dot error';
        text.textContent = 'Offline';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const config = window.AppConfig || {};

    // Show Session Toasts
    if (config.sessionSuccess) {
        showToast(config.sessionSuccess, 'success');
    }
    if (config.sessionError) {
        showToast(config.sessionError, 'error');
    }

    // Navbar Navigation Toggler
    const toggleBtn = document.getElementById('nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            const icon = toggleBtn.querySelector('i');
            if (icon) {
                if (navLinks.classList.contains('active')) {
                    icon.className = 'fas fa-times';
                } else {
                    icon.className = 'fas fa-bars';
                }
            }
        });
    }

    if (config.isAdminRoute && typeof window.Echo !== 'undefined') {
        const isPusher = config.broadcastConnection === 'pusher';

        let echoConfig = {
            broadcaster: isPusher ? 'pusher' : 'reverb',
            key: isPusher ? config.pusherAppKey : config.reverbAppKey,
            forceTLS: isPusher ? true : false,
            enabledTransports: ['ws', 'wss'],
        };

        if (isPusher) {
            echoConfig.cluster = config.pusherCluster;
        } else {
            echoConfig.wsHost = config.reverbHost;
            echoConfig.wsPort = parseInt(config.reverbPort) || 8080;
        }

        const adminEcho = new window.Echo(echoConfig);

        adminEcho.channel('admin-chat').listen('.chat.message', (e) => {
            if (e.message.sender === 'student' && !window.location.pathname.includes('/admin/chat')) {
                try {
                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    audio.volume = 0.5;
                    audio.play();
                } catch (err) { }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: `Pesan Baru: ${e.session.student_name}`,
                    text: e.message.message.length > 50 ? e.message.message.substring(0, 50) + '...' : e.message.message,
                    showConfirmButton: true,
                    confirmButtonText: 'Balas',
                    showCancelButton: true,
                    cancelButtonText: 'Tutup',
                    timer: 10000,
                    timerProgressBar: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '/admin/chat';
                    }
                });
            }
        });
    }
});
