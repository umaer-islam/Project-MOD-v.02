<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
$error_msg = '';
if (isset($_GET['error']) && isset($_GET['msg'])) {
    $error_msg = htmlspecialchars($_GET['msg']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="developer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="designer" content="Umaer Islam — Web Developer & Designer — https://umaerislam.com">
<meta name="copyright" content="© <?= date('Y') ?> Mamun's Ortho Dental. Website designed and developed by Umaer Islam (umaerislam.com)">
<meta name="ai-content-declaration" content="human-authored">
    <title>Doctor Login – Mamun's Ortho Dental</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { navy: '#004591', gold: '#ea741b' },
                    fontFamily: { serif: ['"Playfair Display"', 'serif'], sans: ['"Outfit"', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .login-input {
            width: 100%;
            background: #F4F7FC;
            border: 1px solid transparent;
            border-radius: 12px;
            padding: 16px 16px 16px 48px;
            font-size: 14px;
            color: #004591;
            transition: all 0.3s ease;
            outline: none;
        }
        .login-input:focus {
            background: #ffffff;
            border-color: #004591;
            box-shadow: 0 4px 20px rgba(0,69,145,0.08);
        }
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            transition: color 0.3s ease;
        }
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        /* ── Mobile-Only Redesign ── */
        @media (max-width: 1023px) {
            body { overflow: auto !important; background: #001630 !important; }
            .login-desktop-wrap { display: none !important; }

            .login-mobile-wrap {
                display: flex !important;
                min-height: 100dvh;
                flex-direction: column;
                position: relative;
                overflow: hidden;
            }

            /* Animated gradient background */
            .login-mobile-bg {
                position: fixed; inset: 0; z-index: 0;
                background: linear-gradient(135deg, #001630 0%, #002a60 40%, #001630 100%);
            }
            .login-mobile-bg::before {
                content: ''; position: absolute; top: -40%; right: -30%;
                width: 500px; height: 500px; border-radius: 50%;
                background: radial-gradient(circle, rgba(234,116,27,0.15) 0%, transparent 70%);
                animation: mobBlob1 8s ease-in-out infinite alternate;
            }
            .login-mobile-bg::after {
                content: ''; position: absolute; bottom: -30%; left: -20%;
                width: 400px; height: 400px; border-radius: 50%;
                background: radial-gradient(circle, rgba(0,69,145,0.2) 0%, transparent 70%);
                animation: mobBlob2 10s ease-in-out infinite alternate;
            }
            @keyframes mobBlob1 {
                0% { transform: translate(0, 0) scale(1); }
                100% { transform: translate(-40px, 30px) scale(1.15); }
            }
            @keyframes mobBlob2 {
                0% { transform: translate(0, 0) scale(1); }
                100% { transform: translate(30px, -40px) scale(1.1); }
            }

            /* Grid pattern overlay */
            .login-mobile-grid {
                position: fixed; inset: 0; z-index: 1;
                background-image:
                    linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
                background-size: 40px 40px;
                pointer-events: none;
            }

            /* Mobile content */
            .login-mobile-content {
                position: relative; z-index: 2;
                flex: 1; display: flex; flex-direction: column;
                padding: 0 24px;
                padding-top: max(env(safe-area-inset-top, 0px), 20px);
                padding-bottom: max(env(safe-area-inset-bottom, 0px), 24px);
            }

            /* Mobile brand header */
            .mob-brand {
                display: flex; align-items: center; gap: 14px;
                padding: 20px 0 8px;
            }
            .mob-brand-logo {
                width: 48px; height: 48px; border-radius: 14px;
                background: rgba(255,255,255,0.08);
                border: 1px solid rgba(255,255,255,0.1);
                backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
                display: flex; align-items: center; justify-content: center;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            }
            .mob-brand-logo img { width: 28px; height: 28px; object-fit: contain; }
            .mob-brand-text h1 {
                font-family: 'Playfair Display', serif;
                color: #fff; font-size: 18px; font-weight: 700; line-height: 1.2;
            }
            .mob-brand-text h1 span { color: #ea741b; }
            .mob-brand-text p {
                color: rgba(255,255,255,0.4); font-size: 9px; font-weight: 700;
                text-transform: uppercase; letter-spacing: 0.2em; margin-top: 2px;
            }

            /* Mobile glass card */
            .mob-card {
                margin-top: 28px;
                background: rgba(255,255,255,0.06);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 24px;
                padding: 32px 24px;
                backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
                box-shadow:
                    0 8px 32px rgba(0,0,0,0.3),
                    inset 0 1px 0 rgba(255,255,255,0.06);
            }

            .mob-greeting { margin-bottom: 28px; }
            .mob-greeting .mob-greeting-tag {
                display: inline-flex; align-items: center; gap: 6px;
                background: rgba(234,116,27,0.12); border: 1px solid rgba(234,116,27,0.2);
                border-radius: 100px; padding: 5px 14px;
                font-size: 9px; font-weight: 700; text-transform: uppercase;
                letter-spacing: 0.15em; color: #ea741b; margin-bottom: 16px;
            }
            .mob-greeting .mob-greeting-tag i { font-size: 8px; }
            .mob-greeting h2 {
                font-family: 'Playfair Display', serif;
                color: #fff; font-size: 28px; font-weight: 700; line-height: 1.2; margin-bottom: 6px;
            }
            .mob-greeting p { color: rgba(255,255,255,0.45); font-size: 13px; line-height: 1.5; }

            /* Mobile inputs */
            .mob-field { margin-bottom: 18px; }
            .mob-field label {
                display: block; font-size: 10px; font-weight: 700;
                text-transform: uppercase; letter-spacing: 0.15em;
                color: rgba(255,255,255,0.4); margin-bottom: 8px; padding-left: 2px;
            }
            .mob-input-wrap {
                position: relative; display: flex; align-items: center;
            }
            .mob-input-wrap .mob-input-icon {
                position: absolute; left: 16px; color: rgba(255,255,255,0.25);
                font-size: 14px; pointer-events: none;
                transition: color 0.3s ease; z-index: 2;
            }
            .mob-input {
                width: 100%; min-height: 52px;
                background: rgba(255,255,255,0.05);
                border: 1.5px solid rgba(255,255,255,0.08);
                border-radius: 14px;
                padding: 14px 48px 14px 46px;
                font-size: 15px; color: #fff;
                font-family: 'Outfit', sans-serif;
                outline: none; transition: all 0.3s ease;
                -webkit-appearance: none;
            }
            .mob-input::placeholder { color: rgba(255,255,255,0.2); }
            .mob-input:focus {
                background: rgba(255,255,255,0.08);
                border-color: rgba(234,116,27,0.5);
                box-shadow: 0 0 0 3px rgba(234,116,27,0.1);
            }
            .mob-input:focus ~ .mob-input-icon { color: #ea741b; }

            /* Password toggle */
            .mob-pw-toggle {
                position: absolute; right: 8px;
                width: 36px; height: 36px; border-radius: 10px;
                background: transparent; border: none;
                color: rgba(255,255,255,0.3); font-size: 14px;
                display: flex; align-items: center; justify-content: center;
                cursor: pointer; transition: all 0.2s ease; z-index: 2;
            }
            .mob-pw-toggle:hover { color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.05); }
            .mob-pw-toggle:active { transform: scale(0.9); }

            /* Forgot link row */
            .mob-meta {
                display: flex; align-items: center; justify-content: space-between;
                margin-bottom: 24px; margin-top: -4px;
            }
            .mob-meta a {
                color: rgba(255,255,255,0.4); font-size: 12px; font-weight: 500;
                text-decoration: none; transition: color 0.2s;
            }
            .mob-meta a:hover { color: #ea741b; }

            /* Mobile submit button */
            .mob-submit {
                width: 100%; min-height: 54px;
                background: linear-gradient(135deg, #ea741b 0%, #cf5e0e 100%);
                border: none; border-radius: 14px;
                color: #fff; font-size: 12px; font-weight: 700;
                text-transform: uppercase; letter-spacing: 0.15em;
                cursor: pointer; position: relative; overflow: hidden;
                display: flex; align-items: center; justify-content: center; gap: 10px;
                box-shadow: 0 4px 24px rgba(234,116,27,0.35);
                transition: all 0.3s ease;
                -webkit-appearance: none;
            }
            .mob-submit:active { transform: scale(0.98); }
            .mob-submit::after {
                content: ''; position: absolute; inset: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%);
                pointer-events: none;
            }
            .mob-submit.spinner-state {
                opacity: 0.85; pointer-events: none;
            }

            /* Error message */
            .mob-error {
                display: flex; align-items: center; gap: 10px;
                background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
                border-radius: 14px; padding: 14px 16px; margin-bottom: 20px;
                animation: mobShake 0.4s ease;
            }
            .mob-error i { color: #ef4444; font-size: 14px; flex-shrink: 0; }
            .mob-error span { color: #fca5a5; font-size: 13px; font-weight: 500; }
            @keyframes mobShake {
                0%, 100% { transform: translateX(0); }
                20% { transform: translateX(-6px); }
                40% { transform: translateX(6px); }
                60% { transform: translateX(-4px); }
                80% { transform: translateX(4px); }
            }

            /* Mobile footer */
            .mob-footer {
                margin-top: 32px; padding-top: 20px;
                border-top: 1px solid rgba(255,255,255,0.06);
                text-align: center;
            }
            .mob-footer-badges {
                display: flex; align-items: center; justify-content: center;
                gap: 16px; margin-bottom: 16px;
            }
            .mob-badge {
                display: inline-flex; align-items: center; gap: 5px;
                font-size: 9px; font-weight: 700; text-transform: uppercase;
                letter-spacing: 0.1em; color: rgba(255,255,255,0.25);
            }
            .mob-badge i { font-size: 8px; color: rgba(234,116,27,0.4); }
            .mob-badge-dot {
                width: 3px; height: 3px; border-radius: 50%;
                background: rgba(255,255,255,0.15);
            }
            .mob-back-link {
                display: inline-flex; align-items: center; gap: 6px;
                font-size: 11px; color: rgba(255,255,255,0.3);
                text-decoration: none; font-weight: 500;
                transition: color 0.2s;
            }
            .mob-back-link:hover { color: rgba(255,255,255,0.6); }
            .mob-back-link i { font-size: 10px; }
        }

        /* Desktop: hide mobile layout */
        .login-mobile-wrap { display: none; }
    </style>
</head>
<body class="font-sans antialiased overflow-hidden bg-white text-navy">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->

    <!-- ═══ DESKTOP LAYOUT (unchanged) ═══ -->
    <div class="flex min-h-screen login-desktop-wrap">
        
        <!-- Left Banner (Image) -->
        <div class="hidden lg:flex w-1/2 relative bg-navy items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-navy/60 to-navy/10 z-10 mix-blend-multiply"></div>
            <img src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=1200&q=80" 
                 alt="Premium Clinic Interior" 
                 class="absolute inset-0 w-full h-full object-cover">
            
            <div class="relative z-20 p-16 w-full max-w-lg mb-20 animate-fade-in-up">
                <img src="Logo.png" alt="Mamun's Ortho Dental Logo" class="w-16 h-16 object-contain mb-8 drop-shadow-xl">
                <h1 class="font-serif text-5xl text-white font-bold mb-6 leading-tight">Elevating<br><span class="italic font-light text-gold">Dental Care</span></h1>
                <p class="text-white/80 text-lg leading-relaxed font-light">Secure access to Mamun's Ortho Dental clinical portal. Manage patients, appointments, and medical records securely.</p>
                
                <div class="mt-12 flex items-center gap-4 text-white/50 text-[10px] uppercase tracking-widest font-bold">
                    <i class="fas fa-shield-halved"></i> End-to-End Encrypted
                </div>
            </div>
        </div>

        <!-- Right Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 xl:p-24 relative overflow-y-auto">
            
            <!-- Mobile Background Elements -->
            <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-br from-[#004591]/5 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/2 lg:hidden pointer-events-none"></div>

            <div class="w-full max-w-md relative z-10 animate-fade-in-up" style="animation-delay: 0.1s;">
                
                <a href="index.php" class="inline-flex items-center gap-2 text-[10px] font-bold tracking-widest uppercase text-gray-400 hover:text-navy transition-colors mb-12">
                    <i class="fas fa-arrow-left"></i> Return to Website
                </a>

                <div class="mb-10">
                    <p class="text-gold text-[10px] uppercase font-bold tracking-[0.2em] mb-3">Clinical Portal</p>
                    <h2 class="font-serif text-4xl text-navy font-bold leading-tight mb-2">Welcome Back</h2>
                    <p class="text-gray-500 text-sm">Please sign in to access your administrative dashboard.</p>
                </div>

                <?php if ($error_msg): ?>
                <div class="flex items-center gap-3 bg-red-50 border border-red-100 text-red-600 px-4 py-3 rounded-xl text-sm font-medium mb-8 animate-pulse">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error_msg ?></span>
                </div>
                <?php endif; ?>

                <form action="login.php" method="POST" id="loginForm" class="space-y-6">
                    
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 mb-2 ml-1" for="email">Email Address</label>
                        <div class="relative">
                            <input class="login-input peer" type="email" id="email" name="email"
                                   placeholder="doctor@mamunsdental.com" required
                                   value="<?= isset($_GET['error']) ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                            <i class="fas fa-envelope input-icon peer-focus:text-navy z-10 pointer-events-none"></i>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-gray-500 ml-1" for="password">Password</label>
                            <a href="#" class="text-[10px] font-bold uppercase text-navy hover:text-gold transition-colors">Forgot?</a>
                        </div>
                        <div class="relative">
                            <input class="login-input peer" type="password" id="password" name="password"
                                   placeholder="••••••••" required>
                            <i class="fas fa-lock input-icon peer-focus:text-navy z-10 pointer-events-none"></i>
                        </div>
                    </div>

                    <button type="submit" id="loginBtn" class="w-full bg-navy hover:bg-gold text-white font-bold text-xs uppercase tracking-widest py-4 rounded-xl shadow-lg shadow-navy/20 hover:shadow-gold/30 transition-all transform hover:-translate-y-0.5 mt-4 flex justify-center items-center gap-2 h-[52px]">
                        <span id="btnText">Sign In Securely</span>
                        <i class="fas fa-arrow-right" id="btnIcon"></i>
                        <svg id="btnSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    
                </form>

                <div class="mt-12 pt-8 border-t border-gray-100 flex items-center justify-center gap-6">
                    <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">BMDC Certified</span>
                    <span class="w-1 h-1 bg-gray-200 rounded-full"></span>
                    <span class="text-[9px] uppercase tracking-widest font-bold text-gray-400">SSL Encrypted</span>
                </div>
            </div>
            
        </div>
    </div>
    <!-- /Desktop Layout -->

    <!-- ═══ MOBILE LAYOUT ═══ -->
    <div class="login-mobile-wrap">
        <div class="login-mobile-bg"></div>
        <div class="login-mobile-grid"></div>

        <div class="login-mobile-content">
            <!-- Brand -->
            <div class="mob-brand">
                <div class="mob-brand-logo">
                    <img src="Logo.png" alt="Logo">
                </div>
                <div class="mob-brand-text">
                    <h1>Mamun's <span>Ortho</span></h1>
                    <p>Clinical Portal</p>
                </div>
            </div>

            <!-- Glass Card -->
            <div class="mob-card">
                <div class="mob-greeting">
                    <div class="mob-greeting-tag">
                        <i class="fas fa-shield-halved"></i> Secure Login
                    </div>
                    <h2>Welcome<br>Back</h2>
                    <p>Sign in to access your dashboard</p>
                </div>

                <?php if ($error_msg): ?>
                <div class="mob-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= $error_msg ?></span>
                </div>
                <?php endif; ?>

                <form action="login.php" method="POST" id="mobLoginForm">
                    <div class="mob-field">
                        <label for="mob-email">Email Address</label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-envelope mob-input-icon"></i>
                            <input class="mob-input" type="email" id="mob-email" name="email"
                                   placeholder="doctor@mamunsdental.com" required autocomplete="email"
                                   value="<?= isset($_GET['error']) ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                        </div>
                    </div>

                    <div class="mob-field">
                        <label for="mob-password">Password</label>
                        <div class="mob-input-wrap">
                            <i class="fas fa-lock mob-input-icon"></i>
                            <input class="mob-input" type="password" id="mob-password" name="password"
                                   placeholder="Enter your password" required autocomplete="current-password"
                                   style="padding-right: 52px;">
                            <button type="button" class="mob-pw-toggle" onclick="toggleMobPw()" aria-label="Toggle password visibility">
                                <i class="fas fa-eye" id="mobPwEye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mob-meta">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="remember" style="width:16px;height:16px;border-radius:4px;accent-color:#ea741b;">
                            <span style="color:rgba(255,255,255,0.4);font-size:12px;">Remember me</span>
                        </label>
                        <a href="#">Forgot password?</a>
                    </div>

                    <button type="submit" id="mobLoginBtn" class="mob-submit">
                        <span id="mobBtnText">Sign In</span>
                        <i class="fas fa-arrow-right" id="mobBtnIcon"></i>
                        <svg id="mobBtnSpinner" class="hidden animate-spin w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>

                <div class="mob-footer">
                    <div class="mob-footer-badges">
                        <span class="mob-badge"><i class="fas fa-certificate"></i> BMDC</span>
                        <span class="mob-badge-dot"></span>
                        <span class="mob-badge"><i class="fas fa-lock"></i> SSL</span>
                        <span class="mob-badge-dot"></span>
                        <span class="mob-badge"><i class="fas fa-shield-halved"></i> Encrypted</span>
                    </div>
                    <a href="index.php" class="mob-back-link">
                        <i class="fas fa-arrow-left"></i> Back to website
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Desktop form
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('btnText').textContent = 'Authenticating...';
            document.getElementById('btnIcon').classList.add('hidden');
            document.getElementById('btnSpinner').classList.remove('hidden');
            document.getElementById('loginBtn').classList.add('opacity-90', 'cursor-not-allowed', 'bg-navy');
            document.getElementById('loginBtn').classList.remove('hover:bg-gold', 'hover:-translate-y-0.5');
        });

        // Mobile password toggle
        function toggleMobPw() {
            const pw = document.getElementById('mob-password');
            const eye = document.getElementById('mobPwEye');
            if (pw.type === 'password') {
                pw.type = 'text';
                eye.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pw.type = 'password';
                eye.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Mobile form submit
        document.getElementById('mobLoginForm').addEventListener('submit', function() {
            const btn = document.getElementById('mobLoginBtn');
            document.getElementById('mobBtnText').textContent = 'Signing in...';
            document.getElementById('mobBtnIcon').classList.add('hidden');
            document.getElementById('mobBtnSpinner').classList.remove('hidden');
            btn.classList.add('spinner-state');
        });
    </script>
</body>
</html>
