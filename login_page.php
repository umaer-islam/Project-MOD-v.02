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
        .login-input:focus + .input-icon {
            /* Handled in HTML with peer pattern */
        }
        @keyframes fade-in-up {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }
    </style>
</head>
<body class="font-sans antialiased overflow-hidden bg-white text-navy">
<!-- Developed by Umaer Islam (https://umaerislam.com) -->

    <div class="flex min-h-screen">
        
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

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            document.getElementById('btnText').textContent = 'Authenticating...';
            document.getElementById('btnIcon').classList.add('hidden');
            document.getElementById('btnSpinner').classList.remove('hidden');
            document.getElementById('loginBtn').classList.add('opacity-90', 'cursor-not-allowed', 'bg-navy');
            document.getElementById('loginBtn').classList.remove('hover:bg-gold', 'hover:-translate-y-0.5');
        });
    </script>
</body>
</html>
