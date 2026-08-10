<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header("Location: ../patients.php?error=Invalid+patient");
        exit;
    }
    try {
        // Get patient name before deleting
        $nameStmt = $pdo->prepare("SELECT name, patient_id FROM patients WHERE id = ?");
        $nameStmt->execute([$id]);
        $patient = $nameStmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);

        if ($patient) {
            log_activity($pdo, 'DELETE_PATIENT', "Deleted patient: {$patient['name']} (ID: {$patient['patient_id']})");
        }

        header("Location: ../patients.php?success=Patient+deleted");
        exit;
    } catch (PDOException) {
        header("Location: ../patients.php?error=" . urlencode("Cannot delete: patient has linked records."));
        exit;
    }
}
header("Location: ../patients.php");
exit;

