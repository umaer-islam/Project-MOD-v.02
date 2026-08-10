<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | Mamun's Ortho Dental</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #030813;
            --primary: #004591;
            --accent: #ea741b;
            --glass-bg: rgba(255,255,255,0.03);
            --glass-border: rgba(255,255,255,0.06);
            --text: #ffffff;
            --text-dim: #8a9cc5;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ─── Particle Canvas ─── */
        #particles {
            position: fixed;
            inset: 0;
            z-index: 0;
        }

        /* ─── Ambient Blobs ─── */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.12;
            z-index: 1;
        }
        .blob-1 {
            width: 450px; height: 450px;
            background: var(--primary);
            top: -12%; left: -8%;
            animation: driftA 22s ease-in-out infinite alternate;
        }
        .blob-2 {
            width: 380px; height: 380px;
            background: var(--accent);
            bottom: -10%; right: -5%;
            animation: driftB 18s ease-in-out infinite alternate;
        }
        .blob-3 {
            width: 220px; height: 220px;
            background: var(--primary);
            top: 55%; left: 65%;
            animation: driftA 15s ease-in-out infinite alternate-reverse;
        }
        @keyframes driftA {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 35px) scale(1.12); }
        }
        @keyframes driftB {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-40px, -25px) scale(1.08); }
        }

        /* ─── Wrapper ─── */
        .wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 480px;
            padding: 20px;
        }

        /* ─── Glass Card ─── */
        .card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            padding: 56px 36px 48px;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            box-shadow:
                0 32px 80px rgba(0,0,0,0.4),
                inset 0 1px 0 rgba(255,255,255,0.05);
            text-align: center;
            opacity: 0;
            transform: translateY(60px) scale(0.96);
            animation: cardIn 0.9s cubic-bezier(0.23, 1, 0.32, 1) 0.2s forwards;
            transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1),
                        border-color 0.4s ease,
                        box-shadow 0.4s ease;
        }
        .card:hover {
            transform: translateY(-6px) scale(1.005);
            border-color: rgba(234,116,27,0.25);
            box-shadow:
                0 40px 100px rgba(0,0,0,0.5),
                0 0 60px rgba(234,116,27,0.06),
                inset 0 1px 0 rgba(255,255,255,0.08);
        }
        @keyframes cardIn {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ─── Tooth Graphic ─── */
        .graphic {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            position: relative;
        }
        .tooth-float {
            animation: toothFloat 5s ease-in-out infinite;
            filter: drop-shadow(0 12px 30px rgba(138,180,248,0.15));
            position: relative;
            z-index: 2;
        }
        @keyframes toothFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25%      { transform: translateY(-14px) rotate(4deg); }
            75%      { transform: translateY(-6px) rotate(-3deg); }
        }

        /* Orbit ring */
        .orbit {
            position: absolute;
            width: 180px; height: 60px;
            border: 2px dashed rgba(234,116,27,0.12);
            border-radius: 50%;
            animation: orbitSpin 18s linear infinite;
        }
        .orbit-dot {
            position: absolute;
            width: 6px; height: 6px;
            background: var(--accent);
            border-radius: 50%;
            top: -3px; left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 0 10px var(--accent);
        }
        @keyframes orbitSpin {
            to { transform: rotate(360deg); }
        }

        /* Stars */
        .star {
            position: absolute;
            width: 3px; height: 3px;
            background: #fff;
            border-radius: 50%;
            animation: starTwinkle 3s ease-in-out infinite;
        }
        .star:nth-child(1) { top: 10%; left: 15%; animation-delay: 0s; }
        .star:nth-child(2) { top: 20%; right: 20%; animation-delay: 0.8s; }
        .star:nth-child(3) { bottom: 25%; left: 10%; animation-delay: 1.5s; }
        .star:nth-child(4) { bottom: 15%; right: 12%; animation-delay: 0.3s; }
        .star:nth-child(5) { top: 50%; left: 5%; animation-delay: 2s; }
        @keyframes starTwinkle {
            0%, 100% { opacity: 0.2; transform: scale(1); }
            50%      { opacity: 1; transform: scale(1.6); }
        }

        /* ─── Status Code ─── */
        .code {
            font-size: 7.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -4px;
            background: linear-gradient(135deg, #fff 20%, var(--accent) 50%, #fff 80%);
            background-size: 250% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: shimmerText 5s linear infinite;
            margin-bottom: 8px;
        }
        @keyframes shimmerText {
            to { background-position: 250% center; }
        }

        /* ─── Typography ─── */
        .title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
            letter-spacing: -0.3px;
            opacity: 0;
            animation: fadeUp 0.6s ease 0.6s forwards;
        }
        .desc {
            font-size: 0.92rem;
            color: var(--text-dim);
            line-height: 1.7;
            margin-bottom: 36px;
            padding: 0 8px;
            opacity: 0;
            animation: fadeUp 0.6s ease 0.8s forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Action Button ─── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary), #002c5c);
            color: #fff;
            text-decoration: none;
            padding: 15px 36px;
            border-radius: 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            border: 1px solid rgba(255,255,255,0.08);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 32px rgba(0,69,145,0.35);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            opacity: 0;
            animation: fadeUp 0.6s ease 1s forwards;
        }
        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--accent), #b8500c);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .btn:hover::before { opacity: 1; }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px rgba(234,116,27,0.35);
            border-color: rgba(255,255,255,0.15);
        }
        .btn:active { transform: translateY(0); }
        .btn span, .btn i {
            position: relative;
            z-index: 1;
        }
        .btn i {
            transition: transform 0.3s ease;
        }
        .btn:hover i {
            transform: translateX(-4px);
        }

        /* ─── Secondary Link ─── */
        .secondary {
            display: block;
            margin-top: 16px;
            font-size: 0.82rem;
            color: var(--text-dim);
            text-decoration: none;
            opacity: 0;
            animation: fadeUp 0.6s ease 1.1s forwards;
            transition: color 0.3s ease;
        }
        .secondary:hover { color: var(--accent); }

        /* ─── Responsive ─── */
        @media (max-width: 540px) {
            .card { padding: 44px 24px 36px; border-radius: 24px; }
            .code { font-size: 5.5rem; letter-spacing: -3px; }
            .title { font-size: 1.35rem; }
        }
    </style>
</head>
<body>

    <canvas id="particles"></canvas>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <div class="wrapper">
        <div class="card">

            <div class="graphic">
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>
                <div class="star"></div>

                <div class="orbit">
                    <div class="orbit-dot"></div>
                </div>

                <svg class="tooth-float" width="120" height="120" viewBox="0 0 100 100" fill="none">
                    <!-- Root Left -->
                    <path d="M38 50C38 64 34 80 30 82C26 84 28 66 33 56" fill="url(#tg)" opacity="0.9"/>
                    <!-- Root Right -->
                    <path d="M62 50C62 64 66 80 70 82C74 84 72 66 67 56" fill="url(#tg)" opacity="0.9"/>
                    <!-- Crown -->
                    <path d="M30 47C27 42 26 26 35 23C42 21 48 26 50 26C52 26 58 21 65 23C74 26 73 42 70 47C68 51 66 54 64 60C62 65 60 69 50 69C40 69 38 65 36 60C34 54 32 51 30 47Z" fill="url(#tg)"/>
                    <!-- Highlights -->
                    <path d="M33 30C32 34 33 40 35 44" stroke="#fff" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                    <path d="M65 26C68 28 69 34 69 38" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                    <circle cx="42" cy="29" r="2.5" fill="#fff" opacity="0.7"/>
                    <!-- Question mark on tooth -->
                    <text x="50" y="52" text-anchor="middle" font-family="Outfit" font-size="16" font-weight="800" fill="var(--accent)" opacity="0.8">?</text>
                    <defs>
                        <linearGradient id="tg" x1="50" y1="22" x2="50" y2="82">
                            <stop offset="0%" stop-color="#ffffff"/>
                            <stop offset="60%" stop-color="#e8f0fe"/>
                            <stop offset="100%" stop-color="#8ab4f8"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="code">404</div>
            <h1 class="title">Page Not Found</h1>
            <p class="desc">The page you're looking for doesn't exist or may have been moved. Let us help you find what you need.</p>

            <a href="index.php" class="btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Home</span>
            </a>
            <a href="contact.php" class="secondary">or contact our clinic</a>
        </div>
    </div>

    <script>
    /* ─── Particle System ─── */
    (function() {
        const canvas = document.getElementById('particles');
        const ctx = canvas.getContext('2d');
        let W, H, particles = [];

        function resize() {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
        }
        resize();
        window.addEventListener('resize', resize);

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * W;
                this.y = Math.random() * H;
                this.r = Math.random() * 1.8 + 0.4;
                this.dx = (Math.random() - 0.5) * 0.25;
                this.dy = (Math.random() - 0.5) * 0.25;
                this.opacity = Math.random() * 0.4 + 0.1;
                this.pulse = Math.random() * Math.PI * 2;
                this.pulseSpeed = Math.random() * 0.02 + 0.005;
                this.color = Math.random() > 0.6
                    ? `rgba(234,116,27,${this.opacity})`
                    : `rgba(138,158,197,${this.opacity})`;
            }
            update() {
                this.x += this.dx;
                this.y += this.dy;
                this.pulse += this.pulseSpeed;
                if (this.x < 0 || this.x > W || this.y < 0 || this.y > H) this.reset();
            }
            draw() {
                const o = this.opacity * (0.6 + 0.4 * Math.sin(this.pulse));
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                ctx.fillStyle = this.color.replace(/[\d.]+\)$/, o + ')');
                ctx.fill();
            }
        }

        for (let i = 0; i < 80; i++) particles.push(new Particle());

        function animate() {
            ctx.clearRect(0, 0, W, H);
            particles.forEach(p => { p.update(); p.draw(); });
            requestAnimationFrame(animate);
        }
        animate();
    })();
    </script>
</body>
</html>