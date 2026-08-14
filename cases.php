<?php
require_once 'components/header.php';
restrict_access(['admin']);
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

// Auto-create table if missing
if ($pdo) {
    try {
        $pdo->query("SELECT 1 FROM before_after_cases LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS before_after_cases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            before_image VARCHAR(255) NOT NULL,
            after_image VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error']   ?? '');

$cases = [];
try {
    $cases = $pdo->query("SELECT * FROM before_after_cases ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) { $error_msg = "Database error."; }
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Content Management</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Before &amp; After Cases</h1>
            <p class="text-[#7c7c7c] text-sm mt-1"><?= count($cases) ?> case<?= count($cases) !== 1 ? 's' : '' ?> · Showcase real patient transformations on the homepage</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-plus text-xs"></i> Add Case Study
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

    <!-- Cases Grid -->
    <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
        <?php if (count($cases) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 p-6">
            <?php foreach ($cases as $c): ?>
            <div class="bg-[#F8FAFD] rounded-2xl border border-gray-100 overflow-hidden">
                <!-- Before / After Images -->
                <div class="grid grid-cols-2 gap-0.5 bg-gray-200">
                    <div class="relative">
                        <div class="aspect-[4/3] overflow-hidden bg-[#e8f0fa]">
                            <img src="<?= htmlspecialchars($c['before_image']) ?>" alt="Before" loading="lazy"
                                 class="w-full h-full object-cover">
                        </div>
                        <span class="absolute bottom-2 left-2 bg-red-500 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">Before</span>
                    </div>
                    <div class="relative">
                        <div class="aspect-[4/3] overflow-hidden bg-[#e8f0fa]">
                            <img src="<?= htmlspecialchars($c['after_image']) ?>" alt="After" loading="lazy"
                                 class="w-full h-full object-cover">
                        </div>
                        <span class="absolute bottom-2 left-2 bg-green-500 text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">After</span>
                    </div>
                </div>
                <!-- Info -->
                <div class="p-4">
                    <h4 class="font-bold text-[#004591] mb-1 truncate"><?= htmlspecialchars($c['title']) ?></h4>
                    <p class="text-gray-500 text-xs leading-relaxed line-clamp-2"><?= htmlspecialchars($c['description'] ?? '—') ?></p>
                    <p class="text-gray-300 text-[10px] mt-2"><?= date('M d, Y', strtotime($c['created_at'])) ?></p>
                    <!-- Actions -->
                    <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                        <button onclick="openEdit(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['title'])) ?>', '<?= htmlspecialchars(addslashes($c['description'] ?? '')) ?>')"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-[#F4F7FC] hover:bg-[#ea741b] hover:text-white text-[#7c7c7c] text-[11px] font-bold uppercase tracking-wider transition-all">
                            <i class="fas fa-edit text-xs"></i> Edit
                        </button>
                        <form method="POST" action="api/delete_case.php" onsubmit="return confirm('Delete this case study?')" style="display:inline;flex:1">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-[#F4F7FC] hover:bg-red-500 hover:text-white text-[#7c7c7c] text-[11px] font-bold uppercase tracking-wider transition-all">
                                <i class="fas fa-trash-alt text-xs"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="flex flex-col items-center justify-center py-20 text-center px-6">
            <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-images text-[#7c7c7c] text-xl md:text-2xl"></i>
            </div>
            <p class="text-[#7c7c7c] font-semibold">No case studies yet.</p>
            <p class="text-[#7c7c7c] text-xs mt-1">Add a before &amp; after case to showcase real results.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Add Case Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">New Case</p>
                <h3 class="font-serif text-xl text-[#004591] font-bold">Add Before &amp; After Case</h3>
            </div>
            <button onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="api/add_case.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Case Title *</label>
                <input type="text" name="title" required placeholder="e.g. Orthodontic Brace Transformation"
                       class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Before Image *</label>
                    <div class="relative border-2 border-dashed border-red-200 rounded-xl p-3 text-center hover:border-red-400 transition-colors cursor-pointer bg-red-50/30"
                         onclick="document.getElementById('beforeFile').click()">
                        <img id="beforePreview" src="" alt="" class="hidden max-h-32 mx-auto rounded-lg mb-2 object-cover">
                        <div id="beforePlaceholder" class="py-3">
                            <i class="fas fa-cloud-upload-alt text-xl md:text-2xl text-red-300 mb-1"></i>
                            <p class="text-red-400 text-xs font-semibold">Upload BEFORE</p>
                        </div>
                        <input type="file" id="beforeFile" name="before_image" accept="image/*" required class="hidden"
                               onchange="previewFile(this,'beforePreview','beforePlaceholder')">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">After Image *</label>
                    <div class="relative border-2 border-dashed border-green-200 rounded-xl p-3 text-center hover:border-green-400 transition-colors cursor-pointer bg-green-50/30"
                         onclick="document.getElementById('afterFile').click()">
                        <img id="afterPreview" src="" alt="" class="hidden max-h-32 mx-auto rounded-lg mb-2 object-cover">
                        <div id="afterPlaceholder" class="py-3">
                            <i class="fas fa-cloud-upload-alt text-xl md:text-2xl text-green-300 mb-1"></i>
                            <p class="text-green-500 text-xs font-semibold">Upload AFTER</p>
                        </div>
                        <input type="file" id="afterFile" name="after_image" accept="image/*" required class="hidden"
                               onchange="previewFile(this,'afterPreview','afterPlaceholder')">
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Description / Case Study</label>
                <textarea name="description" rows="3" placeholder="Brief description of the case, treatment applied, duration, etc."
                          class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all resize-none"></textarea>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                    <i class="fas fa-plus mr-2"></i>Add Case Study
                </button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-6 py-3 bg-[#F4F7FC] text-[#7c7c7c] hover:text-[#004591] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Case Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="font-serif text-xl text-[#004591] font-bold">Edit Case Study</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="api/update_case.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Case Title *</label>
                <input type="text" name="title" id="editTitle" required
                       class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Description</label>
                <textarea name="description" id="editDesc" rows="3"
                          class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all resize-none"></textarea>
            </div>
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Replace Images <span class="normal-case font-normal">(leave blank to keep existing)</span></p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">New Before Image</label>
                    <input type="file" name="before_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#F4F7FC] file:text-[#004591] file:font-bold file:text-xs file:cursor-pointer">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">New After Image</label>
                    <input type="file" name="after_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-[#F4F7FC] file:text-[#004591] file:font-bold file:text-xs file:cursor-pointer">
                </div>
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-[#ea741b] hover:bg-[#004591] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                        class="px-6 py-3 bg-[#F4F7FC] text-[#7c7c7c] hover:text-[#004591] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, title, desc) {
    document.getElementById('editId').value = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editModal').classList.remove('hidden');
}
function previewFile(input, previewId, placeholderId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
setTimeout(() => {
    ['successAlert','errorAlert'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = 0; setTimeout(() => el.remove(), 500); }
    });
}, 5000);
['addModal','editModal'].forEach(id => {
    const modal = document.getElementById(id);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
});
</script>

<?php require_once 'components/footer.php'; ?>
