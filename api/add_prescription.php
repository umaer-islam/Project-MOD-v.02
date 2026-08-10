<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
restrict_access(['admin', 'doctor']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id   = (int)($_POST['patient_id'] ?? 0);
    $diagnosis    = trim($_POST['diagnosis'] ?? '');
    $medicines_raw = trim($_POST['medicines_raw'] ?? '');
    $advice       = trim($_POST['advice'] ?? '');
    $doctor_id    = $_SESSION['user_id'] ?? null;

    if (!$patient_id || empty($diagnosis)) {
        header("Location: ../prescriptions.php?error=Patient+and+Diagnosis+are+required");
        exit;
    }

    // Parse medicines: one per line "Name | Dose | Duration"
    $medicines = [];
    foreach (explode("\n", $medicines_raw) as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        $parts = array_map('trim', explode('|', $line));
        $medicines[] = [
            'name'     => $parts[0] ?? '',
            'dose'     => $parts[1] ?? '',
            'duration' => $parts[2] ?? '',
        ];
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO prescriptions (patient_id, doctor_id, diagnosis, medicines, advice) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $diagnosis, json_encode($medicines), $advice]);

        // Get patient name for logging
        $pStmt = $pdo->prepare("SELECT name FROM patients WHERE id = ?");
        $pStmt->execute([$patient_id]);
        $pName = $pStmt->fetchColumn() ?? 'Unknown';

        log_activity($pdo, 'ADD_PRESCRIPTION', "Created prescription for {$pName}: {$diagnosis}");

        header("Location: ../prescriptions.php?success=Prescription+saved");
        exit;
    } catch (PDOException) {
        header("Location: ../prescriptions.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
}
header("Location: ../prescriptions.php");
exit;

