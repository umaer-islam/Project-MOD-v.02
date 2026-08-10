<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dr. Muhammad Shamim Al Mamun — Digital Identity Card</title>
  <meta name="description" content="NFC Digital Identity Card for Dr. Muhammad Shamim Al Mamun — Associate Professor & Head, Dept. of Orthodontics.">
  <meta name="robots" content="noindex, follow">
  <link rel="icon" type="image/png" href="../Logo.png">
<link rel="manifest" href="../site.webmanifest">
<meta name="theme-color" content="#ea741b">
<meta name="msapplication-config" content="../browserconfig.xml">
<link rel="author" href="../humans.txt">

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; -webkit-tap-highlight-color: transparent; }

    :root {
      --navy: #004591;
      --navy-dark: #001e40;
      --orange: #ea741b;
      --orange-dark: #cf5e0e;
      --bg: #f5f7fa;
      --card: #ffffff;
      --text: #1a1a2e;
      --text-muted: #6b7280;
      --border: rgba(0, 69, 145, 0.08);
      --shadow-sm: 0 2px 12px rgba(0,0,0,0.04);
      --shadow-md: 0 8px 30px rgba(0,69,145,0.08);
      --shadow-lg: 0 20px 50px rgba(0,69,145,0.12);
      --spring: cubic-bezier(0.16, 1, 0.3, 1);
      --smooth: cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* Scrollbar */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--orange); }

    /* ═══════════════════════════════
       NFC INTRO OVERLAY
       ═══════════════════════════════ */
    #intro-overlay {
      position: fixed; inset: 0; z-index: 9999;
      background: linear-gradient(160deg, #f0f4f8 0%, #e2e8f0 50%, #f8fafc 100%);
      display: flex; flex-direction: column;
      justify-content: center; align-items: center;
      transition: opacity 0.9s var(--smooth), visibility 0.9s;
    }
    #intro-overlay.fade-out { opacity: 0; visibility: hidden; }

    #intro-card {
      width: 320px; height: 200px;
      background: linear-gradient(135deg, var(--navy-dark) 0%, var(--navy) 100%);
      border-radius: 24px; padding: 28px;
      display: flex; flex-direction: column; justify-content: space-between;
      position: relative; overflow: hidden;
      box-shadow: 0 25px 60px rgba(0,45,100,0.25), 0 0 0 1px rgba(255,255,255,0.1) inset;
      transition: transform 1.2s var(--spring), opacity 0.8s;
      transform-style: preserve-3d;
    }
    #intro-card::before {
      content: '';
      position: absolute; top: -50%; right: -30%; width: 200%; height: 200%;
      background: radial-gradient(circle, rgba(234,116,27,0.15) 0%, transparent 60%);
      pointer-events: none;
    }
    #intro-overlay.fade-out #intro-card {
      transform: scale(0.8) rotateY(90deg);
      opacity: 0;
    }

    /* ═══════════════════════════════
       SCROLL-TRIGGERED REVEAL SYSTEM
       ═══════════════════════════════ */
    .reveal {
      opacity: 0;
      transform: translateY(40px);
      transition: opacity 0.8s var(--spring), transform 0.8s var(--spring);
    }
    .reveal.visible {
      opacity: 1;
      transform: translateY(0);
    }
    .reveal-left {
      opacity: 0;
      transform: translateX(-40px);
      transition: opacity 0.8s var(--spring), transform 0.8s var(--spring);
    }
    .reveal-left.visible {
      opacity: 1;
      transform: translateX(0);
    }
    .reveal-right {
      opacity: 0;
      transform: translateX(40px);
      transition: opacity 0.8s var(--spring), transform 0.8s var(--spring);
    }
    .reveal-right.visible {
      opacity: 1;
      transform: translateX(0);
    }
    .reveal-scale {
      opacity: 0;
      transform: scale(0.85);
      transition: opacity 0.8s var(--spring), transform 0.8s var(--spring);
    }
    .reveal-scale.visible {
      opacity: 1;
      transform: scale(1);
    }

    /* Stagger children */
    .stagger-children > * {
      opacity: 0;
      transform: translateY(25px);
      transition: opacity 0.6s var(--spring), transform 0.6s var(--spring);
    }
    .stagger-children.visible > *:nth-child(1) { transition-delay: 0.05s; opacity: 1; transform: translateY(0); }
    .stagger-children.visible > *:nth-child(2) { transition-delay: 0.1s; opacity: 1; transform: translateY(0); }
    .stagger-children.visible > *:nth-child(3) { transition-delay: 0.15s; opacity: 1; transform: translateY(0); }
    .stagger-children.visible > *:nth-child(4) { transition-delay: 0.2s; opacity: 1; transform: translateY(0); }
    .stagger-children.visible > *:nth-child(5) { transition-delay: 0.25s; opacity: 1; transform: translateY(0); }
    .stagger-children.visible > *:nth-child(6) { transition-delay: 0.3s; opacity: 1; transform: translateY(0); }

    /* ═══════════════════════════════
       PROFILE CARD STYLES
       ═══════════════════════════════ */
    .profile-wrapper {
      width: 100%;
      max-width: 480px;
      min-height: 100vh;
      background: var(--bg);
      position: relative;
    }

    .profile-header-bg {
      background: linear-gradient(160deg, var(--navy-dark) 0%, var(--navy) 60%, #0066cc 100%);
      border-radius: 0 0 40px 40px;
      position: relative;
      overflow: hidden;
    }
    .profile-header-bg::before {
      content: '';
      position: absolute; top: 0; right: 0; width: 300px; height: 300px;
      background: radial-gradient(circle, rgba(234,116,27,0.12) 0%, transparent 70%);
      pointer-events: none;
    }
    .profile-header-bg::after {
      content: '';
      position: absolute; bottom: -20px; left: -20px; width: 200px; height: 200px;
      background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 70%);
      pointer-events: none;
    }

    /* Avatar ring animation */
    @keyframes rotateRing { to { transform: rotate(360deg); } }
    .avatar-ring {
      animation: rotateRing 30s linear infinite;
    }

    /* Floating badge animation */
    @keyframes floatBadge {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-4px); }
    }
    .float-badge { animation: floatBadge 3s ease-in-out infinite; }

    /* Pulse dot */
    @keyframes pulseDot {
      0%, 100% { box-shadow: 0 0 0 0 rgba(16,185,129,0.5); }
      50% { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
    }
    .pulse-active { animation: pulseDot 2s infinite; }

    /* Action button hover micro-interaction */
    .action-btn {
      transition: transform 0.3s var(--spring), box-shadow 0.3s, background 0.3s;
    }
    .action-btn:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
    }
    .action-btn:active {
      transform: translateY(0) scale(0.97);
    }

    /* Info card list row */
    .info-row {
      transition: background 0.3s, padding-left 0.3s var(--spring);
      border-radius: 14px;
      padding: 12px 14px;
      margin: -2px -4px;
    }
    .info-row:hover {
      background: rgba(0, 69, 145, 0.03);
      padding-left: 18px;
    }

    /* Toast */
    .toast-enter { opacity: 1 !important; transform: translateY(0) !important; }

    /* Responsive: Desktop centering shadow */
    @media (min-width: 520px) {
      .profile-wrapper {
        margin: 20px auto;
        border-radius: 32px;
        box-shadow: var(--shadow-lg);
        min-height: auto;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.04);
      }
      .profile-header-bg {
        border-radius: 0 0 40px 40px;
      }
    }
  </style>
</head>
<body>

  <!-- ═══ NFC INTRO OVERLAY ═══ -->
  <div id="intro-overlay">
    <!-- Subtle background pattern -->
    <div style="position:absolute;inset:0;opacity:0.03;pointer-events:none;">
      <svg width="100%" height="100%"><defs><pattern id="dg" width="32" height="32" patternUnits="userSpaceOnUse"><circle cx="1" cy="1" r="1" fill="#004591"/></pattern></defs><rect width="100%" height="100%" fill="url(#dg)"/></svg>
    </div>

    <!-- NFC Card Mockup -->
    <div id="intro-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div style="display:flex;align-items:center;gap:10px;">
          <img src="../Logo.png" alt="Logo" style="width:32px;height:32px;object-fit:contain;">
          <div>
            <div style="color:#fff;font-family:'Playfair Display',serif;font-size:13px;font-weight:700;">Mamun's <span style="color:#ea741b;">Ortho</span></div>
            <div style="color:rgba(255,255,255,0.35);font-size:6px;letter-spacing:3px;text-transform:uppercase;font-weight:700;margin-top:2px;">Dental Center</div>
          </div>
        </div>
        <svg style="width:22px;height:22px;color:#ea741b;transform:rotate(90deg);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 18a10 10 0 0 1 0-12"/><path d="M9 15a6 6 0 0 1 0-6"/><path d="M13 12a2 2 0 0 1 0-1"/></svg>
      </div>
      <div style="text-align:center;">
        <div style="color:#fff;font-family:'Playfair Display',serif;font-size:16px;font-weight:700;letter-spacing:0.5px;">Dr. Muhammad Shamim Al Mamun</div>
        <div style="color:rgba(234,116,27,0.8);font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:3px;margin-top:4px;">Associate Professor & Head</div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;font-size:7px;color:rgba(255,255,255,0.25);font-family:monospace;letter-spacing:3px;">
        <span>NFC DIGITAL IDENTITY</span>
        <span style="color:rgba(234,116,27,0.6);font-weight:700;">TAPYZON</span>
      </div>
    </div>

    <!-- Status -->
    <div style="margin-top:48px;display:flex;flex-direction:column;align-items:center;gap:12px;">
      <div style="display:flex;align-items:center;gap:8px;">
        <div style="width:10px;height:10px;border-radius:50%;background:var(--orange);animation:pulseDot 1.5s infinite;"></div>
        <span id="nfc-status" style="color:#64748b;font-weight:600;letter-spacing:3px;text-transform:uppercase;font-size:10px;">NFC Tag Linked...</span>
      </div>
      <p style="color:#94a3b8;font-size:9px;text-transform:uppercase;letter-spacing:3px;">Tapyzon Digital ID</p>
    </div>
  </div>

  <!-- ═══ MAIN PROFILE ═══ -->
  <div class="profile-wrapper" id="profile-main">

    <!-- ─── HEADER SECTION (Dark Rounded Top) ─── -->
    <div class="profile-header-bg" style="padding:24px 24px 80px;">
      <!-- Top bar -->
      <div class="reveal" style="display:flex;justify-content:space-between;align-items:center;">
        <a href="../index.php" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
          <img src="../Logo.png" alt="Logo" style="width:36px;height:36px;object-fit:contain;">
          <div>
            <div style="color:#fff;font-family:'Playfair Display',serif;font-size:15px;font-weight:700;">Mamun's <span style="color:#ea741b;">Ortho</span></div>
            <div style="color:rgba(255,255,255,0.35);font-size:7px;letter-spacing:2px;text-transform:uppercase;font-weight:700;">Dental Center</div>
          </div>
        </a>
        <a href="../index.php" style="padding:8px 16px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.12);border-radius:99px;color:rgba(255,255,255,0.75);font-size:10px;text-transform:uppercase;letter-spacing:2px;font-weight:700;text-decoration:none;transition:all 0.3s;">
          <i class="fas fa-house-chimney" style="margin-right:4px;font-size:8px;"></i> Home
        </a>
      </div>

      <!-- Avatar (Overlapping dark/light zones) -->
      <div class="reveal-scale" style="text-align:center;margin-top:36px;">
        <div style="position:relative;display:inline-block;">
          <!-- Outer glowing ring -->
          <svg class="avatar-ring" style="position:absolute;inset:-6px;width:calc(100% + 12px);height:calc(100% + 12px);" viewBox="0 0 140 140">
            <defs>
              <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#ea741b;stop-opacity:0.6"/>
                <stop offset="50%" style="stop-color:#004591;stop-opacity:0.3"/>
                <stop offset="100%" style="stop-color:#ea741b;stop-opacity:0.6"/>
              </linearGradient>
            </defs>
            <circle cx="70" cy="70" r="66" fill="none" stroke="url(#ringGrad)" stroke-width="1.5" stroke-dasharray="8 6"/>
          </svg>
          <div style="width:128px;height:128px;border-radius:50%;background:linear-gradient(135deg, #f0f4f8, #e2e8f0);border:4px solid rgba(255,255,255,0.6);display:flex;align-items:center;justify-content:center;box-shadow:0 12px 40px rgba(0,0,0,0.15);position:relative;">
            <span style="font-family:'Playfair Display',serif;font-size:42px;font-weight:700;color:var(--navy);user-select:none;">SM</span>
          </div>
          <!-- Verified badge -->
          <div class="float-badge" style="position:absolute;bottom:2px;right:2px;width:30px;height:30px;background:#fff;border:3px solid var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <i class="fas fa-circle-check" style="color:#10b981;font-size:12px;"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── NAME CARD (Floating over header/content boundary) ─── -->
    <div style="padding:0 20px;margin-top:-56px;position:relative;z-index:10;">
      <div class="reveal" style="background:var(--card);border-radius:24px;padding:28px 24px 24px;box-shadow:var(--shadow-md);border:1px solid var(--border);text-align:center;">
        <h1 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;color:var(--text);line-height:1.3;margin:0;">
          Dr. Muhammad Shamim<br>Al Mamun
        </h1>
        <p style="color:var(--orange);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-top:6px;">
          Consultant Orthodontist
        </p>
        <p style="color:var(--text-muted);font-size:11px;margin-top:4px;font-weight:500;">
          BDS (Dhaka Dental College) · FCPS (Orthodontics)
        </p>

        <!-- BMDC Badge -->
        <div style="display:inline-flex;align-items:center;gap:6px;margin-top:12px;padding:6px 14px;background:#f0f4f8;border:1px solid var(--border);border-radius:99px;">
          <span style="color:var(--text-muted);font-size:8px;text-transform:uppercase;letter-spacing:2px;font-weight:700;">BMDC Reg.</span>
          <span style="width:4px;height:4px;border-radius:50%;background:var(--orange);"></span>
          <span style="color:var(--navy);font-weight:800;font-size:11px;letter-spacing:1px;">A-2133</span>
        </div>

        <!-- Quick action pills row -->
        <div class="stagger-children" style="display:flex;gap:0;margin-top:20px;border-top:1px solid var(--border);padding-top:16px;">
          <button onclick="saveContact()" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;background:none;border:none;cursor:pointer;padding:8px 4px;border-radius:12px;transition:background 0.3s;" onmouseover="this.style.background='rgba(234,116,27,0.05)'" onmouseout="this.style.background='none'">
            <div style="width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#fff3e8,#ffe4cc);display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-user-plus" style="color:var(--orange);font-size:14px;"></i>
            </div>
            <span style="font-size:9px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">Save</span>
          </button>
          <a href="tel:+8801712718527" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;text-decoration:none;padding:8px 4px;border-radius:12px;transition:background 0.3s;" onmouseover="this.style.background='rgba(0,69,145,0.04)'" onmouseout="this.style.background='none'">
            <div style="width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#e8f0fa,#d0e0f5);display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-phone" style="color:var(--navy);font-size:14px;"></i>
            </div>
            <span style="font-size:9px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">Call</span>
          </a>
          <a href="https://wa.me/+8801712718527?text=Hello%2C%20I%20would%20like%20to%20book%20a%20consultation%20with%20Dr.%20Muhammad%20Shamim%20Al%20Mamun." target="_blank" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;text-decoration:none;padding:8px 4px;border-radius:12px;transition:background 0.3s;" onmouseover="this.style.background='rgba(37,211,102,0.05)'" onmouseout="this.style.background='none'">
            <div style="width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#e6faf0,#ccf5e0);display:flex;align-items:center;justify-content:center;">
              <i class="fab fa-whatsapp" style="color:#25D366;font-size:16px;"></i>
            </div>
            <span style="font-size:9px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">WhatsApp</span>
          </a>
          <button onclick="shareCard()" style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;background:none;border:none;cursor:pointer;padding:8px 4px;border-radius:12px;transition:background 0.3s;" onmouseover="this.style.background='rgba(0,69,145,0.04)'" onmouseout="this.style.background='none'">
            <div style="width:40px;height:40px;border-radius:14px;background:linear-gradient(135deg,#f0f0f8,#e0e0f0);display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-share-nodes" style="color:#6366f1;font-size:14px;"></i>
            </div>
            <span style="font-size:9px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;">Share</span>
          </button>
        </div>
      </div>
    </div>

    <!-- ─── SAVE CONTACT CTA ─── -->
    <div style="padding:0 20px;margin-top:16px;">
      <button onclick="saveContact()" class="reveal action-btn" style="width:100%;padding:16px;background:linear-gradient(135deg, var(--orange), var(--orange-dark));color:#fff;border:none;border-radius:18px;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:2px;cursor:pointer;box-shadow:0 6px 24px rgba(234,116,27,0.3);display:flex;align-items:center;justify-content:center;gap:10px;">
        <i class="fas fa-address-card" style="font-size:15px;"></i>
        Save Contact to Phone
      </button>
    </div>

    <!-- ─── INFO SECTIONS ─── -->
    <div style="padding:0 20px;margin-top:20px;display:flex;flex-direction:column;gap:16px;">

      <!-- Education & Credentials -->
      <div class="reveal" style="background:var(--card);border-radius:22px;padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
          <div style="width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,#fff3e8,#ffe4cc);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-graduation-cap" style="color:var(--orange);font-size:14px;"></i>
          </div>
          <h3 style="font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--text);">Education & Credentials</h3>
        </div>
        <div class="stagger-children" style="display:flex;flex-direction:column;gap:2px;">
          <div class="info-row" style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--orange);flex-shrink:0;margin-top:6px;"></div>
            <div>
              <p style="font-size:14px;font-weight:600;color:var(--text);">BDS (Bachelor of Dental Surgery)</p>
              <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">Dhaka Dental College</p>
            </div>
          </div>
          <div class="info-row" style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--navy);flex-shrink:0;margin-top:6px;"></div>
            <div>
              <p style="font-size:14px;font-weight:600;color:var(--text);">FCPS (Orthodontics)</p>
              <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">Bangladesh College of Physicians & Surgeons (BCPS)</p>
            </div>
          </div>
          <div class="info-row" style="display:flex;align-items:flex-start;gap:12px;">
            <div style="width:8px;height:8px;border-radius:50%;background:var(--orange);flex-shrink:0;margin-top:6px;"></div>
            <div>
              <p style="font-size:14px;font-weight:600;color:var(--text);">Special Trained in Implantology</p>
              <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">Advanced Dental Implant Surgery</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Professional Positions -->
      <div class="reveal" style="background:var(--card);border-radius:22px;padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
          <div style="width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,#e8f0fa,#d0e0f5);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-briefcase-medical" style="color:var(--navy);font-size:14px;"></i>
          </div>
          <h3 style="font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--text);">Professional Positions</h3>
        </div>
        <div class="stagger-children" style="display:flex;flex-direction:column;gap:2px;">
          <div class="info-row" style="display:flex;align-items:center;gap:12px;">
            <div style="width:32px;height:32px;border-radius:10px;background:#f0f4f8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-building-columns" style="color:var(--navy);font-size:12px;"></i>
            </div>
            <div style="flex:1;">
              <p style="font-size:13px;font-weight:600;color:var(--text);">Associate Professor & Head</p>
              <p style="font-size:11px;color:var(--text-muted);">Dept. of Orthodontics · Bangladesh Dental College</p>
            </div>
            <i class="fas fa-chevron-right" style="color:#cbd5e1;font-size:10px;"></i>
          </div>
          <div class="info-row" style="display:flex;align-items:center;gap:12px;">
            <div style="width:32px;height:32px;border-radius:10px;background:#f0f4f8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-hospital" style="color:var(--navy);font-size:12px;"></i>
            </div>
            <div style="flex:1;">
              <p style="font-size:13px;font-weight:600;color:var(--text);">Consultant Orthodontist</p>
              <p style="font-size:11px;color:var(--text-muted);">Labaid Hospital, Dhanmondi</p>
            </div>
            <i class="fas fa-chevron-right" style="color:#cbd5e1;font-size:10px;"></i>
          </div>
          <div class="info-row" style="display:flex;align-items:center;gap:12px;">
            <div style="width:32px;height:32px;border-radius:10px;background:#f0f4f8;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="fas fa-tooth" style="color:var(--orange);font-size:12px;"></i>
            </div>
            <div style="flex:1;">
              <p style="font-size:13px;font-weight:600;color:var(--text);">Founder & Lead Orthodontist</p>
              <p style="font-size:11px;color:var(--text-muted);">Mamun's Ortho Dental, Lalmatia</p>
            </div>
            <i class="fas fa-chevron-right" style="color:#cbd5e1;font-size:10px;"></i>
          </div>
        </div>
      </div>

      <!-- Clinic & Contact -->
      <div class="reveal" style="background:var(--card);border-radius:22px;padding:24px;box-shadow:var(--shadow-sm);border:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
          <div style="width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,#e6faf0,#ccf5e0);display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-location-dot" style="color:#10b981;font-size:14px;"></i>
          </div>
          <h3 style="font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--text);">Clinic Information</h3>
        </div>
        <div class="stagger-children" style="display:flex;flex-direction:column;gap:2px;">
          <a href="https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6" target="_blank" class="info-row" style="display:flex;align-items:flex-start;gap:12px;text-decoration:none;color:inherit;">
            <i class="fas fa-map-marker-alt" style="color:var(--orange);font-size:13px;margin-top:2px;width:16px;text-align:center;"></i>
            <p style="font-size:13px;color:var(--text);line-height:1.5;">5/2 (2nd Floor), Block A, Road 5, Lalmatia, Mohammadpur, Dhaka-1207</p>
          </a>
          <div class="info-row" style="display:flex;align-items:center;gap:12px;">
            <i class="fas fa-clock" style="color:var(--navy);font-size:13px;width:16px;text-align:center;"></i>
            <div>
              <p style="font-size:13px;color:var(--text);font-weight:500;">Saturday — Thursday</p>
              <p style="font-size:11px;color:var(--text-muted);">9:00 AM – 9:00 PM</p>
            </div>
          </div>
          <a href="tel:+8801712718527" class="info-row" style="display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;">
            <i class="fas fa-phone" style="color:var(--navy);font-size:13px;width:16px;text-align:center;"></i>
            <p style="font-size:13px;color:var(--text);font-weight:500;">+880 1712-718527</p>
          </a>
          <a href="mailto:mamunddcbdc@gmail.com" class="info-row" style="display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;">
            <i class="fas fa-envelope" style="color:var(--orange);font-size:13px;width:16px;text-align:center;"></i>
            <p style="font-size:13px;color:var(--text);font-weight:500;">mamunddcbdc@gmail.com</p>
          </a>
          <a href="https://mamunorthodental.com" target="_blank" class="info-row" style="display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;">
            <i class="fas fa-globe" style="color:var(--navy);font-size:13px;width:16px;text-align:center;"></i>
            <p style="font-size:13px;color:var(--navy);font-weight:600;">www.mamunorthodental.com</p>
          </a>
        </div>
      </div>

      <!-- Map Action -->
      <a href="https://maps.app.goo.gl/MJg2zb1qWj8xq2Uq6" target="_blank" class="reveal action-btn" style="display:flex;align-items:center;gap:14px;background:var(--card);border-radius:22px;padding:18px 24px;box-shadow:var(--shadow-sm);border:1px solid var(--border);text-decoration:none;color:inherit;">
        <div style="width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#fee2e2,#fecaca);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-map-location-dot" style="color:#ef4444;font-size:18px;"></i>
        </div>
        <div style="flex:1;">
          <p style="font-size:14px;font-weight:700;color:var(--text);">Get Directions</p>
          <p style="font-size:11px;color:var(--text-muted);margin-top:1px;">Open in Google Maps</p>
        </div>
        <i class="fas fa-arrow-up-right-from-square" style="color:var(--orange);font-size:12px;"></i>
      </a>

      <!-- Book Appointment -->
      <a href="../index.php#contact" class="reveal action-btn" style="display:flex;align-items:center;gap:14px;background:linear-gradient(135deg, var(--navy-dark), var(--navy));border-radius:22px;padding:18px 24px;box-shadow:0 8px 24px rgba(0,45,100,0.2);text-decoration:none;color:#fff;">
        <div style="width:44px;height:44px;border-radius:14px;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="fas fa-calendar-check" style="color:#ea741b;font-size:18px;"></i>
        </div>
        <div style="flex:1;">
          <p style="font-size:14px;font-weight:700;">Book an Appointment</p>
          <p style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:1px;">Schedule a visit online</p>
        </div>
        <i class="fas fa-arrow-right" style="color:rgba(255,255,255,0.4);font-size:12px;"></i>
      </a>
    </div>

    <!-- ─── FOOTER ─── -->
    <footer class="reveal" style="padding:32px 20px 28px;text-align:center;">
      <div style="width:48px;height:1px;background:linear-gradient(90deg,transparent,#cbd5e1,transparent);margin:0 auto 16px;"></div>
      <p style="color:#94a3b8;font-size:9px;text-transform:uppercase;letter-spacing:2px;font-weight:600;">
        &copy; <?=date('Y')?> Mamun's Ortho Dental
      </p>
      <a href="https://tapyzon.top" target="_blank" style="display:inline-flex;align-items:center;gap:4px;margin-top:8px;padding:5px 14px;background:rgba(0,69,145,0.04);border:1px solid var(--border);border-radius:99px;text-decoration:none;font-size:10px;color:#94a3b8;font-weight:600;transition:all 0.3s;" onmouseover="this.style.borderColor='rgba(234,116,27,0.3)'" onmouseout="this.style.borderColor='var(--border)'">
        Digital ID by <span style="color:var(--orange);font-weight:700;">Tapyzon</span>
        <span style="color:#cbd5e1;font-weight:400;"></span>
      </a>
    </footer>
  </div>

  <!-- Toast -->
  <div id="toast" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(16px);padding:14px 22px;background:#fff;border:1px solid var(--border);border-radius:16px;display:flex;align-items:center;gap:8px;box-shadow:var(--shadow-lg);opacity:0;transition:all 0.35s var(--spring);pointer-events:none;z-index:100;">
    <i class="fas fa-circle-check" style="color:#10b981;font-size:14px;"></i>
    <span class="toast-text" style="font-size:12px;font-weight:700;color:var(--text);">Link Copied!</span>
  </div>

  <script>
    /* ═══ INTRO ANIMATION ═══ */
    document.addEventListener("DOMContentLoaded", () => {
      const intro = document.getElementById("intro-overlay");
      const status = document.getElementById("nfc-status");

      setTimeout(() => { status.textContent = "Syncing Digital ID..."; }, 600);
      setTimeout(() => {
        status.textContent = "Welcome";
        intro.classList.add("fade-out");
        setTimeout(() => { intro.remove(); }, 900);
      }, 1400);

      /* ═══ SCROLL-TRIGGERED REVEAL (IntersectionObserver) ═══ */
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
          }
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

      document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .stagger-children').forEach(el => {
        observer.observe(el);
      });
    });

    /* ═══ vCARD DOWNLOAD ═══ */
    function saveContact() {
      const vcard = `BEGIN:VCARD
VERSION:3.0
N:Al Mamun;Muhammad Shamim;Dr.;;
FN:Dr. Muhammad Shamim Al Mamun
ORG:Mamun's Ortho Dental
TITLE:Associate Professor & Head, Dept. of Orthodontics
TEL;TYPE=CELL,VOICE:+8801712718527
EMAIL;TYPE=PREF,INTERNET:mamunddcbdc@gmail.com
URL:https://mamunorthodental.com/dr-shamim-al-mamun.php
ADR;TYPE=WORK:;;5/2 (2nd Floor), Block A, Road 5, Lalmatia;Mohammadpur;Dhaka;1207;Bangladesh
END:VCARD`;

      const blob = new Blob([vcard], { type: 'text/vcard;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'Dr_Shamim_Al_Mamun.vcf';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    /* ═══ SHARE CARD ═══ */
    function shareCard() {
      const shareData = {
        title: 'Dr. Muhammad Shamim Al Mamun - Digital Identity',
        text: 'Save contact and book appointment with Dr. Muhammad Shamim Al Mamun.',
        url: window.location.href
      };
      if (navigator.share) {
        navigator.share(shareData).catch(() => {});
      } else {
        navigator.clipboard.writeText(window.location.href).then(() => showToast('Link copied to clipboard!')).catch(() => {});
      }
    }

    /* ═══ TOAST ═══ */
    function showToast(msg) {
      const t = document.getElementById('toast');
      t.querySelector('.toast-text').textContent = msg;
      t.style.opacity = '1';
      t.style.transform = 'translateX(-50%) translateY(0)';
      setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateX(-50%) translateY(16px)';
      }, 2200);
    }
  </script>
</body>
</html>
