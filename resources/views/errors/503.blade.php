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
            --bg-color: #ffffff;
            --primary-blue: #0f4c81;
            --light-blue: #aae0fa;
            --border-blue: #004b8d;
            --text-blue: #084c82;
            --text-muted: #5f8fb8;
            --circle-bg: #eef8ff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-blue);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            overflow-y: auto;
            position: relative;
            padding: 2rem 1rem;
        }

        .maintenance-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2.5rem;
            position: relative;
            z-index: 10;
        }

        /* Branding Header */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--border-blue);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .brand-icon {
            font-size: 1.2rem;
        }

        /* Graphic Wrapper */
        .graphic-wrapper {
            position: relative;
            width: 440px;
            height: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .illustration-circle {
            width: 100%;
            height: 100%;
            background-color: var(--circle-bg);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 95px 2.5rem 2rem 2.5rem;
            text-align: center;
            box-shadow: inset 0 2px 8px rgba(37, 99, 235, 0.03);
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.25;
            color: var(--text-blue);
            margin-bottom: 1rem;
            max-width: 320px;
        }

        .description {
            font-size: 1.05rem;
            line-height: 1.5;
            color: var(--text-muted);
            font-weight: 500;
            max-width: 280px;
        }

        /* Plug Row layout */
        .plug-row {
            position: absolute;
            left: calc(-50vw + 170px); /* extended offscreen by 50px */
            right: calc(-50vw + 170px); /* extended offscreen by 50px */
            top: 290px; /* cuts across lower middle part of 440px circle */
            display: flex;
            align-items: center;
            z-index: 3;
        }

        .cable-unit {
            flex: 1;
            display: flex;
            align-items: center;
        }

        .cable-unit.left {
            animation: float-left-unit 3.5s ease-in-out infinite alternate;
        }

        .cable-unit.right {
            animation: float-right-unit 3.5s ease-in-out infinite alternate;
        }

        .cord-line {
            flex: 1;
            height: 12px;
            background-color: #ffffff;
            border-top: 3px solid var(--border-blue);
            border-bottom: 3px solid var(--border-blue);
        }

        .plug-svg {
            height: auto;
            display: block;
        }

        .plug-svg.male {
            width: 80px;
        }

        .plug-svg.female {
            width: 70px;
        }

        .plug-gap {
            position: relative;
            width: 60px;
            height: 8px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .spark {
            font-size: 1.25rem;
            color: #eab308; /* vibrant yellow */
            text-shadow: 0 0 10px rgba(234, 179, 8, 0.8), 0 0 20px rgba(234, 179, 8, 0.4);
            animation: spark-pulse 2.2s ease-in-out infinite;
            position: absolute;
            opacity: 0;
            pointer-events: none;
            z-index: 10;
        }

        @keyframes float-left-unit {
            0% {
                transform: translateX(0) translateY(0);
            }
            100% {
                transform: translateX(8px) translateY(-3px);
            }
        }

        @keyframes float-right-unit {
            0% {
                transform: translateX(0) translateY(0);
            }
            100% {
                transform: translateX(-8px) translateY(3px);
            }
        }

        @keyframes spark-pulse {
            0%, 88%, 94%, 98%, 100% {
                opacity: 0;
                transform: scale(0.7) rotate(0deg);
            }
            90%, 96% {
                opacity: 1;
                transform: scale(1.2) rotate(15deg);
            }
        }

        /* Buttons & Actions Styling */
        .actions-container {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            width: 100%;
            max-width: 340px;
        }

        .btn {
            width: 100%;
            padding: 0.9rem 1.5rem;
            border-radius: 12px;
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
            background-color: var(--border-blue);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 75, 141, 0.15);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 75, 141, 0.25);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background-color: #ffffff;
            border: 2px solid var(--border-blue);
            color: var(--border-blue);
        }

        .btn-secondary:hover {
            background-color: #f0f9ff;
            transform: translateY(-2px);
        }

        .footer {
            font-size: 0.775rem;
            color: var(--text-muted);
            text-align: center;
            line-height: 1.6;
        }

        .footer-brand {
            font-weight: 600;
            color: var(--text-blue);
        }

        /* Responsive Media Queries */
        @media (max-width: 600px) {
            .graphic-wrapper {
                width: 320px;
                height: 320px;
            }

            .illustration-circle {
                padding-top: 65px;
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }

            .title {
                font-size: 1.55rem;
                max-width: 240px;
                margin-bottom: 0.75rem;
            }

            .description {
                font-size: 0.9rem;
                max-width: 200px;
            }

            .plug-row {
                left: calc(-50vw + 120px); /* extended offscreen */
                right: calc(-50vw + 120px); /* extended offscreen */
                top: 210px;
            }

            .cord-line {
                height: 9px;
                border-top-width: 2px;
                border-bottom-width: 2px;
            }

            .plug-svg.male {
                width: 60px;
            }

            .plug-svg.female {
                width: 52px;
            }

            .plug-gap {
                width: 44px;
            }

            .actions-container {
                max-width: 280px;
            }

            .btn {
                padding: 0.8rem 1.25rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .graphic-wrapper {
                width: 260px;
                height: 260px;
            }

            .illustration-circle {
                padding-top: 55px;
            }

            .title {
                font-size: 1.3rem;
                max-width: 200px;
            }

            .description {
                font-size: 0.8rem;
                max-width: 160px;
            }

            .plug-row {
                left: calc(-50vw + 90px); /* extended offscreen */
                right: calc(-50vw + 90px); /* extended offscreen */
                top: 170px;
            }

            .plug-svg.male {
                width: 50px;
            }

            .plug-svg.female {
                width: 44px;
            }

            .plug-gap {
                width: 36px;
            }
        }

        /* Handle landscape or short screen heights */
        @media (max-height: 620px) {
            body {
                align-items: flex-start;
                padding-top: 3rem;
                padding-bottom: 3rem;
            }
        }
    </style>
</head>
<body>

    <div class="maintenance-container">
        
        <!-- Branding Header -->
        <div class="brand-header">
            <span class="brand-icon">🎓</span>
            <span class="brand-text">SI-PINTER</span>
        </div>

        <!-- Graphic Wrapper -->
        <div class="graphic-wrapper">
            
            <!-- Centered light blue circle -->
            <div class="illustration-circle">
                <h1 class="title">This site is under maintenance</h1>
                <p class="description">We're preparing to serve you better.</p>
            </div>

            <!-- Horizontal line with plugs crossing the circle -->
            <div class="plug-row">
                <!-- Left Cable Unit -->
                <div class="cable-unit left">
                    <div class="cord-line"></div>
                    <!-- Male Plug -->
                    <svg class="plug-svg male" viewBox="0 0 80 60" width="80" height="60">
                        <rect x="0" y="24" width="8" height="12" rx="2" fill="#aae0fa" stroke="#004b8d" stroke-width="3"/>
                        <rect x="8" y="10" width="38" height="40" rx="8" fill="#aae0fa" stroke="#004b8d" stroke-width="3"/>
                        <rect x="46" y="5" width="8" height="50" rx="3" fill="#aae0fa" stroke="#004b8d" stroke-width="3"/>
                        <rect x="54" y="17" width="20" height="8" rx="4" fill="#ffffff" stroke="#004b8d" stroke-width="3"/>
                        <rect x="54" y="35" width="20" height="8" rx="4" fill="#ffffff" stroke="#004b8d" stroke-width="3"/>
                    </svg>
                </div>

                <!-- Plug Gap with Electric Spark -->
                <div class="plug-gap">
                    <div class="spark">⚡</div>
                </div>

                <!-- Right Cable Unit -->
                <div class="cable-unit right">
                    <!-- Female Socket -->
                    <svg class="plug-svg female" viewBox="0 0 70 60" width="70" height="60">
                        <rect x="62" y="24" width="8" height="12" rx="2" fill="#aae0fa" stroke="#004b8d" stroke-width="3"/>
                        <rect x="24" y="10" width="38" height="40" rx="8" fill="#aae0fa" stroke="#004b8d" stroke-width="3"/>
                        <rect x="16" y="5" width="8" height="50" rx="3" fill="#aae0fa" stroke="#004b8d" stroke-width="3"/>
                        <path d="M 24,10 L 29,10 L 29,50 L 24,50 Z" fill="rgba(2, 74, 135, 0.15)"/>
                        <line x1="24" y1="10" x2="24" y2="50" stroke="#004b8d" stroke-width="3"/>
                    </svg>
                    <div class="cord-line"></div>
                </div>
            </div>

        </div>

        <!-- Action Buttons -->
        <div class="actions-container">
            <button id="btn-reload" onclick="window.location.reload()" class="btn btn-primary">
                <i class="fa-solid fa-rotate-right"></i> Muat Ulang Halaman
            </button>
            <a id="btn-wa-support" href="https://wa.me/628123456789" class="btn btn-secondary" target="_blank">
                <i class="fa-brands fa-whatsapp"></i> Hubungi Layanan Akademik
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih atas pengertian dan kesabaran Anda.</p>
            <p>&copy; {{ date('Y') }} <span class="footer-brand">SI-PINTER</span>. All rights reserved.</p>
        </div>

    </div>

</body>
</html>
