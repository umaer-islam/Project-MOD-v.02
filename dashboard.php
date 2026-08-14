<?php
$load_charts = true;
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';
require_once 'components/cache.php';

// ── Safe DB queries ─────────────────────────────────────────────────────────
$todayPatients = 0; $totalPatients = 0; $paymentsThisWeek = 0; $pendingFollowups = 0;
$recentPatients = []; $todayAppointments = []; $activeNotices = [];
$newMessages = 0; $pendingReviews = 0;
$monthlyRevenue = 0; $lastMonthRevenue = 0;
$genderMale = 0; $genderFemale = 0; $genderOther = 0;
$revenueLabels = []; $revenueData = [];
$upcomingAppointments = [];
$db_connected = ($pdo !== null);

if ($db_connected) {
    try {
        // ── Core KPIs (cached 60s) ──
        $todayPatients    = cache_remember('dash:today_patients', 60, fn() => $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status != 'Cancelled'")->fetchColumn() ?: 0);
        $totalPatients    = cache_remember('dash:total_patients', 120, fn() => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn() ?: 0);
        $paymentsThisWeek = cache_remember('dash:payments_week', 60, fn() => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn() ?: 0);
        $pendingFollowups = 0;
        $newMessages      = cache_remember('dash:unread_msgs', 30, fn() => $pdo->query("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'unread'")->fetchColumn() ?: 0);
        $pendingReviews   = cache_remember('dash:pending_reviews', 60, fn() => $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'Pending'")->fetchColumn() ?: 0);

        // ── Revenue Comparison (This Month vs Last Month) ──
        $monthlyRevenue   = cache_remember('dash:monthly_rev', 120, fn() => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")->fetchColumn() ?: 0);
        $lastMonthRevenue = cache_remember('dash:last_month_rev', 120, fn() => $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))")->fetchColumn() ?: 0);

        // ── Patient Gender Distribution ──
        $genderMale   = cache_remember('dash:gender_male', 300, fn() => $pdo->query("SELECT COUNT(*) FROM patients WHERE gender = 'Male'")->fetchColumn() ?: 0);
        $genderFemale = cache_remember('dash:gender_female', 300, fn() => $pdo->query("SELECT COUNT(*) FROM patients WHERE gender = 'Female'")->fetchColumn() ?: 0);
        $genderOther  = cache_remember('dash:gender_other', 300, fn() => $pdo->query("SELECT COUNT(*) FROM patients WHERE gender NOT IN ('Male','Female') OR gender IS NULL")->fetchColumn() ?: 0);

        // ── Revenue Last 6 Months ──
        $revenueData = cache_remember('dash:revenue_6mo', 300, function () use ($pdo) {
            $data = [];
            for ($i = 5; $i >= 0; $i--) {
                $m = date('Y-m', strtotime("-$i months"));
                $rev = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE YEAR(created_at) = YEAR('{$m}-01') AND MONTH(created_at) = MONTH('{$m}-01')")->fetchColumn() ?: 0;
                $data[] = (int)$rev;
            }
            return $data;
        });
        $revenueLabels = [];
        for ($i = 5; $i >= 0; $i--) {
            $revenueLabels[] = date('M', strtotime("-$i months"));
        }

        // ── Recent Patients (cached 60s) ──
        $recentPatients = cache_remember('dash:recent_patients', 60, fn() => $pdo->query("SELECT name, patient_id, created_at FROM patients ORDER BY created_at DESC LIMIT 5")->fetchAll());

        // ── Today's Appointments (cached 30s) ──
        $todayAppointments = cache_remember('dash:today_apts', 30, fn() => $pdo->query(
            "SELECT a.appointment_time, a.status, p.name as patient_name FROM appointments a 
             JOIN patients p ON a.patient_id = p.id 
             WHERE a.appointment_date = CURDATE() ORDER BY a.appointment_time ASC LIMIT 8"
        )->fetchAll());

        // ── Upcoming Appointments (Next 3 days, cached 60s) ──
        $upcomingAppointments = cache_remember('dash:upcoming_apts', 60, fn() => $pdo->query(
            "SELECT a.appointment_date, a.appointment_time, a.status, p.name as patient_name 
             FROM appointments a JOIN patients p ON a.patient_id = p.id 
             WHERE a.appointment_date > CURDATE() AND a.appointment_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) 
             AND a.status != 'Cancelled' ORDER BY a.appointment_date ASC, a.appointment_time ASC LIMIT 6"
        )->fetchAll());

        // ── Active Notices (cached 300s) ──
        $activeNotices = cache_remember('dash:notices', 300, fn() => $pdo->query(
            "SELECT title, description FROM announcements 
             WHERE (expiry_date IS NULL OR expiry_date >= CURDATE()) ORDER BY date_posted DESC LIMIT 3"
        )->fetchAll());



    } catch (PDOException $e) { $db_connected = false; }
}

$hour = (int)date('H');
$greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
$revenueTrend = $lastMonthRevenue > 0 ? round((($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100) : 0;
$revenueUp = $revenueTrend >= 0;
?>

<main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <!-- DB Error Banner -->
    <?php if (!$db_connected): ?>
    <div class="mb-6 flex items-start gap-4 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4" style="animation: fadeSlideUp 0.5s ease forwards;">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
            <i class="fas fa-database text-amber-600"></i>
        </div>
        <div>
            <p class="font-bold text-amber-800">Database Unavailable</p>
            <p class="text-amber-700 text-sm mt-0.5">Could not connect to the database. Showing placeholder stats.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 1: GREETING HERO
         ═══════════════════════════════════════════════════════════ -->
    <div class="relative mb-8 rounded-[28px] overflow-hidden" style="animation: fadeSlideUp 0.6s cubic-bezier(0.16,1,0.3,1) forwards;">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#004591] via-[#003570] to-[#042A5E]"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(rgba(255,255,255,0.8) 1px, transparent 1px); background-size: 20px 20px;"></div>
        <!-- Floating orbs -->
        <div class="absolute -top-20 -right-20 w-64 h-64 bg-[#ea741b]/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-white/5 rounded-full blur-2xl"></div>

        <div class="relative z-10 px-6 sm:px-8 py-8 sm:py-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-full backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse shadow-[0_0_8px_rgba(74,222,128,0.6)]"></span>
                        <span class="text-[9px] text-white/70 font-bold uppercase tracking-[0.2em]">System Online</span>
                    </div>
                    <span class="text-[10px] text-white/40 font-bold"><?= date('M d, Y') ?></span>
                </div>
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-white mb-2">
                    <?= $greeting ?>, <span class="text-[#ea741b]"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Doctor') ?></span>
                </h1>
                <p class="text-white/50 text-sm max-w-lg">Here's what's happening at your clinic today. You have <span class="text-white/80 font-semibold"><?= $todayPatients ?> appointment<?= $todayPatients != 1 ? 's' : '' ?></span> scheduled.</p>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-center flex-shrink-0">
                <button id="privacyToggle" class="w-11 h-11 rounded-xl bg-white/10 text-white/60 hover:bg-white/20 hover:text-white transition-all flex items-center justify-center backdrop-blur-sm" title="Toggle Privacy Mode">
                    <i class="fas fa-eye text-sm"></i>
                </button>
                <a href="appointments.php" class="inline-flex items-center gap-2 px-6 py-3.5 bg-[#ea741b] hover:bg-[#cf5e0e] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#ea741b]/30 transition-all duration-300 hover:shadow-xl hover:shadow-[#ea741b]/40 hover:-translate-y-0.5">
                    <i class="fas fa-calendar-plus text-xs"></i> New Appointment
                </a>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 2: KPI CARDS WITH ANIMATED COUNTERS
         ═══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <!-- Total Patients -->
        <div class="group relative bg-white rounded-[20px] border border-gray-100 p-5 hover:border-[#004591]/20 transition-all duration-500 hover:shadow-[0_8px_30px_rgba(0,69,145,0.08)] hover:-translate-y-1" style="animation: fadeSlideUp 0.6s 0.1s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-2xl bg-[#e8f0fa] flex items-center justify-center group-hover:bg-[#004591] group-hover:shadow-[0_4px_15px_rgba(0,69,145,0.3)] transition-all duration-500">
                    <i class="fas fa-users text-[#004591] text-sm group-hover:text-white transition-colors"></i>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-widest text-green-600 bg-green-50 px-2 py-1 rounded-full">Total</span>
            </div>
            <p class="font-serif text-3xl lg:text-4xl font-bold text-[#004591] counter" data-target="<?= $totalPatients ?>">0</p>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mt-1.5">Registered Patients</p>
            <!-- Mini sparkline -->
            <div class="absolute bottom-0 left-0 right-0 h-12 overflow-hidden rounded-b-[20px] opacity-30">
                <svg viewBox="0 0 200 40" class="w-full h-full" preserveAspectRatio="none">
                    <polyline fill="none" stroke="#004591" stroke-width="2" points="0,35 30,28 60,32 90,20 120,25 150,15 180,18 200,10" />
                    <polyline fill="url(#sparkGrad1)" stroke="none" points="0,40 0,35 30,28 60,32 90,20 120,25 150,15 180,18 200,10 200,40" />
                    <defs><linearGradient id="sparkGrad1" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#004591" stop-opacity="0.3"/><stop offset="100%" stop-color="#004591" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="group relative bg-white rounded-[20px] border border-gray-100 p-5 hover:border-[#ea741b]/20 transition-all duration-500 hover:shadow-[0_8px_30px_rgba(234,116,27,0.08)] hover:-translate-y-1" style="animation: fadeSlideUp 0.6s 0.2s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-2xl bg-[#ea741b]/10 flex items-center justify-center group-hover:bg-[#ea741b] group-hover:shadow-[0_4px_15px_rgba(234,116,27,0.3)] transition-all duration-500">
                    <i class="fas fa-calendar-check text-[#ea741b] text-sm group-hover:text-white transition-colors"></i>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-widest text-[#ea741b] bg-[#ea741b]/10 px-2 py-1 rounded-full">Today</span>
            </div>
            <p class="font-serif text-3xl lg:text-4xl font-bold text-[#004591] counter" data-target="<?= $todayPatients ?>">0</p>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mt-1.5">Appointments Today</p>
            <div class="absolute bottom-0 left-0 right-0 h-12 overflow-hidden rounded-b-[20px] opacity-30">
                <svg viewBox="0 0 200 40" class="w-full h-full" preserveAspectRatio="none">
                    <polyline fill="none" stroke="#ea741b" stroke-width="2" points="0,30 30,25 60,35 90,18 120,22 150,12 180,20 200,8" />
                    <polyline fill="url(#sparkGrad2)" stroke="none" points="0,40 0,30 30,25 60,35 90,18 120,22 150,12 180,20 200,8 200,40" />
                    <defs><linearGradient id="sparkGrad2" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#ea741b" stop-opacity="0.3"/><stop offset="100%" stop-color="#ea741b" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>
        </div>

        <!-- Revenue This Month -->
        <div class="group relative bg-white rounded-[20px] border border-gray-100 p-5 hover:border-green-200 transition-all duration-500 hover:shadow-[0_8px_30px_rgba(34,197,94,0.08)] hover:-translate-y-1" style="animation: fadeSlideUp 0.6s 0.3s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-2xl bg-green-50 flex items-center justify-center group-hover:bg-green-500 group-hover:shadow-[0_4px_15px_rgba(34,197,94,0.3)] transition-all duration-500">
                    <i class="fas fa-taka-sign text-green-600 text-sm group-hover:text-white transition-colors"></i>
                </div>
                <span class="text-[9px] font-bold uppercase tracking-widest <?= $revenueUp ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50' ?> px-2 py-1 rounded-full flex items-center gap-1">
                    <i class="fas fa-arrow-<?= $revenueUp ? 'up' : 'down' ?> text-[7px]"></i><?= abs($revenueTrend) ?>%
                </span>
            </div>
            <p class="font-serif text-2xl lg:text-3xl font-bold text-[#004591]"><span class="text-base text-gray-400">৳</span><span class="counter" data-target="<?= $monthlyRevenue ?>">0</span></p>
            <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mt-1.5">Revenue This Month</p>
            <div class="absolute bottom-0 left-0 right-0 h-12 overflow-hidden rounded-b-[20px] opacity-30">
                <svg viewBox="0 0 200 40" class="w-full h-full" preserveAspectRatio="none">
                    <polyline fill="none" stroke="#22c55e" stroke-width="2" points="0,32 30,28 60,30 90,22 120,18 150,20 180,12 200,10" />
                    <polyline fill="url(#sparkGrad3)" stroke="none" points="0,40 0,32 30,28 60,30 90,22 120,18 150,20 180,12 200,10 200,40" />
                    <defs><linearGradient id="sparkGrad3" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#22c55e" stop-opacity="0.3"/><stop offset="100%" stop-color="#22c55e" stop-opacity="0"/></linearGradient></defs>
                </svg>
            </div>
        </div>

        <!-- Pending Follow-ups (Accent Card) -->
        <div class="group relative bg-gradient-to-br from-[#004591] to-[#042A5E] rounded-[20px] p-5 border border-white/10 shadow-[0_8px_30px_rgba(0,69,145,0.25)] hover:shadow-[0_12px_40px_rgba(0,69,145,0.35)] hover:-translate-y-1 transition-all duration-500 overflow-hidden" style="animation: fadeSlideUp 0.6s 0.4s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="absolute inset-0 opacity-10 mix-blend-overlay bg-[url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22n%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.65%22 numOctaves=%223%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23n)%22/%3E%3C/svg%3E')]"></div>
            <div class="absolute -right-6 -bottom-6 opacity-10 group-hover:opacity-20 transition-opacity group-hover:scale-110 duration-500">
                <i class="fas fa-clock text-white text-7xl"></i>
            </div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-11 h-11 rounded-2xl bg-white/15 flex items-center justify-center">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-white/50 bg-white/10 px-2 py-1 rounded-full">7 days</span>
                </div>
                <p class="font-serif text-3xl lg:text-4xl font-bold text-white counter" data-target="<?= $pendingFollowups ?>">0</p>
                <p class="text-white/50 text-[10px] font-bold uppercase tracking-widest mt-1.5">Pending Follow-ups</p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 3: SMART INSIGHTS RIBBON
         ═══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8" style="animation: fadeSlideUp 0.6s 0.5s cubic-bezier(0.16,1,0.3,1) both;">
        <!-- Insight 1: Unread Messages -->
        <a href="messages.php" class="group flex items-center gap-4 bg-white rounded-[18px] border border-gray-100 p-4 hover:border-[#004591]/20 hover:shadow-[0_4px_20px_rgba(0,69,145,0.06)] transition-all duration-300">
            <div class="w-10 h-10 rounded-xl <?= $newMessages > 0 ? 'bg-[#004591]/10' : 'bg-gray-50' ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-envelope-open-text text-sm <?= $newMessages > 0 ? 'text-[#004591]' : 'text-gray-300' ?>"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-gray-700 group-hover:text-[#004591] transition-colors">Messages</p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                    <?php if ($newMessages > 0): ?>
                        <span class="text-[#ea741b]"><?= $newMessages ?> unread</span>
                    <?php else: ?>
                        All caught up
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($newMessages > 0): ?>
                <span class="w-2 h-2 rounded-full bg-[#ea741b] animate-pulse flex-shrink-0"></span>
            <?php endif; ?>
            <i class="fas fa-chevron-right text-[9px] text-gray-300 group-hover:text-[#ea741b] transition-colors"></i>
        </a>

        <!-- Insight 2: Pending Reviews -->
        <a href="testimonials.php" class="group flex items-center gap-4 bg-white rounded-[18px] border border-gray-100 p-4 hover:border-[#ea741b]/20 hover:shadow-[0_4px_20px_rgba(234,116,27,0.06)] transition-all duration-300">
            <div class="w-10 h-10 rounded-xl <?= $pendingReviews > 0 ? 'bg-[#ea741b]/10' : 'bg-gray-50' ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                <i class="fas fa-star text-sm <?= $pendingReviews > 0 ? 'text-[#ea741b]' : 'text-gray-300' ?>"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm text-gray-700 group-hover:text-[#ea741b] transition-colors">Reviews</p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                    <?php if ($pendingReviews > 0): ?>
                        <span class="text-[#ea741b]"><?= $pendingReviews ?> pending</span>
                    <?php else: ?>
                        No pending reviews
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($pendingReviews > 0): ?>
                <span class="w-2 h-2 rounded-full bg-[#ea741b] animate-pulse flex-shrink-0"></span>
            <?php endif; ?>
            <i class="fas fa-chevron-right text-[9px] text-gray-300 group-hover:text-[#ea741b] transition-colors"></i>
        </a>

    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 4: BENTO GRID — CHARTS + APPOINTMENTS + ACTIVITY
         ═══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 gap-6 mb-8">

        <!-- ── Revenue Chart (Full Width) ── -->
        <div class="bg-white rounded-[20px] border border-gray-100 p-6 hover:shadow-[0_8px_30px_rgba(0,69,145,0.06)] transition-all duration-500" style="animation: fadeSlideUp 0.6s 0.6s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Analytics</p>
                    <h3 class="font-serif text-lg text-[#004591] font-bold">Revenue Trend</h3>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#004591]"></span>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Monthly</span>
                    </div>
                </div>
            </div>
            <div class="w-full h-[260px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 5: APPOINTMENTS + QUICK ACTIONS
         ═══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-8">

        <!-- ── Today's Appointments (7 cols) ── -->
        <div class="xl:col-span-7 bg-white rounded-[20px] border border-gray-100 overflow-hidden hover:shadow-[0_8px_30px_rgba(0,69,145,0.06)] transition-all duration-500" style="animation: fadeSlideUp 0.6s 0.8s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Schedule</p>
                    <h3 class="font-serif text-lg text-[#004591] font-bold">Today's Appointments</h3>
                </div>
                <a href="appointments.php" class="text-[10px] font-bold uppercase tracking-widest text-[#004591] hover:text-[#ea741b] transition-colors flex items-center gap-1">
                    View All <i class="fas fa-arrow-right text-[8px]"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-50 max-h-[380px] overflow-y-auto custom-scrollbar">
                <?php if (count($todayAppointments) > 0): ?>
                    <?php foreach($todayAppointments as $idx => $appt):
                        $statusColors = [
                            'Scheduled'    => 'bg-blue-50 text-blue-600',
                            'Waiting'      => 'bg-amber-50 text-amber-600',
                            'In Treatment' => 'bg-purple-50 text-purple-600',
                            'Completed'    => 'bg-green-50 text-green-600',
                            'Follow-Up'    => 'bg-indigo-50 text-indigo-600',
                            'Cancelled'    => 'bg-red-50 text-red-500',
                        ];
                        $sc = $statusColors[$appt['status']] ?? 'bg-gray-100 text-gray-500';
                        $timeStr = date('h:i A', strtotime($appt['appointment_time']));
                        $isNow = (date('H:i') >= date('H:i', strtotime($appt['appointment_time'] . ' -15 minutes')) && date('H:i') <= date('H:i', strtotime($appt['appointment_time'] . ' +45 minutes')));
                    ?>
                    <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-[#F8FAFD] transition-colors group">
                        <div class="relative flex-shrink-0">
                            <div class="w-10 h-10 rounded-full <?= $isNow ? 'bg-[#ea741b]/10 ring-2 ring-[#ea741b]/30' : 'bg-[#e8f0fa]' ?> flex items-center justify-center text-[#004591] font-bold text-sm transition-all">
                                <?= strtoupper(substr($appt['patient_name'], 0, 1)) ?>
                            </div>
                            <?php if ($isNow): ?>
                                <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-green-500 rounded-full border-2 border-white animate-pulse"></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[#004591] text-sm truncate group-hover:text-[#ea741b] transition-colors"><?= htmlspecialchars($appt['patient_name']) ?></p>
                            <p class="text-gray-400 text-xs mt-0.5"><i class="far fa-clock text-[#ea741b] mr-1"></i><?= $timeStr ?></p>
                        </div>
                        <span class="text-[8px] font-bold px-2 py-0.5 rounded-full uppercase tracking-widest <?= $sc ?> flex-shrink-0"><?= $appt['status'] ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                        <div class="w-14 h-14 rounded-full bg-[#F0F4FA] flex items-center justify-center mb-3">
                            <i class="fas fa-calendar-check text-gray-300 text-xl"></i>
                        </div>
                        <p class="text-gray-400 font-semibold text-sm">No appointments today</p>
                        <a href="appointments.php" class="mt-3 text-[10px] text-[#004591] font-bold uppercase tracking-widest hover:text-[#ea741b] transition-colors">+ Schedule One</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Quick Actions + Notices (5 cols) ── -->
        <div class="xl:col-span-5 flex flex-col gap-5" style="animation: fadeSlideUp 0.6s 1s cubic-bezier(0.16,1,0.3,1) both;">

            <!-- Quick Actions -->
            <div class="bg-white rounded-[20px] border border-gray-100 p-5 hover:shadow-[0_8px_30px_rgba(0,69,145,0.06)] transition-all duration-500">
                <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-4">Quick Actions</p>
                <div class="grid grid-cols-2 gap-2">
                    <?php
                    $actions = [
                        ['label'=>'Patient',    'href'=>'patients.php',     'icon'=>'fa-user-plus',       'color'=>'text-[#004591] bg-[#e8f0fa] hover:bg-[#004591] hover:text-white'],
                        ['label'=>'Appointment','href'=>'appointments.php', 'icon'=>'fa-calendar-plus',   'color'=>'text-[#ea741b] bg-[#ea741b]/10 hover:bg-[#ea741b] hover:text-white'],
                        ['label'=>'Cash Memo', 'href'=>'cash_memos.php', 'icon'=>'fa-money-bill-wave', 'color'=>'text-purple-600 bg-purple-50 hover:bg-purple-500 hover:text-white'],
                        ['label'=>'Prescribe',  'href'=>'create_prescription.php','icon'=>'fa-pills',    'color'=>'text-rose-600 bg-rose-50 hover:bg-rose-500 hover:text-white'],
                        ['label'=>'Messages',   'href'=>'messages.php',     'icon'=>'fa-envelope',        'color'=>'text-sky-600 bg-sky-50 hover:bg-sky-500 hover:text-white'],
                    ];
                    foreach($actions as $a):
                    ?>
                    <a href="<?= $a['href'] ?>" class="flex flex-col items-center gap-2 py-3 rounded-xl <?= $a['color'] ?> transition-all duration-300 group hover:-translate-y-0.5 hover:shadow-md">
                        <i class="fas <?= $a['icon'] ?> text-base group-hover:scale-110 transition-transform"></i>
                        <span class="text-[9px] font-bold uppercase tracking-widest"><?= $a['label'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Active Notices -->
            <?php if (count($activeNotices) > 0): ?>
            <div class="bg-gradient-to-br from-[#004591] to-[#042A5E] rounded-[20px] p-5 border border-white/10 shadow-[0_8px_25px_rgba(0,69,145,0.2)] flex-1">
                <p class="text-[10px] uppercase tracking-widest text-white/40 font-bold mb-4">Notices</p>
                <div class="space-y-2.5">
                    <?php foreach($activeNotices as $n): ?>
                    <div class="bg-white/10 rounded-xl p-3.5 hover:bg-white/15 transition-colors">
                        <p class="text-white font-semibold text-xs leading-snug"><?= htmlspecialchars($n['title']) ?></p>
                        <p class="text-white/50 text-[10px] mt-1 line-clamp-2"><?= htmlspecialchars(substr($n['description'] ?? '', 0, 60)) ?>...</p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="announcements.php" class="mt-3 block text-center text-[9px] font-bold uppercase tracking-widest text-white/40 hover:text-white transition-colors">View All →</a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 6: RECENT PATIENTS + UPCOMING + DEMOGRAPHICS
         ═══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">

        <!-- ── Recent Patients (5 cols) ── -->
        <div class="xl:col-span-5 bg-white rounded-[20px] border border-gray-100 overflow-hidden hover:shadow-[0_8px_30px_rgba(0,69,145,0.06)] transition-all duration-500" style="animation: fadeSlideUp 0.6s 1.1s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Latest</p>
                    <h3 class="font-serif text-lg text-[#004591] font-bold">Recent Patients</h3>
                </div>
                <a href="patients.php" class="text-[10px] font-bold uppercase tracking-widest text-[#004591] hover:text-[#ea741b] transition-colors flex items-center gap-1">
                    View All <i class="fas fa-arrow-right text-[8px]"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                <?php if (count($recentPatients) > 0): ?>
                    <?php foreach($recentPatients as $p): ?>
                    <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-[#F8FAFD] transition-colors group">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#e8f0fa] to-[#d0e0f5] flex items-center justify-center text-[#004591] font-bold text-sm flex-shrink-0 group-hover:from-[#004591] group-hover:to-[#003570] group-hover:text-white transition-all duration-300">
                            <?= strtoupper(substr($p['name'], 0, 1)) ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-[#004591] text-sm truncate group-hover:text-[#ea741b] transition-colors"><?= htmlspecialchars($p['name']) ?></p>
                            <p class="text-gray-400 text-xs mt-0.5"><span class="privacy-sensitive"><?= htmlspecialchars($p['patient_id']) ?></span></p>
                        </div>
                        <span class="text-[10px] text-gray-400 flex-shrink-0"><?= date('M d', strtotime($p['created_at'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-14 text-center">
                        <p class="text-gray-400 font-semibold text-sm">No patients yet. <a href="patients.php" class="text-[#004591] underline">Add the first one.</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Upcoming Appointments (4 cols) ── -->
        <div class="xl:col-span-4 bg-white rounded-[20px] border border-gray-100 overflow-hidden hover:shadow-[0_8px_30px_rgba(0,69,145,0.06)] transition-all duration-500" style="animation: fadeSlideUp 0.6s 1.2s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-50">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Upcoming</p>
                    <h3 class="font-serif text-lg text-[#004591] font-bold">Next 3 Days</h3>
                </div>
                <a href="appointments.php" class="text-[10px] font-bold uppercase tracking-widest text-[#004591] hover:text-[#ea741b] transition-colors flex items-center gap-1">
                    View All <i class="fas fa-arrow-right text-[8px]"></i>
                </a>
            </div>
            <div class="divide-y divide-gray-50 max-h-[340px] overflow-y-auto custom-scrollbar">
                <?php if (count($upcomingAppointments) > 0): ?>
                    <?php foreach($upcomingAppointments as $ua):
                        $dayLabel = '';
                        $apptDate = strtotime($ua['appointment_date']);
                        $today = strtotime(date('Y-m-d'));
                        $diffDays = round(($apptDate - $today) / 86400);
                        if ($diffDays == 1) $dayLabel = 'Tomorrow';
                        elseif ($diffDays == 2) $dayLabel = 'In 2 days';
                        else $dayLabel = date('D, M d', $apptDate);
                    ?>
                    <div class="px-6 py-3.5 hover:bg-[#F8FAFD] transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-[9px] font-bold uppercase tracking-widest text-[#ea741b]"><?= $dayLabel ?></span>
                            <span class="text-[9px] text-gray-400 font-bold"><?= date('h:i A', strtotime($ua['appointment_time'])) ?></span>
                        </div>
                        <p class="font-semibold text-[#004591] text-sm"><?= htmlspecialchars($ua['patient_name']) ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="px-6 py-14 text-center">
                        <p class="text-gray-400 font-semibold text-sm">No upcoming appointments</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Patient Demographics (3 cols) ── -->
        <div class="xl:col-span-3 bg-white rounded-[20px] border border-gray-100 p-5 hover:shadow-[0_8px_30px_rgba(0,69,145,0.06)] transition-all duration-500" style="animation: fadeSlideUp 0.6s 1.3s cubic-bezier(0.16,1,0.3,1) both;">
            <div class="mb-5">
                <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Demographics</p>
                <h3 class="font-serif text-lg text-[#004591] font-bold">Patient Gender</h3>
            </div>

            <?php
            $totalG = max($genderMale + $genderFemale + $genderOther, 1);
            $malePct = round(($genderMale / $totalG) * 100);
            $femalePct = round(($genderFemale / $totalG) * 100);
            $otherPct = 100 - $malePct - $femalePct;
            ?>

            <!-- Circular Progress -->
            <div class="flex justify-center mb-5">
                <div class="relative w-28 h-28">
                    <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#f1f5f9" stroke-width="10"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#004591" stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="<?= $malePct * 3.267 ?> <?= (100 - $malePct) * 3.267 ?>"
                            class="transition-all duration-1000 ease-out" style="filter: drop-shadow(0 0 4px rgba(0,69,145,0.3));"/>
                        <circle cx="60" cy="60" r="52" fill="none" stroke="#ea741b" stroke-width="10" stroke-linecap="round"
                            stroke-dasharray="<?= $femalePct * 3.267 ?> <?= (100 - $femalePct) * 3.267 ?>"
                            stroke-dashoffset="-<?= $malePct * 3.267 ?>"
                            class="transition-all duration-1000 ease-out" style="filter: drop-shadow(0 0 4px rgba(234,116,27,0.3));"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <p class="font-serif text-2xl font-bold text-[#004591]"><?= number_format($totalPatients) ?></p>
                        <p class="text-[8px] text-gray-400 font-bold uppercase tracking-widest">Total</p>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#004591]"></span>
                        <span class="text-xs text-gray-600">Male</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#004591]"><?= $malePct ?>%</span>
                        <span class="text-[10px] text-gray-400">(<?= $genderMale ?>)</span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#ea741b]"></span>
                        <span class="text-xs text-gray-600">Female</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#ea741b]"><?= $femalePct ?>%</span>
                        <span class="text-[10px] text-gray-400">(<?= $genderFemale ?>)</span>
                    </div>
                </div>
                <?php if ($genderOther > 0): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                        <span class="text-xs text-gray-600">Other</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-400"><?= $otherPct ?>%</span>
                        <span class="text-[10px] text-gray-400">(<?= $genderOther ?>)</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</main>

<!-- ═══════════════════════════════════════════════════════════════════════════
     DASHBOARD JAVASCRIPT — Charts, Counters, Privacy
     ═══════════════════════════════════════════════════════════════════════════ -->
<script>
document.addEventListener("DOMContentLoaded", function() {

    // ── 1. Animated Counters ──
    const counters = document.querySelectorAll('.counter');
    const animateCounter = (el) => {
        const target = parseInt(el.dataset.target) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        const duration = 1500;
        const start = performance.now();
        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = target.toLocaleString();
        };
        requestAnimationFrame(step);
    };

    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    counters.forEach(c => counterObserver.observe(c));

    // ── 2. Revenue Bar Chart ──
    const revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($revenueLabels) ?>,
                datasets: [{
                    label: 'Revenue (৳)',
                    data: <?= json_encode($revenueData) ?>,
                    backgroundColor: (ctx) => {
                        const chart = ctx.chart;
                        const {ctx: c, chartArea} = chart;
                        if (!chartArea) return 'rgba(0,69,145,0.7)';
                        const gradient = c.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, 'rgba(0,69,145,0.15)');
                        gradient.addColorStop(1, 'rgba(0,69,145,0.8)');
                        return gradient;
                    },
                    borderColor: '#004591',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    hoverBackgroundColor: '#ea741b',
                    barPercentage: 0.6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 1200, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#004591',
                        bodyColor: '#64748b',
                        borderColor: '#f1f5f9',
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 14,
                        displayColors: false,
                        titleFont: { weight: '700', size: 13 },
                        callbacks: {
                            label: (c) => '৳ ' + c.parsed.y.toLocaleString(),
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#94a3b8', font: {size: 10, weight:'600'}, callback: v => '৳' + (v >= 1000 ? (v/1000) + 'k' : v) },
                        grid: { color: 'rgba(0,69,145,0.04)', drawBorder: false },
                        border: { display: false }
                    },
                    x: {
                        ticks: { color: '#94a3b8', font: {size: 10, weight:'600'} },
                        grid: { display: false },
                        border: { display: false }
                    }
                }
            }
        });
    }

    // ── 3. Privacy Mode Toggle ──
    const privacyBtn = document.getElementById('privacyToggle');
    let isPrivacyOn = false;
    if(privacyBtn) {
        privacyBtn.addEventListener('click', () => {
            isPrivacyOn = !isPrivacyOn;
            privacyBtn.innerHTML = isPrivacyOn
                ? '<i class="fas fa-eye-slash text-sm text-[#ea741b]"></i>'
                : '<i class="fas fa-eye text-sm"></i>';
            document.querySelectorAll('.privacy-sensitive').forEach(el => {
                el.classList.toggle('blur-sm', isPrivacyOn);
                el.classList.toggle('select-none', isPrivacyOn);
            });
        });
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
