<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';
require_once 'components/activity_logger.php';

$success_msg = '';
$error_msg   = '';
$user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($name && $email) {
        try {
            if ($pass) {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password_hash=? WHERE id=?");
                $stmt->execute([$name, $email, $hash, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name=?, email=? WHERE id=?");
                $stmt->execute([$name, $email, $user_id]);
            }
            $_SESSION['user_name'] = $name;
            log_activity($pdo, 'UPDATE_PROFILE', "Updated own profile: {$name} (Email: {$email})");
            $success_msg = "Profile updated successfully.";
        } catch (PDOException $e) {
            $error_msg = "Error updating profile. Email might already exist.";
        }
    } else {
        $error_msg = "Name and Email are required.";
    }
}

// Fetch current user
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = null;
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div class="mb-8 max-w-2xl mx-auto">
        <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Account Settings</p>
        <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Your Profile</h1>
        <p class="text-[#7c7c7c] text-sm mt-1">Manage your login credentials and personal information.</p>
    </div>

    <div class="max-w-2xl mx-auto">
        <?php if ($success_msg): ?>
        <div class="mb-5 flex items-center bg-green-50 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium">
            <i class="fas fa-check-circle mr-3"></i> <?= $success_msg ?>
        </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
        <div class="mb-5 flex items-center bg-red-50 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium">
            <i class="fas fa-exclamation-circle mr-3"></i> <?= $error_msg ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-3xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] p-6 md:p-10">
            <form method="POST" class="space-y-6">
                <input type="hidden" name="update_profile" value="1">
                
                <div class="flex items-center gap-6 mb-8 pb-8 border-b border-gray-100">
                    <img class="w-20 h-20 rounded-2xl border-2 border-[#ea741b]/20 shadow-sm"
                         src="https://ui-avatars.com/api/?name=<?= urlencode($user['name'] ?? 'User') ?>&background=004591&color=fff&bold=true&size=200"
                         alt="User avatar">
                    <div>
                        <h3 class="font-bold text-[#004591] text-lg"><?= htmlspecialchars($user['name'] ?? '') ?></h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1"><?= htmlspecialchars($user['role'] ?? 'Admin') ?></p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required 
                               class="w-full bg-[#F4F7FC] border border-transparent rounded-xl px-4 py-3 text-sm text-[#004591] focus:border-[#ea741b] focus:bg-white transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required
                               class="w-full bg-[#F4F7FC] border border-transparent rounded-xl px-4 py-3 text-sm text-[#004591] focus:border-[#ea741b] focus:bg-white transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Change Password <span class="text-gray-400 font-normal lowercase tracking-normal">(Leave blank to keep current)</span></label>
                    <input type="password" name="password" placeholder="Enter new password to change"
                           class="w-full bg-[#F4F7FC] border border-transparent rounded-xl px-4 py-3 text-sm text-[#004591] focus:border-[#ea741b] focus:bg-white transition-all outline-none">
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-8 py-3.5 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/30 transition-all duration-300 transform hover:-translate-y-0.5">
                        <i class="fas fa-save md:mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
