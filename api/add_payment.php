<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
restrict_access(['admin', 'doctor', 'receptionist']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? 'Cash');
    $notes = trim($_POST['notes'] ?? '');

    if (!$patient_id || $amount <= 0) {
        header("Location: ../payments.php?error=Invalid+payment+details");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO payments (patient_id, amount, payment_method, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$patient_id, $amount, $payment_method, $notes]);

        // Get patient name for logging
        $pStmt = $pdo->prepare("SELECT name FROM patients WHERE id = ?");
        $pStmt->execute([$patient_id]);
        $pName = $pStmt->fetchColumn() ?? 'Unknown';

        log_activity($pdo, 'ADD_PAYMENT', "Recorded payment of ৳{$amount} ({$payment_method}) for {$pName}");
        
        header("Location: ../payments.php?success=Payment+recorded");
        exit;

    } catch (PDOException) {
        header("Location: ../payments.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
} else {
    header("Location: ../payments.php");
    exit;
}



