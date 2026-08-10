<!-- Right Side Wrapper (Stacks topbar and main content vertically) -->
<div class="flex-1 flex flex-col min-h-0 min-w-0">

<!-- Topbar -->
<header class="h-16 flex items-center justify-between px-4 md:px-6 mx-4 mt-4 rounded-2xl glass-panel-premium z-40 flex-shrink-0 sticky top-4">
    <div class="flex items-center gap-3 md:gap-4">
        <!-- Mobile Sidebar Toggle -->
        <button onclick="openSidebar()" id="mobileSidebarToggle" class="md:hidden w-9 h-9 rounded-xl bg-[#F4F7FC] flex items-center justify-center text-[#004591] hover:bg-[#004591] hover:text-white transition-all duration-300">
            <i class="fas fa-bars text-sm"></i>
        </button>

        <!-- Search -->
        <div class="relative hidden lg:block">
            <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                <i class="fas fa-search text-gray-400 text-sm"></i>
            </span>
            <input type="text" id="globalSearch" placeholder="Search patients by phone or ID..."
                   class="w-64 xl:w-96 pl-11 pr-4 py-2.5 rounded-full bg-gray-50 border border-gray-200 shadow-inner focus:border-[#ea741b] focus:ring-4 focus:ring-[#ea741b]/10 focus:bg-white text-sm text-[#004591] placeholder-gray-400 transition-all duration-300 outline-none font-sans">

            <!-- AJAX Results Dropdown -->
            <div id="searchResults" class="absolute left-0 right-0 mt-2 w-full bg-white border border-gray-100 rounded-xl shadow-xl hidden z-50 max-h-64 overflow-y-auto">
                <!-- Results injected here -->
            </div>
        </div>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center gap-3">
        <!-- Live Clock (Hidden on very small screens) -->
        <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#F4F7FC] text-[#004591]">
            <i class="far fa-clock text-[#ea741b] text-[11px]"></i>
            <span id="liveClock" class="text-xs font-bold font-mono">--:--:--</span>
        </div>
        <script>
            function updateClock() {
                const now = new Date();
                let h = now.getHours();
                const ampm = h >= 12 ? 'PM' : 'AM';
                h = h % 12; h = h ? h : 12;
                let m = String(now.getMinutes()).padStart(2, '0');
                let s = String(now.getSeconds()).padStart(2, '0');
                document.getElementById('liveClock').textContent = `${h}:${m}:${s} ${ampm}`;
            }
            setInterval(updateClock, 1000);
            updateClock();
        </script>

        <!-- Homepage Link -->
        <a href="index.php" target="_blank" class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-[#004591] transition-colors px-3 py-2 rounded-xl hover:bg-[#F4F7FC]">
            <i class="fas fa-globe text-[#ea741b] text-[11px]"></i>
            <span class="hidden lg:inline">Website</span>
        </a>

        <!-- Notifications -->
        <?php
        require_once __DIR__ . '/../database/connection.php';
        $unread_msg_count = 0;
        $pending_review_count = 0;
        if (isset($pdo)) {
            try { $unread_msg_count = (int)$pdo->query("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'unread'")->fetchColumn(); } catch (Exception $e) {}
            try { $pending_review_count = (int)$pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'Pending'")->fetchColumn(); } catch (Exception $e) {}
        }
        $total_notifs = $unread_msg_count + $pending_review_count;
        ?>
        <div class="relative">
            <button id="notifMenuBtn" class="relative w-10 h-10 rounded-full bg-white border border-gray-100 shadow-sm flex items-center justify-center <?= $total_notifs > 0 ? 'text-[#ea741b]' : 'text-[#7c7c7c]' ?> hover:border-[#004591] hover:text-[#004591] transition-all duration-300 focus:outline-none hover-lift-premium">
                <i class="fas fa-bell text-sm <?= $total_notifs > 0 ? 'animate-pulse' : '' ?>"></i>
                <?php if ($total_notifs > 0): ?>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold flex items-center justify-center rounded-full border-2 border-white shadow-sm"><?= $total_notifs ?></span>
                <?php endif; ?>
            </button>
            <div id="notifMenu" class="absolute right-0 mt-3 w-72 glass-panel-premium rounded-2xl hidden py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                    <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Notifications</p>
                    <?php if ($total_notifs > 0): ?>
                        <span class="bg-red-100 text-red-600 text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full"><?= $total_notifs ?> New</span>
                    <?php endif; ?>
                </div>
                <div class="py-2">
                    <?php if ($total_notifs > 0): ?>
                        <?php if ($unread_msg_count > 0): ?>
                        <a href="messages.php" class="flex items-start gap-3 px-4 py-3 hover:bg-[#F4F7FC] transition-colors border-l-2 border-[#004591]">
                            <div class="w-8 h-8 rounded-full bg-[#004591]/10 flex items-center justify-center text-[#004591] flex-shrink-0 mt-0.5"><i class="fas fa-calendar-alt text-xs"></i></div>
                            <div>
                                <p class="text-[#004591] text-xs font-bold leading-tight">Messages / Appointments</p>
                                <p class="text-gray-500 text-[10px] mt-1">You have <?= $unread_msg_count ?> unread inquiry.</p>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php if ($pending_review_count > 0): ?>
                        <a href="testimonials.php" class="flex items-start gap-3 px-4 py-3 hover:bg-[#F4F7FC] transition-colors border-l-2 border-[#ea741b]">
                            <div class="w-8 h-8 rounded-full bg-[#ea741b]/10 flex items-center justify-center text-[#ea741b] flex-shrink-0 mt-0.5"><i class="fas fa-star text-xs"></i></div>
                            <div>
                                <p class="text-[#ea741b] text-xs font-bold leading-tight">New Patient Reviews</p>
                                <p class="text-gray-500 text-[10px] mt-1">You have <?= $pending_review_count ?> pending review.</p>
                            </div>
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="px-4 py-6 text-center">
                            <i class="far fa-bell-slash text-gray-300 text-2xl mb-2"></i>
                            <p class="text-gray-400 text-xs font-semibold">No new notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="border-t border-gray-100 mt-1 pt-1 grid grid-cols-2">
                    <a href="messages.php" class="block text-center border-r border-gray-100 py-2 text-[9px] font-bold uppercase tracking-widest text-[#004591] hover:bg-gray-50 transition-colors">Messages</a>
                    <a href="testimonials.php" class="block text-center py-2 text-[9px] font-bold uppercase tracking-widest text-[#ea741b] hover:bg-gray-50 transition-colors">Reviews</a>
                </div>
            </div>
        </div>
        <script>
            // Notification toggle
            const notifBtn = document.getElementById('notifMenuBtn');
            const notifMenu = document.getElementById('notifMenu');
            if(notifBtn) {
                notifBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notifMenu.classList.toggle('hidden');
                    const userMenu = document.getElementById('userMenu');
                    if(userMenu && !userMenu.classList.contains('hidden')) userMenu.classList.add('hidden');
                });
            }
            document.addEventListener('click', (e) => {
                if(notifMenu && !notifMenu.contains(e.target) && e.target !== notifBtn) notifMenu.classList.add('hidden');
            });
        </script>

        <div class="relative">
            <button id="userMenuBtn" class="flex items-center gap-3 group focus:outline-none hover-lift-premium">
                <img class="w-10 h-10 rounded-full border-2 border-white shadow-sm group-hover:border-[#ea741b] transition-colors"
                     src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'User') ?>&background=004591&color=fff&bold=true&size=128"
                     alt="User avatar">
                <div class="hidden md:flex flex-col items-start leading-none">
                    <span class="text-xs font-bold text-[#004591] group-hover:text-[#ea741b] transition-colors"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                    <span class="text-[9px] text-gray-400 uppercase tracking-wider mt-0.5"><?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Receptionist')) ?></span>
                </div>
                <i class="fas fa-chevron-down text-[10px] text-gray-400 hidden md:block group-hover:text-[#ea741b] transition-colors ml-1"></i>
            </button>

            <div id="userMenu" class="absolute right-0 mt-3 w-56 glass-panel-premium rounded-2xl hidden py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Signed in as</p>
                    <p class="text-sm font-bold text-[#004591] mt-0.5 truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
                </div>
                <a href="profile.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#7c7c7c] hover:text-[#004591] hover:bg-[#F4F7FC] transition-colors">
                    <i class="fas fa-user-circle text-[#ea741b] text-sm w-4"></i>Your Profile
                </a>
                <a href="index.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-[#7c7c7c] hover:text-[#004591] hover:bg-[#F4F7FC] transition-colors">
                    <i class="fas fa-home text-[#ea741b] text-sm w-4"></i>Homepage
                </a>
                <div class="border-t border-gray-100 mt-1 pt-1">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt text-sm w-4"></i>Sign Out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Main Content Area Wrapper (scrolls internally) -->
<div class="flex-1 w-full h-full relative overflow-y-auto bg-[#F8FAFC]">
