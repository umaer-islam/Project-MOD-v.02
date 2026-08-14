<?php
$load_ui_components = true;
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

try {
    $stmt = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC");
    $patients = $stmt->fetchAll();
    $total = count($patients);
} catch (PDOException $e) {
    $patients = []; $total = 0;
    $error_msg = "A database error occurred. Please try again.";
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Management</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Patients Directory</h1>
            <p class="text-[#7c7c7c] text-sm mt-1"><?= $total ?> patient<?= $total !== 1 ? 's' : '' ?> · Manage records, histories, and QR profiles</p>
        </div>
        <button onclick="document.getElementById('addPatientModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-user-plus text-xs"></i>
            Add Patient
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



    <!-- Patients Table Card -->
    <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">


        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-sm"></i>
                </span>
                <input type="text" id="patientTableSearch" placeholder="Filter by name, phone or ID..."
                       class="pl-10 pr-4 py-2.5 rounded-xl bg-[#F4F7FC] border border-transparent focus:border-[#ea741b] focus:bg-white text-sm text-[#004591] placeholder-gray-400 transition-all outline-none w-64">
            </div>
            <button title="Export CSV"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#F4F7FC] text-[#7c7c7c] hover:text-[#004591] text-[11px] font-bold uppercase tracking-wider transition-all">
                <i class="fas fa-file-export text-xs text-[#ea741b]"></i>
                Export
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Patient ID</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Name</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Phone</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Age / Gender</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Registered</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody" class="divide-y divide-gray-50">
                    <?php if (count($patients) > 0): ?>
                        <?php foreach($patients as $p): ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors group">
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="bg-[#e8f0fa] text-[#004591] text-[10px] font-bold px-3 py-1.5 rounded-full tracking-wider">
                                    <?= htmlspecialchars($p['patient_id']) ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#004591] flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                        <?= strtoupper(substr($p['name'], 0, 1)) ?>
                                    </div>
                                    <span class="font-semibold text-[#004591]"><?= htmlspecialchars($p['name']) ?></span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] font-medium"><?= htmlspecialchars($p['phone']) ?></td>
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c]">
                                <?= $p['age'] ?>y /
                                <?php if ($p['gender'] == 'Male'): ?>
                                    <span class="text-blue-500 font-bold text-xs ml-1"><i class="fas fa-mars"></i> Male</span>
                                <?php elseif ($p['gender'] == 'Female'): ?>
                                    <span class="text-pink-500 font-bold text-xs ml-1"><i class="fas fa-venus"></i> Female</span>
                                <?php else: ?>
                                    <span class="text-gray-500 text-xs ml-1"><?= htmlspecialchars($p['gender']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] text-sm"><?= date('M d, Y', strtotime($p['created_at'])) ?></td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="patient_record.php?pid=<?= urlencode($p['patient_id']) ?>&token=<?= urlencode($p['access_token'] ?? '') ?>" target="_blank"
                                       class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#004591] hover:text-white flex items-center justify-center text-[#004591] transition-all" title="View QR Profile">
                                        <i class="fas fa-qrcode text-xs"></i>
                                    </a>
                                    <button onclick="openEditPatient(this)"
                                            data-id="<?= $p['id'] ?>"
                                            data-name="<?= htmlspecialchars($p['name']) ?>"
                                            data-phone="<?= htmlspecialchars($p['phone']) ?>"
                                            data-age="<?= (int)$p['age'] ?>"
                                            data-gender="<?= htmlspecialchars($p['gender']) ?>"
                                            data-address="<?= htmlspecialchars($p['address'] ?? '') ?>"
                                            class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#ea741b] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Edit">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <form method="POST" action="api/delete_patient.php" onsubmit="return confirm('Delete this patient? This cannot be undone.')" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-red-500 hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Delete">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-users text-[#7c7c7c] text-xl md:text-2xl"></i>
                                </div>
                                <p class="text-[#7c7c7c] font-semibold">No patients found.</p>
                                <p class="text-[#7c7c7c] text-xs mt-1">Add a new patient to get started.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add Patient Modal -->
<div id="addPatientModal" tabindex="-1" aria-hidden="true"
     class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">New Record</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Add New Patient</h3>
                </div>
                <button type="button" onclick="document.getElementById('addPatientModal').classList.add('hidden')"
                        class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <!-- Modal Body -->
            <div class="p-4 sm:p-6">
                <form id="addPatientForm" action="api/add_patient.php" method="POST">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Full Name *</label>
                            <input type="text" name="name" id="name" required placeholder="Patient's full name">
                        </div>
                        <div>
                            <label for="phone" class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Phone Number *</label>
                            <input type="text" name="phone" id="phone" required placeholder="01XXXXXXXXX">
                        </div>
                        <div>
                            <label for="age" class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Age</label>
                            <input type="number" name="age" id="age" min="1" max="150" placeholder="e.g. 30">
                        </div>
                        <div>
                            <label for="gender" class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Gender</label>
                            <div class="mod-dropdown" id="gender" data-name="gender" data-placeholder="Select Gender">
                                <input type="hidden" name="gender" value="Male">
                                <div class="mod-dropdown-trigger">
                                    <span class="mod-dropdown-selected">Male</span>
                                    <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                                </div>
                                <div class="mod-dropdown-panel">
                                    <div class="mod-dropdown-option is-selected" data-value="Male"><span class="opt-check"></span><span>Male</span></div>
                                    <div class="mod-dropdown-option" data-value="Female"><span class="opt-check"></span><span>Female</span></div>
                                    <div class="mod-dropdown-option" data-value="Other"><span class="opt-check"></span><span>Other</span></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="address" class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Address</label>
                            <input type="text" name="address" id="address" placeholder="Home address">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="notes" class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Medical History / Notes</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Previous conditions, allergies, etc."></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="submit"
                                class="px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 transition-all duration-300">
                            <i class="fas fa-user-plus mr-2"></i>
                            Add Patient & Generate ID
                        </button>
                        <button type="button" onclick="document.getElementById('addPatientModal').classList.add('hidden')"
                                class="px-6 py-3 bg-[#F4F7FC] text-[#7c7c7c] hover:text-[#004591] hover:bg-gray-100 text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div id="editPatientModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Edit Record</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Update Patient Info</h3>
                </div>
                <button type="button" onclick="document.getElementById('editPatientModal').classList.add('hidden')"
                        class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="editPatientForm" action="api/update_patient.php" method="POST">
                    <input type="hidden" name="id" id="editId">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="sm:col-span-2">
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Full Name *</label>
                            <input type="text" name="name" id="editName" required placeholder="Patient's full name">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Phone Number *</label>
                            <input type="text" name="phone" id="editPhone" required placeholder="01XXXXXXXXX">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Age</label>
                            <input type="number" name="age" id="editAge" min="1" max="150" placeholder="e.g. 30">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Gender</label>
                            <div class="mod-dropdown" id="editGender" data-name="gender" data-placeholder="Select Gender">
                                <input type="hidden" name="gender" value="">
                                <div class="mod-dropdown-trigger">
                                    <span class="mod-dropdown-selected">Select Gender</span>
                                    <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                                </div>
                                <div class="mod-dropdown-panel">
                                    <div class="mod-dropdown-option" data-value="Male"><span class="opt-check"></span><span>Male</span></div>
                                    <div class="mod-dropdown-option" data-value="Female"><span class="opt-check"></span><span>Female</span></div>
                                    <div class="mod-dropdown-option" data-value="Other"><span class="opt-check"></span><span>Other</span></div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Address</label>
                            <input type="text" name="address" id="editAddress" placeholder="Home address">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="submit"
                                class="px-6 py-3 bg-[#ea741b] hover:bg-[#004591] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                        <button type="button" onclick="document.getElementById('editPatientModal').classList.add('hidden')"
                                class="px-6 py-3 bg-[#F4F7FC] text-[#7c7c7c] hover:text-[#004591] hover:bg-gray-100 text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openEditPatient(btn) {
    document.getElementById('editId').value = btn.dataset.id;
    document.getElementById('editName').value = btn.dataset.name;
    document.getElementById('editPhone').value = btn.dataset.phone;
    document.getElementById('editAge').value = btn.dataset.age;
    document.getElementById('editAddress').value = btn.dataset.address;
    setModDropdown('editGender', btn.dataset.gender);
    document.getElementById('editPatientModal').classList.remove('hidden');
}
document.addEventListener('DOMContentLoaded', () => {
    const filterInput = document.getElementById('patientTableSearch');
    const tableBody = document.getElementById('patientTableBody');
    if (filterInput && tableBody) {
        filterInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            tableBody.querySelectorAll('tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }
    // Close modals on overlay click
    ['addPatientModal','editPatientModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (modal) modal.addEventListener('click', function(e) {
            if (e.target === modal) modal.classList.add('hidden');
        });
    });
    // Auto-dismiss alerts after 5s
    setTimeout(() => {
        ['successAlert','errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.transition = 'opacity 0.5s', el.style.opacity = 0, setTimeout(() => el.remove(), 500);
        });
    }, 5000);
});
</script>

<?php require_once 'components/footer.php'; ?>

