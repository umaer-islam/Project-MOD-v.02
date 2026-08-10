<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
restrict_access(['admin', 'doctor']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header("Location: ../prescriptions.php?error=Invalid+prescription");
        exit;
    }
    try {
        // Get prescription info before deleting
        $infoStmt = $pdo->prepare("SELECT p.id, pt.name FROM prescriptions p LEFT JOIN patients pt ON p.patient_id = pt.id WHERE p.id = ?");
        $infoStmt->execute([$id]);
        $info = $infoStmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM prescriptions WHERE id = ?");
        $stmt->execute([$id]);

        if ($info) {
            log_activity($pdo, 'DELETE_PRESCRIPTION', "Deleted prescription #{$id} for {$info['name']}");
        }

        header("Location: ../prescriptions.php?success=Prescription+deleted");
        exit;
    } catch (PDOException) {
        header("Location: ../prescriptions.php?error=" . urlencode("Cannot delete prescription."));
        exit;
    }
}
header("Location: ../prescriptions.php");
exit;

