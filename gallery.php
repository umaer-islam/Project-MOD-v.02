<?php
require_once 'components/header.php';
restrict_access(['admin']);
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

// Auto-create table if missing
if ($pdo) {
    try {
        $pdo->query("SELECT 1 FROM gallery LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS gallery (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_path VARCHAR(255) NOT NULL,
            caption VARCHAR(255),
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }
}

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error']   ?? '');

$images = [];
try {
    $images = $pdo->query("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC")->fetchAll();
} catch (PDOException $e) { $error_msg = "Database error."; }
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Content Management</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Clinic Gallery</h1>
            <p class="text-[#7c7c7c] text-sm mt-1"><?= count($images) ?> image<?= count($images) !== 1 ? 's' : '' ?> · Manage your public-facing clinic interior photos</p>
        </div>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-image text-xs"></i> Add Image
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

    <!-- Gallery Grid -->
    <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
        <?php if (count($images) > 0): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 p-6">
            <?php foreach ($images as $img): ?>
            <div class="group relative rounded-2xl overflow-hidden border border-gray-100 bg-[#F4F7FC]">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="<?= htmlspecialchars($img['image_path']) ?>"
                         alt="<?= htmlspecialchars($img['caption'] ?? 'Gallery') ?>" loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                </div>
                <div class="p-3">
                    <p class="text-[#004591] font-semibold text-xs truncate"><?= htmlspecialchars($img['caption'] ?? '—') ?></p>
                    <p class="text-gray-400 text-[10px] mt-0.5">Order: <?= $img['sort_order'] ?></p>
                </div>
                <!-- Action overlay -->
                <div class="absolute inset-0 bg-[#004591]/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                    <button onclick="openEdit(<?= $img['id'] ?>, '<?= htmlspecialchars(addslashes($img['caption'] ?? '')) ?>', <?= $img['sort_order'] ?>)"
                            class="w-9 h-9 rounded-xl bg-white/20 hover:bg-[#ea741b] text-white flex items-center justify-center transition-all" title="Edit">
                        <i class="fas fa-edit text-xs"></i>
                    </button>
                    <form method="POST" action="api/delete_gallery.php" onsubmit="return confirm('Delete this image?')" style="display:inline">
                        <input type="hidden" name="id" value="<?= $img['id'] ?>">
                        <button type="submit" class="w-9 h-9 rounded-xl bg-white/20 hover:bg-red-500 text-white flex items-center justify-center transition-all" title="Delete">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="flex flex-col items-center justify-center py-20 text-center px-6">
            <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-images text-[#7c7c7c] text-xl md:text-2xl"></i>
            </div>
            <p class="text-[#7c7c7c] font-semibold">No gallery images yet.</p>
            <p class="text-[#7c7c7c] text-xs mt-1">Click "Add Image" to upload the first photo.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Add Image Modal -->
<div id="addModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Upload</p>
                <h3 class="font-serif text-xl text-[#004591] font-bold">Add Gallery Image</h3>
            </div>
            <button onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="api/add_gallery.php" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <!-- Image Preview -->
            <div class="relative border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-[#ea741b] transition-colors cursor-pointer"
                 onclick="document.getElementById('imageFile').click()">
                <img id="imgPreview" src="" alt="" class="hidden max-h-48 mx-auto rounded-lg mb-3 object-cover">
                <div id="imgPlaceholder" class="py-6">
                    <i class="fas fa-cloud-upload-alt text-2xl md:text-3xl text-gray-300 mb-2"></i>
                    <p class="text-gray-400 text-sm font-semibold">Click to select image</p>
                    <p class="text-gray-300 text-xs mt-1">JPG, PNG, WebP, GIF</p>
                </div>
                <input type="file" id="imageFile" name="image" accept="image/*" required class="hidden"
                       onchange="previewImage(this)">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Caption</label>
                <input type="text" name="caption" placeholder="e.g. Reception Area"
                       class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Display Order <span class="normal-case font-normal text-gray-400">(lower = first)</span></label>
                <input type="number" name="sort_order" value="0" min="0"
                       class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all">
            </div>
            <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                <button type="submit" class="px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                    <i class="fas fa-upload mr-2"></i>Upload Image
                </button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')"
                        class="px-6 py-3 bg-[#F4F7FC] text-[#7c7c7c] hover:text-[#004591] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-gray-100">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="font-serif text-xl text-[#004591] font-bold">Edit Image Details</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <form action="api/update_gallery.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="id" id="editId">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Caption</label>
                <input type="text" name="caption" id="editCaption" class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Display Order</label>
                <input type="number" name="sort_order" id="editOrder" min="0" class="w-full px-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] outline-none transition-all">
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
function openEdit(id, caption, order) {
    document.getElementById('editId').value = id;
    document.getElementById('editCaption').value = caption;
    document.getElementById('editOrder').value = order;
    document.getElementById('editModal').classList.remove('hidden');
}
function previewImage(input) {
    const preview = document.getElementById('imgPreview');
    const placeholder = document.getElementById('imgPlaceholder');
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
// Auto-dismiss alerts
setTimeout(() => {
    ['successAlert','errorAlert'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = 0; setTimeout(() => el.remove(), 500); }
    });
}, 5000);
// Close modals on overlay click
['addModal','editModal'].forEach(id => {
    const modal = document.getElementById(id);
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
});
</script>

<?php require_once 'components/footer.php'; ?>
