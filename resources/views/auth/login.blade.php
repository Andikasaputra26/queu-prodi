<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — SI-PINTER</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0; 
        }
        
        .input-field {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            color: #0f172a;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        
        @keyframes subtleFloat {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        .subtle-float { animation: subtleFloat 6s ease-in-out infinite; }
    </style>
</head>
<body class="p-4 md:p-8">
    
    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-[0_20px_60px_rgba(0,0,0,0.05)] border border-slate-100 flex flex-col md:flex-row overflow-hidden gsap-container">
        
        <div class="w-full md:w-5/12 p-8 md:p-12 lg:p-14 flex flex-col justify-center bg-white z-10 relative">
            <div class="mb-10 gsap-item">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center mb-6 shadow-sm border border-indigo-100">
                    <i class="fas fa-shield-halved text-2xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-slate-800 tracking-tight mb-2">SI-PINTER</h1>
                <p class="text-slate-500 text-sm">Sistem Informasi Pelayanan Jurusan</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3.5 rounded-xl mb-6 text-sm flex items-start gap-3 gsap-item">
                    <i class="fas fa-circle-exclamation mt-0.5"></i>
                    <div class="flex-1">
                        @foreach ($errors->all() as $error)
                            <p class="font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div class="gsap-item">
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}"
                            class="input-field"
                            placeholder="admin@prodi.ac.id" autocomplete="off">
                    </div>
                </div>
                
                <div class="gsap-item">
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="password" name="password" id="password" required
                            class="input-field pr-12"
                            placeholder="••••••••••••">
                        <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-indigo-600 transition-colors focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="gsap-item pt-2">
                    <button type="submit" class="w-full btn-primary font-bold py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 text-[15px]">
                        <span>Masuk Sekarang</span>
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-100 text-center gsap-item">
                <a href="{{ route('queue.register') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-600 transition-all">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>

        <div class="hidden md:flex md:w-7/12 bg-slate-50 border-l border-slate-100 items-center justify-center p-12 relative overflow-hidden">
            <div class="absolute inset-0 opacity-50" style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
            
            <div class="relative z-10 text-center flex flex-col items-center gsap-mascot">
                
                <div class="w-64 h-64 bg-white rounded-[3rem] flex flex-col items-center justify-center shadow-xl border border-slate-100 subtle-float relative overflow-hidden" id="mascot">
                    <div class="flex gap-8 mt-6" id="eyes-container">
                        <div class="w-14 h-16 bg-slate-50 rounded-[50%] relative overflow-hidden shadow-[inset_0_4px_8px_rgba(0,0,0,0.05)] flex items-center justify-center border border-slate-100">
                            <div class="w-6 h-6 bg-slate-800 rounded-full pupil"></div>
                            <div class="w-full h-[120%] bg-indigo-500 absolute -top-[120%] left-0 rounded-b-full eyelid border-b-4 border-indigo-600 shadow-lg"></div>
                        </div>
                        <div class="w-14 h-16 bg-slate-50 rounded-[50%] relative overflow-hidden shadow-[inset_0_4px_8px_rgba(0,0,0,0.05)] flex items-center justify-center border border-slate-100">
                            <div class="w-6 h-6 bg-slate-800 rounded-full pupil"></div>
                            <div class="w-full h-[120%] bg-indigo-500 absolute -top-[120%] left-0 rounded-b-full eyelid border-b-4 border-indigo-600 shadow-lg"></div>
                        </div>
                    </div>
                    <div class="w-12 h-3 border-b-4 border-slate-700 rounded-b-full mt-8 transition-all duration-300" id="mouth"></div>
                </div>

                <div class="mt-10 bg-white/80 backdrop-blur-md px-6 py-3.5 rounded-2xl border border-slate-200 shadow-sm inline-block">
                    <p class="text-slate-600 font-medium text-sm" id="mascot-text">Keamanan data Anda adalah prioritas kami.</p>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#password');
        const emailInput = document.querySelector('#email');
        
        const eyelids = document.querySelectorAll('.eyelid');
        const pupils = document.querySelectorAll('.pupil');
        const mouth = document.querySelector('#mouth');
        const mascotText = document.querySelector('#mascot-text');

        document.addEventListener("DOMContentLoaded", () => {
            gsap.to("body", { opacity: 1, duration: 0.4, ease: "power2.inOut" });

            const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

            tl.from(".gsap-container", { y: 40, opacity: 0, duration: 0.8 })
              .from(".gsap-item", { x: -20, opacity: 0, duration: 0.5, stagger: 0.1 }, "-=0.4")
              .from(".gsap-mascot", { scale: 0.8, opacity: 0, duration: 0.8, ease: "back.out(1.5)" }, "-=0.6");
        });

        emailInput.addEventListener('focus', () => {
            gsap.to(pupils, { x: -10, y: 5, duration: 0.3 });
            gsap.to(mouth, { width: 48, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
            gsap.to(eyelids, { top: "-120%", duration: 0.3 }); 
            mascotText.innerText = "Ketik email dengan perlahan...";
        });

        emailInput.addEventListener('input', (e) => {
            let progress = Math.min(e.target.value.length / 30, 1);
            let moveX = -10 + (progress * 20); 
            gsap.to(pupils, { x: moveX, duration: 0.1 });
        });

        const resetMascot = () => {
            if (document.activeElement !== emailInput && document.activeElement !== passwordInput) {
                gsap.to(pupils, { x: 0, y: 0, duration: 0.3 });
                gsap.to(eyelids, { top: "-120%", duration: 0.4, ease: "power2.out" });
                gsap.to(mouth, { width: 48, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
                mascotText.innerText = "Keamanan data Anda adalah prioritas kami.";
            }
        };

        emailInput.addEventListener('blur', resetMascot);
        passwordInput.addEventListener('blur', resetMascot);

        passwordInput.addEventListener('focus', () => {
            if (passwordInput.getAttribute('type') === 'password') {
                gsap.to(pupils, { x: 0, y: 0, duration: 0.3 });
                gsap.to(eyelids, { top: "0%", duration: 0.4, ease: "bounce.out" });
                gsap.to(mouth, { width: 20, height: 20, borderRadius: "50%", border: "4px solid #334155", duration: 0.3 });
                mascotText.innerText = "Saya tidak ngintip kok! 🙈";
            } else {
                gsap.to(eyelids, { top: "-60%", duration: 0.3, ease: "power2.out" });
                gsap.to(mouth, { width: 30, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
                mascotText.innerText = "Hati-hati, password Anda terlihat!";
            }
        });

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');

            if (document.activeElement === passwordInput) {
                if (isPassword) {
                    gsap.to(eyelids, { top: "-60%", duration: 0.3, ease: "power2.out" });
                    gsap.to(mouth, { width: 30, height: 12, borderRadius: "0 0 100px 100px", borderTop: "none", borderBottom: "4px solid #334155", duration: 0.3 });
                    mascotText.innerText = "Hati-hati, password Anda terlihat!";
                } else {
                    gsap.to(eyelids, { top: "0%", duration: 0.3, ease: "bounce.out" });
                    gsap.to(mouth, { width: 20, height: 20, borderRadius: "50%", border: "4px solid #334155", duration: 0.3 });
                    mascotText.innerText = "Saya tidak ngintip kok! 🙈";
                }
            }
        });
    </script>
</body>
</html>

