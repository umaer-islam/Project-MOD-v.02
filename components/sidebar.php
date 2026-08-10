<!-- Sidebar -->
<aside id="mainSidebar" class="w-64 flex-shrink-0 flex flex-col bg-[#080B14] border-r border-[#1e293b] shadow-2xl transition-all duration-300 z-30
    fixed inset-y-0 left-0 -translate-x-full md:translate-x-0 md:relative md:inset-auto">

    <!-- Logo -->
    <div class="px-6 py-5 border-b border-[#1e293b] flex items-center justify-between">
        <a href="dashboard.php" class="flex items-center gap-3 group">
            <img src="Logo.png" alt="Mamun's Ortho Dental Logo" class="w-10 h-10 object-contain flex-shrink-0 transition-transform duration-300 group-hover:scale-105" style="filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));">
            <div class="flex flex-col leading-none">
                <span class="font-serif text-white text-[14px] font-bold tracking-tight">Mamun's <span class="text-[#ea741b] italic font-medium">Ortho</span></span>
                <span class="text-[8px] tracking-[0.25em] text-gray-500 uppercase font-bold mt-0.5">Dental System</span>
            </div>
        </a>
        <!-- Mobile close -->
        <button onclick="closeSidebar()" class="md:hidden w-8 h-8 rounded-lg bg-[#111827] flex items-center justify-center text-gray-400 hover:bg-red-500/10 hover:text-red-400 transition-all">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <!-- Nav Items -->
    <div class="overflow-y-auto flex-1 py-4 px-3 space-y-0.5">
        <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $rawRole = $_SESSION['user_role'] ?? '';
        $role = strtolower(trim((string)$rawRole));
        if (empty($role)) $role = 'receptionist'; // Fallback
        
        // ── Navigation items with per-role access control ──────────────
        // Roles: admin = full access | doctor = clinical only | receptionist = front-desk + manager
        $navItems = [

            // ── Shared: all roles ──
            ['url' => 'dashboard.php',    'icon' => 'fa-chart-line',          'label' => 'Dashboard',       'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],
            ['url' => 'patients.php',     'icon' => 'fa-users',               'label' => 'Patients',        'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],
            ['url' => 'appointments.php', 'icon' => 'fa-calendar-check',      'label' => 'Appointments',    'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],
            ['url' => 'payments.php',     'icon' => 'fa-file-invoice-dollar', 'label' => 'Payments',        'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],
            ['url' => 'messages.php',     'icon' => 'fa-envelope-open-text',  'label' => 'Messages',        'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],
            ['url' => 'testimonials.php', 'icon' => 'fa-star',                'label' => 'Testimonials',    'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],
            ['url' => 'reports.php',      'icon' => 'fa-chart-pie',           'label' => 'Reports',         'roles' => ['admin', 'doctor', 'receptionist'], 'group' => ''],

            // ── Clinical: admin + doctor only ──
            ['url' => 'prescriptions.php','icon' => 'fa-pills',               'label' => 'Prescriptions',   'roles' => ['admin', 'doctor'],                'group' => 'Clinical'],

            // ── Admin only ──
            ['url' => 'announcements.php','icon' => 'fa-bullhorn',            'label' => 'Announcements',   'roles' => ['admin'],                           'group' => ''],
            ['url' => 'users.php',        'icon' => 'fa-user-gear',           'label' => 'Staff',           'roles' => ['admin'],                           'group' => ''],
            ['url' => 'gallery.php',      'icon' => 'fa-images',              'label' => 'Gallery',         'roles' => ['admin'],                           'group' => ''],
            ['url' => 'cases.php',        'icon' => 'fa-image',               'label' => 'Before & After',  'roles' => ['admin'],                           'group' => ''],
        ];

        $renderedCount = 0;
        $lastGroup = null;
        foreach ($navItems as $item):
            if (!in_array($role, $item['roles'])) continue;
            $renderedCount++;
            $isActive   = ($currentPage == $item['url']);
            $itemGroup  = $item['group'] ?? '';

            // Render group label separator on first item of each named group
            if ($itemGroup !== '' && $itemGroup !== $lastGroup):
                $lastGroup = $itemGroup;
        ?>
            <div class="pt-4 pb-1 px-5">
                <p class="text-[9px] font-bold uppercase tracking-[0.25em] text-gray-600"><?= $itemGroup ?></p>
            </div>
        <?php endif; ?>
            <a href="<?= $item['url'] ?>" class="flex items-center gap-3 px-4 py-3 mx-2 rounded-xl group transition-all duration-300 <?= $isActive ? 'bg-gradient-to-r from-[#ea741b]/10 to-transparent border-l-4 border-[#ea741b] text-white shadow-[inset_4px_0_20px_rgba(234,116,27,0.05)]' : 'text-gray-400 hover:bg-[#111827] hover:text-white border-l-4 border-transparent' ?>">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 <?= $isActive ? 'bg-[#ea741b]/20 shadow-[0_0_15px_rgba(234,116,27,0.2)]' : 'bg-[#111827] group-hover:bg-[#1e293b]' ?> transition-all premium-glow-border">
                    <i class="fas <?= $item['icon'] ?> text-sm <?= $isActive ? 'text-[#ea741b]' : 'text-gray-500 group-hover:text-white' ?> transition-colors"></i>
                </div>
                <span class="font-semibold text-sm tracking-wide"><?= $item['label'] ?></span>
                <?php if ($isActive): ?>
                    <div class="ml-auto w-1.5 h-1.5 rounded-full bg-[#ea741b] shadow-[0_0_8px_rgba(234,116,27,0.8)]"></div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
        
        <?php if ($renderedCount === 0): ?>
            <div class="px-4 py-6 mt-4 mx-2 bg-red-50 border border-red-100 rounded-xl text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                <p class="text-xs font-bold text-red-600 uppercase tracking-widest mb-1">Session Error</p>
                <p class="text-[10px] text-red-500">Your role [<?= htmlspecialchars($role) ?>] has no permissions.</p>
                <a href="logout.php" class="inline-block mt-3 px-4 py-1.5 bg-red-500 text-white rounded text-xs font-bold shadow-sm">Fix Session (Logout)</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer / Logout -->
    <div class="p-4 border-t border-[#1e293b]">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-200 group">
            <div class="w-8 h-8 rounded-lg bg-[#111827] group-hover:bg-red-500/20 flex items-center justify-center flex-shrink-0 transition-all">
                <i class="fas fa-sign-out-alt text-sm"></i>
            </div>
            <span class="font-semibold text-sm">Logout</span>
        </a>
    </div>
</aside>

<!-- Mobile Sidebar Overlay -->
<div id="sidebarOverlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-20 hidden md:hidden"></div>

<script>
function openSidebar() {
    document.getElementById('mainSidebar').classList.remove('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.remove('hidden');
}
function closeSidebar() {
    document.getElementById('mainSidebar').classList.add('-translate-x-full');
    document.getElementById('sidebarOverlay').classList.add('hidden');
}
</script>

