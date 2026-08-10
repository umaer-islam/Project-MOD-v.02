<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';
restrict_access(['admin', 'doctor', 'receptionist']);

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

if ($pdo !== null) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_name VARCHAR(100) NOT NULL,
            location VARCHAR(100) DEFAULT 'Dhaka',
            stars INT DEFAULT 5,
            review TEXT NOT NULL,
            status ENUM('Published', 'Hidden') DEFAULT 'Published',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC");
        $testimonials = $stmt->fetchAll();
    } catch (PDOException $e) {
        $testimonials = [];
        $error_msg = "A database error occurred. Please try again.";
    }
} else {
    $testimonials = [];
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Landing Page</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Patient Reviews</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Manage testimonials shown on the website</p>
        </div>
        <button onclick="document.getElementById('addTestimonialModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
            <i class="fas fa-plus text-xs"></i> New Review
        </button>
    </div>

    <?php if ($success_msg): ?>
    <div class="mb-5 flex items-center bg-green-50 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="successAlert">
        <i class="fas fa-check-circle mr-3"></i> <?= $success_msg ?>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="mb-5 flex items-center bg-red-50 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="errorAlert">
        <i class="fas fa-exclamation-circle mr-3"></i> <?= $error_msg ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach($testimonials as $t): ?>
        <div class="admin-card bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col relative group">
            <div class="absolute top-4 right-4 flex gap-1 opacity-0 group-hover:opacity-100 transition-all">
                <?php if($t['status'] === 'Pending'): ?>
                <form method="POST" action="api/save_testimonial.php" style="display:inline">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600 hover:bg-green-500 hover:text-white transition-all mr-1" title="Approve"><i class="fas fa-check text-xs"></i></button>
                </form>
                <?php endif; ?>
                <button onclick="openEditTestimonial(<?= $t['id'] ?>)" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-[#7c7c7c] hover:text-[#004591]"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="api/save_testimonial.php" onsubmit="return confirm('Delete this review?')" style="display:inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-[#7c7c7c] hover:text-red-500"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
            
            <div class="flex gap-1 mb-3 text-[#ea741b]">
                <?php for($i=0; $i<$t['stars']; $i++) echo '<i class="fas fa-star text-sm"></i>'; ?>
            </div>
            <p class="text-sm text-gray-600 italic mb-5 leading-relaxed flex-1">"<?= htmlspecialchars($t['review']) ?>"</p>
            
            <div class="flex items-center justify-between border-t border-gray-50 pt-4 mt-auto">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#004591] text-white flex items-center justify-center font-bold text-xs"><?= strtoupper(substr($t['patient_name'],0,1)) ?></div>
                    <div class="leading-tight">
                        <p class="font-bold text-[#004591] text-sm"><?= htmlspecialchars($t['patient_name']) ?></p>
                        <p class="text-xs text-gray-400"><?= htmlspecialchars($t['location']) ?></p>
                    </div>
                </div>
                <?php 
                if($t['status']=='Published') $badge='bg-green-50 text-green-600 border-green-200'; 
                elseif($t['status']=='Pending') $badge='bg-yellow-50 text-yellow-600 border-yellow-200 animate-pulse'; 
                else $badge='bg-gray-100 text-gray-500 border-gray-200'; 
                ?>
                <span class="text-[10px] uppercase font-bold px-2 py-1 rounded border <?= $badge ?>"><?= $t['status'] ?></span>
            </div>
            
            <!-- Hidden data for edit modal -->
            <div id="t_data_<?= $t['id'] ?>" data-name="<?= htmlspecialchars($t['patient_name']) ?>" data-loc="<?= htmlspecialchars($t['location']) ?>" data-stars="<?= $t['stars'] ?>" data-review="<?= htmlspecialchars($t['review']) ?>" data-status="<?= $t['status'] ?>" class="hidden"></div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Modal -->
<div id="addTestimonialModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-[#004591]/20 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-5 pb-4 border-b">
            <h3 class="font-serif text-xl font-bold text-[#004591]" id="modalTitle">New Review</h3>
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form action="api/save_testimonial.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" id="modalAction" value="add">
            <input type="hidden" name="id" id="modalId" value="">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Patient Name</label>
                    <input type="text" name="patient_name" id="modalName" required class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#ea741b] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Location</label>
                    <input type="text" name="location" id="modalLoc" value="Dhaka" class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#ea741b] outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Stars (1-5)</label>
                    <input type="number" name="stars" id="modalStars" min="1" max="5" value="5" class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#ea741b] outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Visibility</label>
                    <div class="mod-dropdown" id="modalStatus" data-name="status" data-placeholder="Select Status">
                        <input type="hidden" name="status" value="Published">
                        <div class="mod-dropdown-trigger">
                            <span class="mod-dropdown-selected">Published</span>
                            <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                        </div>
                        <div class="mod-dropdown-panel">
                            <div class="mod-dropdown-option is-selected" data-value="Published"><span class="opt-check"></span><span>Published</span></div>
                            <div class="mod-dropdown-option" data-value="Pending"><span class="opt-check"></span><span>Pending</span></div>
                            <div class="mod-dropdown-option" data-value="Hidden"><span class="opt-check"></span><span>Hidden</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Review Text</label>
                <textarea name="review" id="modalReview" rows="4" required class="w-full border rounded-xl px-3 py-2 text-sm focus:border-[#ea741b] outline-none"></textarea>
            </div>
            
            <div class="pt-4 border-t flex gap-3">
                <button type="submit" class="flex-1 bg-[#004591] hover:bg-[#ea741b] text-white py-2.5 rounded-xl font-bold uppercase text-xs tracking-widest transition-colors"><i class="fas fa-save mr-2"></i> Save</button>
                <button type="button" onclick="closeModal()" class="px-5 bg-gray-100 py-2.5 rounded-xl text-gray-600 font-bold uppercase text-xs transition-colors">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditTestimonial(id) {
        document.getElementById('modalTitle').innerText = 'Edit Review';
        document.getElementById('modalAction').value = 'update';
        document.getElementById('modalId').value = id;
        
        const data = document.getElementById('t_data_' + id);
        document.getElementById('modalName').value = data.dataset.name;
        document.getElementById('modalLoc').value = data.dataset.loc;
        document.getElementById('modalStars').value = data.dataset.stars;
        document.getElementById('modalReview').value = data.dataset.review;
        setModDropdown('modalStatus', data.dataset.status);
        
        document.getElementById('addTestimonialModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('addTestimonialModal').classList.add('hidden');
        document.getElementById('modalTitle').innerText = 'New Review';
        document.getElementById('modalAction').value = 'add';
        document.getElementById('modalId').value = '';
        document.getElementById('modalName').value = '';
        document.getElementById('modalReview').value = '';
    }
    
    setTimeout(() => {
        ['successAlert','errorAlert'].forEach(id => {
            let el = document.getElementById(id);
            if(el) el.remove();
        });
    }, 4000);
</script>

<?php require_once 'components/footer.php'; ?>

