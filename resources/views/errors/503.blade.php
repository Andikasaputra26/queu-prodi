<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeliharaan Sistem — SI-PINTER</title>
    <meta name="description" content="SI-PINTER sedang dalam pemeliharaan sistem. Kami akan segera kembali untuk melayani Anda.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #020617;
            --card-bg: rgba(15, 23, 42, 0.65);
            --card-border: rgba(255, 255, 255, 0.08);
            --primary: #2563eb;
            --primary-glow: rgba(37, 99, 235, 0.3);
            --secondary: #4f46e5;
            --accent: #0ea5e9;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated Ambient Lights */
        .ambient-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(140px);
            z-index: 1;
            opacity: 0.25;
            animation: floatGlow 15s ease-in-out infinite alternate;
        }

        .glow-1 {
            background: radial-gradient(circle, var(--primary) 0%, transparent 70%);
            top: -10%;
            left: -10%;
        }

        .glow-2 {
            background: radial-gradient(circle, var(--secondary) 0%, transparent 70%);
            bottom: -10%;
            right: -10%;
            animation-delay: -7.5s;
        }

        .glow-3 {
            background: radial-gradient(circle, var(--accent) 0%, transparent 70%);
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            opacity: 0.1;
            animation: pulseGlow 8s ease-in-out infinite alternate;
        }

        @keyframes floatGlow {
            0% {
                transform: translate(0, 0) scale(1);
            }
            100% {
                transform: translate(80px, 50px) scale(1.15);
            }
        }

        @keyframes pulseGlow {
            0% {
                opacity: 0.05;
                transform: translate(-50%, -50%) scale(0.9);
            }
            100% {
                opacity: 0.15;
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        /* Floating background elements */
        .grid-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center center;
            z-index: 2;
            mask-image: radial-gradient(circle at center, black 40%, transparent 80%);
            -webkit-mask-image: radial-gradient(circle at center, black 40%, transparent 80%);
        }

        .container {
            width: 100%;
            max-width: 580px;
            padding: 2rem;
            position: relative;
            z-index: 10;
        }

        .maintenance-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 3.5rem 3rem;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow: 
                0 30px 60px -15px rgba(0, 0, 0, 0.8), 
                0 0 50px rgba(37, 99, 235, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2.25rem;
        }

        /* Brand Styling */
        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--text-main);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .brand-icon {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        /* Emblem / Rotating Gears Illustration */
        .emblem-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .emblem-glow-ring {
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            border: 2px dashed rgba(37, 99, 235, 0.2);
            animation: rotateDashed 20s linear infinite;
        }

        .gear-main {
            font-size: 4.5rem;
            background: linear-gradient(135deg, var(--text-main) 30%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            z-index: 3;
            animation: spinClockwise 12s linear infinite;
            filter: drop-shadow(0 0 15px rgba(14, 165, 233, 0.3));
        }

        .gear-sub {
            position: absolute;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            bottom: 25px;
            right: 25px;
            z-index: 2;
            animation: spinCounterClockwise 8s linear infinite;
            filter: drop-shadow(0 0 8px rgba(79, 70, 229, 0.2));
        }

        @keyframes rotateDashed {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes spinClockwise {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes spinCounterClockwise {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }

        /* Content Text */
        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.15rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.2;
            background: linear-gradient(135deg, #ffffff 40%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .description {
            font-size: 0.975rem;
            line-height: 1.65;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Status Pill */
        .status-pill {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: rgba(16, 185, 129, 0.06);
            border: 1px solid rgba(16, 185, 129, 0.15);
            padding: 0.6rem 1.25rem;
            border-radius: 99px;
            width: fit-content;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseDot 2s infinite;
        }

        @keyframes pulseDot {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        .status-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--success);
            letter-spacing: 0.02em;
        }

        .divider {
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.08) 20%, rgba(255, 255, 255, 0.08) 80%, transparent);
        }

        /* Buttons & Actions */
        .actions {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .btn {
            width: 100%;
            padding: 0.95rem 1.5rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: #ffffff;
            box-shadow: 0 4px 25px var(--primary-glow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(37, 99, 235, 0.45);
            filter: brightness(1.1);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text-main);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.07);
            border-color: rgba(255, 255, 255, 0.18);
            transform: translateY(-2px);
        }

        .btn-secondary:active {
            transform: translateY(0);
        }

        .footer {
            font-size: 0.775rem;
            color: #64748b;
            line-height: 1.6;
        }

        .footer-brand {
            color: var(--text-muted);
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .maintenance-card {
                padding: 3rem 1.75rem;
            }
            .title {
                font-size: 1.85rem;
            }
            .brand {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

    <div class="grid-bg"></div>
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>
    <div class="ambient-glow glow-3"></div>

    <div class="container">
        <div class="maintenance-card">
            
            <div class="brand">
                <span class="brand-icon">🎓</span> SI-PINTER
            </div>

            <div class="emblem-container">
                <div class="emblem-glow-ring"></div>
                <i class="fa-solid fa-gear gear-main"></i>
                <i class="fa-solid fa-gear gear-sub"></i>
            </div>

            <div class="info-group">
                <h1 class="title" id="page-title">Maintenance Sistem</h1>
                <p class="description" id="page-desc">Kami sedang melakukan maintenance pada sistem SI-PINTER untuk meningkatkan kualitas, keandalan, dan kecepatan pelayanan persuratan terpadu kami. Kami akan segera kembali aktif.</p>
            </div>

            <div class="status-pill">
                <div class="pulse-dot"></div>
                <span class="status-text" id="status-label">Maintenance Berlangsung</span>
            </div>

            <div class="divider"></div>

            <div class="actions">
                <button id="btn-reload" onclick="window.location.reload()" class="btn btn-primary">
                    <i class="fa-solid fa-rotate-right"></i> Muat Ulang Halaman
                </button>
                <a id="btn-wa-support" href="https://wa.me/628123456789" class="btn btn-secondary" target="_blank">
                    <i class="fa-brands fa-whatsapp"></i> Hubungi Layanan Akademik
                </a>
            </div>

            <div class="footer">
                <p>Terima kasih atas pengertian dan kesabaran Anda.</p>
                <p>&copy; {{ date('Y') }} <span class="footer-brand">SI-PINTER</span>. All rights reserved.</p>
            </div>

        </div>
    </div>

</body>
</html>
