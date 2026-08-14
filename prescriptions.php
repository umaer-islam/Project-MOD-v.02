<?php
$load_ui_components = true;
require_once 'components/header.php';
restrict_access(['admin', 'doctor']);
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

try {
    $stmt = $pdo->query("SELECT pr.*, p.name as patient_name, p.patient_id as p_id, d.name as doctor_name FROM prescriptions pr JOIN patients p ON pr.patient_id = p.id LEFT JOIN users d ON pr.doctor_id = d.id ORDER BY pr.created_at DESC LIMIT 50");
    $prescriptions = $stmt->fetchAll();
    $patientsStmt = $pdo->query("SELECT id, name, patient_id FROM patients ORDER BY name ASC");
    $allPatients = $patientsStmt->fetchAll();
} catch (PDOException $e) {
    $prescriptions = []; $allPatients = [];
    $error_msg = "Error fetching prescriptions.";
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Clinical Records</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Prescriptions</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Digital prescriptions and patient QR codes</p>
        </div>
        <a href="create_prescription.php" class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-file-medical text-xs"></i> New Prescription
        </a>
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

    <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Date</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Patient</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Diagnosis</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Doctor</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (count($prescriptions) > 0): ?>
                        <?php foreach($prescriptions as $pr): ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] whitespace-nowrap font-medium"><?= date('M d, Y', strtotime($pr['created_at'])) ?></td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#e8f0fa] flex items-center justify-center text-[#004591] text-sm font-bold flex-shrink-0">
                                        <?= strtoupper(substr($pr['patient_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-[#004591]"><?= htmlspecialchars($pr['patient_name']) ?></p>
                                        <p class="text-[10px] text-[#7c7c7c]"><?= htmlspecialchars($pr['p_id']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] max-w-[200px] truncate" title="<?= htmlspecialchars($pr['diagnosis']) ?>">
                                <?= htmlspecialchars($pr['diagnosis']) ?>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="flex items-center gap-2 text-[#004591] font-semibold text-sm">
                                    <div class="w-7 h-7 rounded-full bg-[#e8f0fa] flex items-center justify-center text-[#004591] text-xs font-bold">
                                        <?= strtoupper(substr($pr['doctor_name'] ?? 'M', 0, 1)) ?>
                                    </div>
                                    Dr. <?= htmlspecialchars($pr['doctor_name'] ?? 'Mamun') ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <?php 
                                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                                    $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
                                    $recordUrl = $baseUrl . '/patient_record.php?pid=' . urlencode($pr['p_id']);
                                    ?>
                                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&color=004591&data=<?= urlencode($recordUrl) ?>" target="_blank"
                                       class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#004591] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="View QR">
                                        <i class="fas fa-qrcode text-xs"></i>
                                    </a>
                                    <a href="print_prescription.php?id=<?= $pr['id'] ?>" target="_blank" class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#ea741b] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Print Prescription">
                                        <i class="fas fa-print text-xs"></i>
                                    </a>
                                    <form method="POST" action="api/delete_prescription.php" onsubmit="return confirm('Delete this prescription? This cannot be undone.')" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $pr['id'] ?>">
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
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-pills text-[#7c7c7c] text-xl md:text-2xl"></i>
                                </div>
                                <p class="text-[#7c7c7c] font-semibold">No prescriptions generated yet.</p>
                                <p class="text-[#7c7c7c] text-xs mt-1">Create prescriptions from the Prescriptions page.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>

<!-- Add Prescription Modal -->
<div id="addPrescriptionModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-2xl">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">New Record</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Write Prescription</h3>
                </div>
                <button onclick="document.getElementById('addPrescriptionModal').classList.add('hidden')" class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="api/add_prescription.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Select Patient *</label>
                    <div class="mod-dropdown" data-name="patient_id" data-placeholder="-- Choose Patient --">
                        <input type="hidden" name="patient_id" value="" required>
                        <div class="mod-dropdown-trigger">
                            <span class="mod-dropdown-selected">-- Choose Patient --</span>
                            <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                        </div>
                        <div class="mod-dropdown-panel">
                            <div class="mod-dropdown-option" data-value=""><span class="opt-check"></span><span>-- Choose Patient --</span></div>
                            <?php foreach($allPatients as $p): ?>
                            <div class="mod-dropdown-option" data-value="<?= $p['id'] ?>"><span class="opt-check"></span><span><?= htmlspecialchars($p['name']) ?> (<?= $p['patient_id'] ?>)</span></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Diagnosis *</label>
                    <input type="text" name="diagnosis" required placeholder="e.g., Dental caries on lower molar">
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Medicines <span class="text-[#7c7c7c] normal-case tracking-normal font-normal">(one per line: Name | Dose | Duration)</span></label>
                    <textarea name="medicines_raw" rows="4" placeholder="e.g.&#10;Amoxicillin | 500mg | 7 days&#10;Paracetamol | 500mg | As needed"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Advice / Instructions</label>
                    <textarea name="advice" rows="2" placeholder="e.g., Avoid hard food for 2 weeks, follow-up in 10 days"></textarea>
                </div>
                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit" class="px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                        <i class="fas fa-save mr-2"></i> Save Prescription
                    </button>
                    <button type="button" onclick="document.getElementById('addPrescriptionModal').classList.add('hidden')" class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('addPrescriptionModal');
    if (modal) modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
    setTimeout(() => {
        ['successAlert','errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = 0; setTimeout(() => el.remove(), 500); }
        });
    }, 5000);
});
</script>
