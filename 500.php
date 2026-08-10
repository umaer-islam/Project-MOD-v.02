<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error | Mamun's Ortho Dental</title>
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
            --text: #ffffff;
            --text-dim: #8a9cc5;
            --terminal-green: #34d399;
            --terminal-red: #f87171;
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
            background: var(--accent);
            top: -12%; right: -8%;
            animation: driftA 20s ease-in-out infinite alternate;
        }
        .blob-2 {
            width: 380px; height: 380px;
            background: var(--primary);
            bottom: -10%; left: -5%;
            animation: driftB 18s ease-in-out infinite alternate;
        }
        .blob-3 {
            width: 200px; height: 200px;
            background: var(--danger);
            top: 50%; left: 30%;
            animation: driftA 14s ease-in-out infinite alternate-reverse;
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
            padding: 52px 36px 44px;
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

        /* ─── Gear Graphic ─── */
        .graphic {
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            position: relative;
            filter: drop-shadow(0 6px 25px rgba(234,116,27,0.12));
        }
        .gear-cw {
            transform-origin: 38px 38px;
            animation: spinCW 12s linear infinite;
        }
        .gear-ccw {
            transform-origin: 65px 65px;
            animation: spinCCW 8s linear infinite;
        }
        .gear-inner-cw {
            transform-origin: 38px 38px;
            animation: spinCW 6s linear infinite;
        }
        @keyframes spinCW  { to { transform: rotate(360deg); } }
        @keyframes spinCCW { to { transform: rotate(-360deg); } }

        /* Glow pulse on gears */
        .gear-glow {
            animation: gearPulse 3s ease-in-out infinite;
        }
        @keyframes gearPulse {
            0%, 100% { filter: drop-shadow(0 0 4px rgba(234,116,27,0.1)); }
            50%      { filter: drop-shadow(0 0 12px rgba(234,116,27,0.3)); }
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
            margin-bottom: 24px;
            padding: 0 8px;
            opacity: 0;
            animation: fadeUp 0.6s ease 0.8s forwards;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Terminal / Diagnostic Box ─── */
        .terminal {
            background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            padding: 16px 18px;
            text-align: left;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 0.78rem;
            margin-bottom: 32px;
            opacity: 0;
            animation: fadeUp 0.6s ease 0.9s forwards;
            overflow: hidden;
        }
        .terminal-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .terminal-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
        }
        .terminal-dot.r { background: #f87171; }
        .terminal-dot.y { background: #fbbf24; }
        .terminal-dot.g { background: #34d399; }
        .terminal-title {
            margin-left: 8px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.72rem;
            color: var(--text-dim);
            letter-spacing: 0.5px;
        }
        .term-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
            line-height: 1.6;
        }
        .term-line:last-child { margin-bottom: 0; }
        .term-prompt { color: var(--terminal-green); }
        .term-text { color: var(--text-dim); }
        .term-error { color: var(--terminal-red); }
        .term-accent { color: var(--accent); }
        .cursor-blink {
            display: inline-block;
            width: 7px; height: 14px;
            background: var(--terminal-green);
            animation: blink 1s step-end infinite;
            vertical-align: middle;
            margin-left: 2px;
        }
        @keyframes blink {
            50% { opacity: 0; }
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
            transition: transform 0.4s ease;
        }
        .btn:hover i {
            transform: rotate(180deg);
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
            .card { padding: 40px 22px 32px; border-radius: 24px; }
            .code { font-size: 5.5rem; letter-spacing: -3px; }
            .title { font-size: 1.35rem; }
            .terminal { padding: 12px 14px; }
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

            <div class="graphic gear-glow">
                <svg width="130" height="130" viewBox="0 0 100 100" fill="none">
                    <!-- Big gear CW -->
                    <g class="gear-cw">
                        <circle cx="38" cy="38" r="17" fill="url(#gg)" stroke="var(--accent)" stroke-width="1.5"/>
                        <circle cx="38" cy="38" r="6" fill="var(--bg)"/>
                        <!-- Teeth -->
                        <rect x="35" y="15" width="6" height="8" rx="2" fill="var(--accent)"/>
                        <rect x="35" y="53" width="6" height="8" rx="2" fill="var(--accent)"/>
                        <rect x="15" y="35" width="8" height="6" rx="2" fill="var(--accent)"/>
                        <rect x="53" y="35" width="8" height="6" rx="2" fill="var(--accent)"/>
                        <rect x="35" y="15" width="6" height="8" rx="2" fill="var(--accent)" transform="rotate(30 38 38)"/>
                        <rect x="35" y="15" width="6" height="8" rx="2" fill="var(--accent)" transform="rotate(60 38 38)"/>
                        <rect x="35" y="15" width="6" height="8" rx="2" fill="var(--accent)" transform="rotate(-30 38 38)"/>
                        <rect x="35" y="15" width="6" height="8" rx="2" fill="var(--accent)" transform="rotate(-60 38 38)"/>
                        <!-- Inner ring -->
                        <circle cx="38" cy="38" r="11" stroke="rgba(255,255,255,0.1)" stroke-width="1" fill="none"/>
                    </g>
                    <!-- Small gear CCW -->
                    <g class="gear-ccw">
                        <circle cx="65" cy="65" r="13" fill="url(#gg)" stroke="rgba(255,255,255,0.5)" stroke-width="1"/>
                        <circle cx="65" cy="65" r="4.5" fill="var(--bg)"/>
                        <rect x="63" y="48" width="4" height="6" rx="1.5" fill="rgba(255,255,255,0.7)"/>
                        <rect x="63" y="76" width="4" height="6" rx="1.5" fill="rgba(255,255,255,0.7)"/>
                        <rect x="48" y="63" width="6" height="4" rx="1.5" fill="rgba(255,255,255,0.7)"/>
                        <rect x="76" y="63" width="6" height="4" rx="1.5" fill="rgba(255,255,255,0.7)"/>
                        <rect x="63" y="48" width="4" height="6" rx="1.5" fill="rgba(255,255,255,0.7)" transform="rotate(45 65 65)"/>
                        <rect x="63" y="48" width="4" height="6" rx="1.5" fill="rgba(255,255,255,0.7)" transform="rotate(-45 65 65)"/>
                    </g>
                    <!-- Tiny accent gear CW -->
                    <g class="gear-inner-cw">
                        <circle cx="78" cy="28" r="7" fill="none" stroke="var(--danger)" stroke-width="1" opacity="0.5"/>
                        <circle cx="78" cy="28" r="2.5" fill="var(--danger)" opacity="0.4"/>
                    </g>
                    <defs>
                        <linearGradient id="gg" x1="38" y1="21" x2="38" y2="55">
                            <stop offset="0%" stop-color="#3b4d61"/>
                            <stop offset="100%" stop-color="#141c24"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <div class="code">500</div>
            <h1 class="title">Server Error</h1>
            <p class="desc">Something went wrong on our end. Our team has been notified and is working to resolve the issue.</p>

            <!-- Terminal Diagnostic -->
            <div class="terminal">
                <div class="terminal-header">
                    <div class="terminal-dot r"></div>
                    <div class="terminal-dot y"></div>
                    <div class="terminal-dot g"></div>
                    <span class="terminal-title">system诊断</span>
                </div>
                <div class="term-line">
                    <span class="term-prompt">$</span>
                    <span class="term-text" id="term1"></span>
                </div>
                <div class="term-line">
                    <span class="term-prompt">$</span>
                    <span class="term-text" id="term2"></span>
                </div>
                <div class="term-line">
                    <span class="term-prompt">$</span>
                    <span class="term-text" id="term3"></span>
                </div>
                <div class="term-line">
                    <span class="term-prompt">$</span>
                    <span class="term-text" id="term4"></span>
                    <span class="cursor-blink"></span>
                </div>
            </div>

            <button onclick="window.location.reload();" class="btn">
                <i class="fas fa-sync-alt"></i>
                <span>Retry Request</span>
            </button>
            <a href="index.php" class="secondary">or return to the homepage</a>
        </div>
    </div>

    <script>
    /* ─── Terminal Typewriter ─── */
    (function() {
        const lines = [
            { el: 'term1', text: 'Checking server status...', delay: 0 },
            { el: 'term2', text: 'Database connection: OK', delay: 1200 },
            { el: 'term3', text: 'Error logged — retrying...', delay: 2400 },
            { el: 'term4', text: 'Stand by.', delay: 3600 }
        ];

        lines.forEach(line => {
            setTimeout(() => {
                const el = document.getElementById(line.el);
                if (!el) return;
                let i = 0;
                const interval = setInterval(() => {
                    if (i < line.text.length) {
                        el.textContent += line.text[i];
                        i++;
                    } else {
                        clearInterval(interval);
                    }
                }, 35);
            }, line.delay);
        });
    })();

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