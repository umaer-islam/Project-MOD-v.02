<?php
$load_ui_components = true;
require_once 'components/header.php';
restrict_access(['admin']);
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

$users = [];
if ($pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error_msg = "Database error query failed.";
    }
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Clinic Administration</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Staff Management</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Manage system access for Admins, Doctors, and Receptionists</p>
        </div>
        <button onclick="openAddUserModal()"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
            <i class="fas fa-plus text-xs"></i> Create Account
        </button>
    </div>

    <?php if ($success_msg): ?>
    <div class="mb-5 flex items-center bg-green-50 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium shadow-sm border border-green-100 animate-pulse">
        <i class="fas fa-check-circle mr-3"></i> <?= $success_msg ?>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="mb-5 flex items-center bg-red-50 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium shadow-sm border border-red-100">
        <i class="fas fa-exclamation-circle mr-3"></i> <?= $error_msg ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden text-nowrap">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="whitespace-nowrap px-6 py-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Staff Member</th>
                    <th class="whitespace-nowrap px-6 py-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold">Email Address</th>
                    <th class="whitespace-nowrap px-6 py-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-center">System Role</th>
                    <th class="whitespace-nowrap px-6 py-4 text-[10px] uppercase tracking-widest text-gray-400 font-bold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach($users as $u): ?>
                <tr class="hover:bg-[#F4F7FC]/50 transition-colors group">
                    <td class="whitespace-nowrap px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#004591]/10 text-[#004591] flex items-center justify-center font-bold text-xs ring-2 ring-white shadow-sm">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#004591]"><?= htmlspecialchars($u['name']) ?></p>
                                <p class="text-[10px] text-gray-400 font-medium">Joined: <?= date('M d, Y', strtotime($u['created_at'])) ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-5 text-sm text-gray-600 font-medium">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td class="whitespace-nowrap px-6 py-5 text-center">
                        <?php 
                        $roleColors = [
                            'admin' => 'bg-red-50 text-red-600 border-red-100',
                            'doctor' => 'bg-blue-50 text-blue-600 border-blue-100',
                            'receptionist' => 'bg-orange-50 text-orange-600 border-orange-100'
                        ];
                        $colorClass = $roleColors[strtolower($u['role'])] ?? 'bg-gray-50 text-gray-600 border-gray-100';
                        ?>
                        <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest border <?= $colorClass ?>">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-5 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="openEditUserModal(this)" 
                                    data-id="<?= $u['id'] ?>"
                                    data-name="<?= htmlspecialchars($u['name']) ?>"
                                    data-email="<?= htmlspecialchars($u['email']) ?>"
                                    data-role="<?= htmlspecialchars(strtolower($u['role'])) ?>"
                                    class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-[#004591] hover:text-white flex items-center justify-center transition-all shadow-sm">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <?php if($u['id'] != $_SESSION['user_id']): ?>
                            <form action="api/save_user.php" method="POST" onsubmit="return confirm('Permanently delete this account?')" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all shadow-sm">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>

<!-- User Modal -->
<div id="userModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4 bg-[#004591]/20 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 relative">
        <button onclick="closeUserModal()" class="absolute top-6 right-6 text-gray-400 hover:text-red-500 transition-colors"><i class="fas fa-times"></i></button>
        
        <div class="text-center mb-8">
            <h3 class="font-serif text-xl md:text-2xl font-bold text-[#004591]" id="modalTitle">Manage Account</h3>
            <p class="text-[10px] font-bold text-[#ea741b] uppercase tracking-widest mt-1">Staff Access Credentials</p>
        </div>

        <form action="api/save_user.php" method="POST" class="space-y-5">
            <input type="hidden" name="action" id="userAction" value="add">
            <input type="hidden" name="id" id="userId" value="">

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Staff Name</label>
                <input type="text" name="name" id="userName" required class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#ea741b]/50 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Email Address</label>
                <input type="email" name="email" id="userEmail" required class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#ea741b]/50 focus:bg-white transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1">System Role</label>
                <div class="mod-dropdown" id="userRole" data-name="role" data-placeholder="Select Role">
                    <input type="hidden" name="role" value="receptionist" required>
                    <div class="mod-dropdown-trigger">
                        <span class="mod-dropdown-selected">Receptionist</span>
                        <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                    </div>
                    <div class="mod-dropdown-panel">
                        <div class="mod-dropdown-option is-selected" data-value="receptionist"><span class="opt-check"></span><span>Receptionist</span></div>
                        <div class="mod-dropdown-option" data-value="doctor"><span class="opt-check"></span><span>Doctor</span></div>
                        <div class="mod-dropdown-option" data-value="admin"><span class="opt-check"></span><span>Administrator</span></div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1.5 ml-1" id="pwLabel">Account Password</label>
                <input type="password" name="password" id="userPassword" class="w-full border-2 border-gray-100 bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#ea741b]/50 focus:bg-white transition-all" placeholder="Enter password...">
            </div>

            <button type="submit" class="w-full py-4 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all duration-300 shadow-xl shadow-[#004591]/20 mt-4">
                Save Staff Member
            </button>
        </form>
    </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('modalTitle').innerText = 'New Staff Account';
    document.getElementById('userAction').value = 'add';
    document.getElementById('userId').value = '';
    document.getElementById('userName').value = '';
    document.getElementById('userEmail').value = '';
    setModDropdown('userRole', 'receptionist');
    document.getElementById('userPassword').required = true;
    document.getElementById('pwLabel').innerText = 'Set Initial Password';
    document.getElementById('userModal').classList.remove('hidden');
}

function openEditUserModal(btn) {
    document.getElementById('modalTitle').innerText = 'Edit Staff Member';
    document.getElementById('userAction').value = 'update';
    document.getElementById('userId').value = btn.dataset.id;
    document.getElementById('userName').value = btn.dataset.name;
    document.getElementById('userEmail').value = btn.dataset.email;
    setModDropdown('userRole', btn.dataset.role);
    document.getElementById('userPassword').required = false;
    document.getElementById('pwLabel').innerText = 'Change Password (Leave blank to keep)';
    document.getElementById('userModal').classList.remove('hidden');
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}
document.getElementById('userModal')?.addEventListener('click', e => { if(e.target.id === 'userModal') closeUserModal(); });
</script>

<?php require_once 'components/footer.php'; ?>
