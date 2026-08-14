<?php
$load_ui_components = true;
require_once 'components/header.php';
restrict_access(['admin']);
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

try {
    $stmt = $pdo->query("SELECT * FROM announcements ORDER BY date_posted DESC");
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    $announcements = [];
    $error_msg = "Error fetching announcements.";
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Notice Board</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Announcements</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Manage notices for clinic staff and patients</p>
        </div>
        <button onclick="document.getElementById('addAnnouncementModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-bullhorn text-xs"></i> Post Notice
        </button>
    </div>

    <?php if ($success_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="successAlert">
        <i class="fas fa-check-circle text-green-500"></i> <?= $success_msg ?>
        <button onclick="document.getElementById('successAlert').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
        <button onclick="document.getElementById('errorAlert').remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>

    <!-- Announcements Grid -->
    <?php if(count($announcements) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <?php foreach($announcements as $a):
                $isExpired = $a['expiry_date'] && strtotime($a['expiry_date']) < time();
                $isPublic = $a['visibility'] === 'Public';
            ?>
            <div class="admin-card bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] hover:shadow-[0_8px_30px_rgba(0,69,145,0.10)] hover:-translate-y-1 transition-all duration-300 <?= $isExpired ? 'opacity-60' : '' ?> relative overflow-hidden group">

                <!-- Status accent -->
                <div class="absolute top-0 left-0 w-1 h-full bg-<?= $isExpired ? 'gray-300' : ($isPublic ? '[#ea741b]' : '[#004591]') ?> rounded-l-2xl"></div>

                <div class="flex justify-between items-start mb-4 pl-2">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl <?= $isPublic ? 'bg-[#ea741b]/10' : 'bg-[#e8f0fa]' ?> flex items-center justify-center">
                            <i class="fas fa-bullhorn <?= $isPublic ? 'text-[#ea741b]' : 'text-[#004591]' ?> text-sm"></i>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-1 rounded-full <?= $isExpired ? 'text-gray-500 bg-gray-100' : ($isPublic ? 'text-[#ea741b] bg-[#ea741b]/10' : 'text-[#004591] bg-[#e8f0fa]') ?>">
                            <?= $isExpired ? 'Expired' : ($isPublic ? 'Public' : 'Staff Only') ?>
                        </span>
                    </div>
                    <div class="flex gap-1">
                        <button type="button"
                                data-id="<?= $a['id'] ?>"
                                data-title="<?= htmlspecialchars($a['title']) ?>"
                                data-desc="<?= htmlspecialchars($a['description']) ?>"
                                data-visibility="<?= htmlspecialchars($a['visibility']) ?>"
                                data-expiry="<?= htmlspecialchars($a['expiry_date'] ?? '') ?>"
                                onclick="openEditAnnouncement(this)"
                                class="w-7 h-7 rounded-lg bg-[#F4F7FC] hover:bg-[#ea741b] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all opacity-0 group-hover:opacity-100" title="Edit">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <form method="POST" action="api/delete_announcement.php" onsubmit="return confirm('Delete this announcement?')" style="display:inline">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="w-7 h-7 rounded-lg bg-[#F4F7FC] hover:bg-red-500 hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all opacity-0 group-hover:opacity-100" title="Delete">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="pl-2">
                    <h3 class="font-serif text-lg text-[#004591] font-bold leading-snug mb-2"><?= htmlspecialchars($a['title']) ?></h3>
                    <p class="text-[#7c7c7c] text-sm leading-relaxed mb-4"><?= nl2br(htmlspecialchars($a['description'])) ?></p>

                    <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        <span><i class="fas fa-calendar-alt mr-1 text-[#ea741b]"></i><?= date('M d, Y', strtotime($a['date_posted'])) ?></span>
                        <?php if($a['expiry_date']): ?>
                            <span class="<?= $isExpired ? 'text-red-500' : 'text-[#7c7c7c]' ?>">
                                <i class="fas fa-clock mr-1"></i>Exp: <?= date('M d', strtotime($a['expiry_date'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Add New Card Shortcut -->
            <div onclick="document.getElementById('addAnnouncementModal').classList.remove('hidden')"
                 class="rounded-2xl border-2 border-dashed border-gray-200 p-6 flex flex-col items-center justify-center text-center hover:border-[#004591]/40 hover:bg-white cursor-pointer transition-all duration-300 group min-h-[180px]">
                <div class="w-12 h-12 rounded-full bg-[#F4F7FC] group-hover:bg-[#e8f0fa] flex items-center justify-center mb-3 transition-colors">
                    <i class="fas fa-plus text-[#7c7c7c] group-hover:text-[#004591] transition-colors"></i>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-widest text-gray-400 group-hover:text-[#004591] transition-colors">Post New Notice</p>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-20">
            <div class="w-20 h-20 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-broadcast-tower text-[#7c7c7c] text-2xl md:text-3xl"></i>
            </div>
            <p class="font-serif text-xl text-[#004591] font-bold mb-2">No Announcements Yet</p>
            <p class="text-[#7c7c7c] text-sm mb-6">Post the first notice for the clinic board.</p>
            <button onclick="document.getElementById('addAnnouncementModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all">
                <i class="fas fa-bullhorn text-xs"></i> Post First Notice
            </button>
        </div>
    <?php endif; ?>
</main>

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Notice Board</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Post Announcement</h3>
                </div>
                <button onclick="document.getElementById('addAnnouncementModal').classList.add('hidden')"
                        class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="api/add_announcement.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Title *</label>
                    <input type="text" name="title" required placeholder="e.g., Holiday Closure Notice">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Description *</label>
                    <textarea name="description" required rows="4" placeholder="Full notice content..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Visibility</label>
                        <div class="mod-dropdown" data-name="visibility" data-placeholder="Select Visibility">
                            <input type="hidden" name="visibility" value="Public">
                            <div class="mod-dropdown-trigger">
                                <span class="mod-dropdown-selected">Public</span>
                                <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                            </div>
                            <div class="mod-dropdown-panel">
                                <div class="mod-dropdown-option is-selected" data-value="Public"><span class="opt-check"></span><span>Public</span></div>
                                <div class="mod-dropdown-option" data-value="Staff"><span class="opt-check"></span><span>Staff Only</span></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="mod-calendar" data-placeholder="No expiry">
                            <input type="hidden" name="expiry_date" value="">
                            <div class="mod-calendar-trigger">
                                <span class="mod-calendar-label">Expiry Date</span>
                                <div class="mod-calendar-value">
                                    <i class="fas fa-calendar-day mod-calendar-icon text-sm"></i>
                                    <span class="mod-calendar-text">No expiry</span>
                                    <span class="mod-calendar-clear"><i class="fas fa-times text-[8px]"></i></span>
                                </div>
                            </div>
                            <div class="mod-calendar-panel">
                                <div class="cal-header">
                                    <button type="button" class="cal-header-btn cal-prev"><i class="fas fa-chevron-left"></i></button>
                                    <div class="cal-month-year"></div>
                                    <button type="button" class="cal-today-btn">Today</button>
                                    <button type="button" class="cal-header-btn cal-next"><i class="fas fa-chevron-right"></i></button>
                                </div>
                                <div class="cal-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
                                <div class="cal-days"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit"
                            class="flex-1 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                        <i class="fas fa-paper-plane mr-2"></i> Publish Notice
                    </button>
                    <button type="button" onclick="document.getElementById('addAnnouncementModal').classList.add('hidden')"
                            class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnouncementModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-lg">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Edit</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Update Announcement</h3>
                </div>
                <button onclick="document.getElementById('editAnnouncementModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="api/update_announcement.php" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="id" id="editAnnId">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Title *</label>
                    <input type="text" name="title" id="editAnnTitle" required placeholder="Announcement title">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Description *</label>
                    <textarea name="description" id="editAnnDesc" required rows="4" placeholder="Full notice content..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Visibility</label>
                        <div class="mod-dropdown" id="editAnnVisibility" data-name="visibility" data-placeholder="Select Visibility">
                            <input type="hidden" name="visibility" value="">
                            <div class="mod-dropdown-trigger">
                                <span class="mod-dropdown-selected">Select Visibility</span>
                                <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                            </div>
                            <div class="mod-dropdown-panel">
                                <div class="mod-dropdown-option" data-value="Public"><span class="opt-check"></span><span>Public</span></div>
                                <div class="mod-dropdown-option" data-value="Staff"><span class="opt-check"></span><span>Staff Only</span></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="mod-calendar" id="editAnnExpiry" data-placeholder="No expiry">
                            <input type="hidden" name="expiry_date" value="">
                            <div class="mod-calendar-trigger">
                                <span class="mod-calendar-label">Expiry Date</span>
                                <div class="mod-calendar-value">
                                    <i class="fas fa-calendar-day mod-calendar-icon text-sm"></i>
                                    <span class="mod-calendar-text">No expiry</span>
                                    <span class="mod-calendar-clear"><i class="fas fa-times text-[8px]"></i></span>
                                </div>
                            </div>
                            <div class="mod-calendar-panel">
                                <div class="cal-header">
                                    <button type="button" class="cal-header-btn cal-prev"><i class="fas fa-chevron-left"></i></button>
                                    <div class="cal-month-year"></div>
                                    <button type="button" class="cal-today-btn">Today</button>
                                    <button type="button" class="cal-header-btn cal-next"><i class="fas fa-chevron-right"></i></button>
                                </div>
                                <div class="cal-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
                                <div class="cal-days"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit" class="flex-1 py-3 bg-[#ea741b] hover:bg-[#004591] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                    <button type="button" onclick="document.getElementById('editAnnouncementModal').classList.add('hidden')" class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'components/footer.php'; ?>

<script>
function openEditAnnouncement(btn) {
    document.getElementById('editAnnId').value = btn.dataset.id;
    document.getElementById('editAnnTitle').value = btn.dataset.title;
    document.getElementById('editAnnDesc').value = btn.dataset.desc;
    setModDropdown(document.getElementById('editAnnVisibility'), btn.dataset.visibility);
    setModCalendar('editAnnExpiry', btn.dataset.expiry);
    document.getElementById('editAnnouncementModal').classList.remove('hidden');
}
document.querySelectorAll('.mod-dropdown[data-mod-init]').forEach(function(d){d.removeAttribute('data-mod-init');d._modInit=false;});
document.querySelectorAll('.mod-calendar[data-cal-init]').forEach(function(c){c.removeAttribute('data-cal-init');c._calInit=false;});
document.querySelectorAll('.mod-dropdown').forEach(initModDropdown);
document.querySelectorAll('.mod-calendar').forEach(initModCalendar);
['addAnnouncementModal','editAnnouncementModal'].forEach(function(id){
    var m=document.getElementById(id);
    if(m)m.addEventListener('click',function(e){if(e.target===m)m.classList.add('hidden');});
});
</script>
