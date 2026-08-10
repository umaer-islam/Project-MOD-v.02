<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
restrict_access(['admin', 'doctor', 'receptionist']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header("Location: ../payments.php?error=Invalid+payment");
        exit;
    }
    try {
        // Get payment info before deleting
        $infoStmt = $pdo->prepare("SELECT py.amount, py.payment_method, pt.name FROM payments py LEFT JOIN patients pt ON py.patient_id = pt.id WHERE py.id = ?");
        $infoStmt->execute([$id]);
        $info = $infoStmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
        $stmt->execute([$id]);

        if ($info) {
            log_activity($pdo, 'DELETE_PAYMENT', "Deleted payment record #{$id}: ৳{$info['amount']} ({$info['payment_method']}) for {$info['name']}");
        }

        header("Location: ../payments.php?success=Payment+record+deleted");
        exit;
    } catch (PDOException) {
        header("Location: ../payments.php?error=" . urlencode("Cannot delete payment record."));
        exit;
    }
}
header("Location: ../payments.php");
exit;

