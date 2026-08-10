<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
restrict_access(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header("Location: ../payments.php?tab=memos&error=Invalid+cash+memo");
        exit;
    }
    try {
        // Get memo info before deleting
        $infoStmt = $pdo->prepare("SELECT memo_number, customer_name, grand_total FROM cash_memos WHERE id = ?");
        $infoStmt->execute([$id]);
        $info = $infoStmt->fetch();

        // Items cascade-delete automatically via FK
        $stmt = $pdo->prepare("DELETE FROM cash_memos WHERE id = ?");
        $stmt->execute([$id]);

        if ($info) {
            log_activity($pdo, 'DELETE_CASH_MEMO', "Deleted cash memo {$info['memo_number']} for {$info['customer_name']} — Total: ৳{$info['grand_total']}");
        }

        header("Location: ../payments.php?tab=memos&success=" . urlencode("Cash memo deleted."));
        exit;
    } catch (PDOException) {
        header("Location: ../payments.php?tab=memos&error=" . urlencode("Cannot delete cash memo."));
        exit;
    }
}
header("Location: ../payments.php?tab=memos");
exit;
