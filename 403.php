<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Forbidden | Mamun's Ortho Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #030813;
            --primary: #004591;
            --accent: #ea741b;
            --danger: #ea4335;
            --glass-bg: rgba(255,255,255,0.03);
            --glass-border: rgba(255,255,255,0.06);
            --glass-hover: rgba(255,255,255,0.08);
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
            width: 500px; height: 500px;
            background: var(--danger);
            top: -15%; left: -10%;
            animation: driftA 20s ease-in-out infinite alternate;
        }
        .blob-2 {
            width: 400px; height: 400px;
            background: var(--primary);
            bottom: -10%; right: -8%;
            animation: driftB 18s ease-in-out infinite alternate;
        }
        .blob-3 {
            width: 200px; height: 200px;
            background: var(--accent);
            top: 50%; left: 60%;
            animation: driftA 14s ease-in-out infinite alternate-reverse;
        }
        @keyframes driftA {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, 40px) scale(1.15); }
        }
        @keyframes driftB {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-50px, -30px) scale(1.1); }
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
            border-color: rgba(234,67,53,0.25);
            box-shadow:
                0 40px 100px rgba(0,0,0,0.5),
                0 0 60px rgba(234,67,53,0.06),
                inset 0 1px 0 rgba(255,255,255,0.08);
        }
        @keyframes cardIn {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ─── Animated Shield SVG ─── */
        .graphic {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            position: relative;
        }
        .shield-wrap {
            animation: shieldFloat 6s ease-in-out infinite;
            filter: drop-shadow(0 8px 30px rgba(234,67,53,0.15));
        }
        @keyframes shieldFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            30%      { transform: translateY(-10px) rotate(-2deg); }
            60%      { transform: translateY(-5px) rotate(2deg); }
        }
        .shield-ring {
            position: absolute;
            width: 170px; height: 170px;
            border: 2px dashed rgba(234,67,53,0.15);
            border-radius: 50%;
            animation: ringRotate 25s linear infinite;
        }
        @keyframes ringRotate {
            to { transform: rotate(360deg); }
        }
        .shield-dot {
            position: absolute;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            top: 0; left: 50%;
            transform: translateX(-50%);
            animation: dotPulse 2s ease-in-out infinite;
        }
        @keyframes dotPulse {
            0%, 100% { opacity: 0.3; transform: translateX(-50%) scale(1); }
            50%      { opacity: 1; transform: translateX(-50%) scale(1.5); }
        }

        /* Keyhole pulse */
        .keyhole-pulse {
            animation: kp 2.5s ease-in-out infinite;
            transform-origin: center;
        }
        @keyframes kp {
            0%, 100% { opacity: 0.5; }
            50%      { opacity: 1; }
        }

        /* ─── Status Code ─── */
        .code {
            font-size: 7.5rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -4px;
            background: linear-gradient(135deg, #fff 20%, var(--danger) 50%, #fff 80%);
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
            transform: translateX(4px);
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
                <div class="shield-ring">
                    <div class="shield-dot"></div>
                </div>
                <svg class="shield-wrap" width="130" height="130" viewBox="0 0 100 100" fill="none">
                    <!-- Shield body -->
                    <path d="M50 85C50 85 82 68 82 38V18L50 8L18 18V38C18 68 50 85 50 85Z"
                          fill="url(#sg)" stroke="var(--danger)" stroke-width="2" stroke-linejoin="round"/>
                    <!-- Inner shield -->
                    <path d="M50 80C50 80 76 66 76 40V22L50 13L24 22V40C24 66 50 80 50 80Z"
                          fill="#0a0510" opacity="0.5"/>
                    <!-- Shackle -->
                    <path d="M36 28V22C36 13.2 42.7 6 50 6C57.3 6 64 13.2 64 22V28"
                          stroke="#fff" stroke-width="4" stroke-linecap="round" opacity="0.9"/>
                    <!-- Lock body -->
                    <rect x="37" y="36" width="26" height="20" rx="4" fill="url(#lg)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                    <!-- Keyhole -->
                    <circle class="keyhole-pulse" cx="50" cy="44" r="3.5" fill="#fff"/>
                    <path class="keyhole-pulse" d="M48.5 46.5L51.5 46.5L52 53L48 53Z" fill="#fff"/>
                    <!-- Warning rays -->
                    <line x1="10" y1="14" x2="16" y2="18" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                    <line x1="90" y1="14" x2="84" y2="18" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                    <line x1="50" y1="2" x2="50" y2="6" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" opacity="0.7"/>
                    <defs>
                        <linearGradient id="sg" x1="50" y1="8" x2="50" y2="85">
                            <stop offset="0%" stop-color="#2a1515"/>
                            <stop offset="100%" stop-color="#0a0505"/>
                        </linearGradient>
                        <linearGradient id="lg" x1="50" y1="36" x2="50" y2="56">
                            <stop offset="0%" stop-color="#fff"/>
                            <stop offset="100%" stop-color="#8ab4f8"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="code">403</div>
            <h1 class="title">Access Restricted</h1>
            <p class="desc">This area requires clinical administrator privileges. Your current access level doesn't permit viewing this resource.</p>

            <a href="dashboard.php" class="btn">
                <span>Go to Dashboard</span>
                <i class="fas fa-arrow-right"></i>
            </a>
            <a href="index.php" class="secondary">or visit the public homepage</a>
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
                this.dx = (Math.random() - 0.5) * 0.3;
                this.dy = (Math.random() - 0.5) * 0.3;
                this.opacity = Math.random() * 0.4 + 0.1;
                this.pulse = Math.random() * Math.PI * 2;
                this.pulseSpeed = Math.random() * 0.02 + 0.005;
                this.color = Math.random() > 0.6
                    ? `rgba(234,67,53,${this.opacity})`
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