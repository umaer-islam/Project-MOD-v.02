<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';
require_once 'components/activity_logger.php';
restrict_access(['admin', 'doctor', 'receptionist']);

// Safe DB fetch
try {
    $stmt = $pdo->query("SELECT * FROM contact_inquiries ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
    // Mark unread messages as read when the admin views this page
    $unreadStmt = $pdo->query("UPDATE contact_inquiries SET status = 'read' WHERE status = 'unread'");
    if ($unreadStmt->rowCount() > 0) {
        log_activity($pdo, 'MARK_MESSAGES_READ', "Marked {$unreadStmt->rowCount()} unread messages as read");
    }
} catch (PDOException $e) {
    $messages = [];
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div class="mb-8">
        <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Communications</p>
        <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Patient Inquiries</h1>
        <p class="text-[#7c7c7c] text-sm mt-1">Messages sent from the landing page contact form.</p>
    </div>

    <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Date</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Sender Info</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Service Expected</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Message Body</th>
                        <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (count($messages) > 0): ?>
                        <?php foreach($messages as $msg): ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors items-start <?= ($msg['status'] ?? '') === 'unread' ? 'bg-orange-50/50' : '' ?>">
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] whitespace-nowrap font-medium align-top">
                                <?= date('M d, Y', strtotime($msg['created_at'])) ?><br>
                                <span class="text-xs text-gray-400"><?= date('h:i A', strtotime($msg['created_at'])) ?></span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                <p class="font-bold text-[#004591]">
                                    <?= htmlspecialchars($msg['name']) ?>
                                    <?php if(($msg['status'] ?? '') === 'unread'): ?>
                                        <span class="ml-2 inline-flex items-center justify-center bg-red-100 text-red-600 text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full">New</span>
                                    <?php endif; ?>
                                </p>
                                <p class="text-xs text-gray-500 mt-1"><i class="fas fa-phone mr-1"></i> <a href="tel:<?= htmlspecialchars($msg['phone']) ?>" class="hover:text-[#ea741b]"><?= htmlspecialchars($msg['phone']) ?></a></p>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 align-top">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold whitespace-nowrap">
                                    <?= htmlspecialchars($msg['service'] ?: 'General Inquiry') ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] align-top text-sm leading-relaxed max-w-md whitespace-pre-wrap"><?= htmlspecialchars($msg['message'] ?: 'No message provided.') ?></td>
                            <td class="whitespace-nowrap px-6 py-4 align-top text-right">
                                <form action="api/delete_message.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($msg['id']) ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition-colors bg-red-50 hover:bg-red-100 p-2 rounded-lg" title="Delete Message">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                                    <i class="far fa-envelope-open text-[#7c7c7c] text-xl md:text-2xl"></i>
                                </div>
                                <p class="text-[#7c7c7c] font-semibold">No inquiries received yet.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
