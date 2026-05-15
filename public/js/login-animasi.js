const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');
    const emailInput = document.querySelector('#email');
    const loginBtn = document.querySelector('button[type="submit"]');
    const mascotContainer = document.querySelector('#mascot');
    
    const eyelids = document.querySelectorAll('.eyelid');
    const pupils = document.querySelectorAll('.pupil');
    const mouth = document.querySelector('#mouth');
    const mascotText = document.querySelector('#mascot-text');

    let lastMousePos = { x: 0, y: 0 };

    document.addEventListener("DOMContentLoaded", () => {
        gsap.to("body", { opacity: 1, duration: 0.4, ease: "power2.inOut" });

        const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

        tl.from(".gsap-container", { y: 40, opacity: 0, duration: 0.8 })
            .from(".gsap-item", { x: -20, opacity: 0, duration: 0.5, stagger: 0.1 }, "-=0.4")
            .from(".gsap-mascot", { scale: 0.8, opacity: 0, duration: 0.8, ease: "back.out(1.5)" }, "-=0.6");
    });

    document.addEventListener('mousemove', (e) => {
        lastMousePos.x = e.clientX;
        lastMousePos.y = e.clientY;
        
        if (document.activeElement === emailInput || document.activeElement === passwordInput || document.activeElement === togglePassword) return;

        updateEyes(e.clientX, e.clientY);
    });

    function updateEyes(clientX, clientY) {
        const rect = mascotContainer.getBoundingClientRect();
        const mascotCenterX = rect.left + rect.width / 2;
        const mascotCenterY = rect.top + rect.height / 2;

        const deltaX = clientX - mascotCenterX;
        const deltaY = clientY - mascotCenterY;

        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        const maxDistance = 300; 
        const strength = Math.min(distance / maxDistance, 1);

        const angle = Math.atan2(deltaY, deltaX);
        const moveX = Math.cos(angle) * strength * 12;
        const moveY = Math.sin(angle) * strength * 8;

        gsap.to(pupils, { x: moveX, y: moveY, duration: 0.2 });
    }

    emailInput.addEventListener('focus', () => {
        gsap.to(pupils, { x: -10, y: 5, duration: 0.3 });
        gsap.to(mouth, { width: 40, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
        gsap.to(eyelids, { top: "-120%", duration: 0.3 }); 
        mascotText.innerText = "Ketik email dengan perlahan... 🐱";
    });

    emailInput.addEventListener('input', (e) => {
        let progress = Math.min(e.target.value.length / 30, 1);
        let moveX = -10 + (progress * 20); 
        gsap.to(pupils, { x: moveX, duration: 0.1 });
    });

    const resetMascot = () => {
        if (document.activeElement !== emailInput && document.activeElement !== passwordInput && document.activeElement !== togglePassword) {
            gsap.to(eyelids, { top: "-120%", duration: 0.4, ease: "power2.out" });
            gsap.to(mouth, { width: 40, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
            mascotText.innerText = "Meong! Keamanan data Anda adalah prioritas kami 🐾";

            if (lastMousePos.x !== 0 && lastMousePos.y !== 0) {
                updateEyes(lastMousePos.x, lastMousePos.y);
            } else {
                gsap.to(pupils, { x: 0, y: 0, duration: 0.3 });
            }
        }
    };

    emailInput.addEventListener('blur', resetMascot);
    passwordInput.addEventListener('blur', resetMascot);
    togglePassword.addEventListener('blur', resetMascot);

    passwordInput.addEventListener('focus', () => {
        if (passwordInput.getAttribute('type') === 'password') {
            gsap.to(pupils, { x: 0, y: 0, duration: 0.3 });
            gsap.to(eyelids, { top: "0%", duration: 0.4, ease: "bounce.out" });
            gsap.to(mouth, { width: 20, height: 20, borderRadius: "50%", border: "4px solid #334155", duration: 0.3 });
            mascotText.innerText = "Saya tidak ngintip kok! 🙈🐾";
        } else {
            gsap.to(eyelids, { top: "-60%", duration: 0.3, ease: "power2.out" });
            gsap.to(mouth, { width: 30, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
            mascotText.innerText = "Hati-hati, password Anda terlihat! 🙀";
        }
    });

    togglePassword.addEventListener('click', function () {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');

        if (isPassword) {
            gsap.to(eyelids, { top: "-60%", duration: 0.3, ease: "power2.out" });
            gsap.to(mouth, { width: 30, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
            mascotText.innerText = "Hati-hati, password Anda terlihat! 🙀";
        } else {
            gsap.to(eyelids, { top: "0%", duration: 0.3, ease: "bounce.out" });
            gsap.to(mouth, { width: 20, height: 20, borderRadius: "50%", border: "4px solid #334155", duration: 0.3 });
            mascotText.innerText = "Saya tidak ngintip kok! 🙈🐾";
        }
    });

    loginBtn.addEventListener('mouseenter', () => {
        if (document.activeElement !== emailInput && document.activeElement !== passwordInput && document.activeElement !== togglePassword) {
            gsap.to(mouth, { width: 32, height: 16, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "6px solid #334155", duration: 0.3 });
            gsap.to(eyelids, { top: "-80%", duration: 0.3 });
            mascotText.innerText = "Ayo masuk! 😻";
        }
    });

    loginBtn.addEventListener('mouseleave', () => {
        if (document.activeElement !== emailInput && document.activeElement !== passwordInput && document.activeElement !== togglePassword) {
            resetMascot();
        }
    });