<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once 'generate_qr.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($phone)) {
        header("Location: ../patients.php?error=Name+and+phone+are+required");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Count for generating MOD-XXXX ID
        $stmt = $pdo->query("SELECT MAX(id) FROM patients FOR UPDATE");
        $count = $stmt->fetchColumn() + 1;
        $patient_id = 'MOD-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        // Insert Patient
        $insert = $pdo->prepare("INSERT INTO patients (patient_id, name, phone, age, gender, address, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$patient_id, $name, $phone, $age, $gender, $address, $notes]);
        $db_id = $pdo->lastInsertId();

        // Generate QR code (Token based secure access link)
        $token = bin2hex(random_bytes(16));
        $portal_url = "https://mamunorthodental.com/patient_record.php?pid={$patient_id}&token={$token}";
        
        // Use external service to generate QR or just store the link if library is missing
        // For local development, we'll assume a dummy or Google API QR URL
        $qr_url = generateQR($portal_url, $patient_id);
        
        // In a real app we'd update the patient record with token/qr_path here
        
        $pdo->commit();

        log_activity($pdo, 'ADD_PATIENT', "Added new patient: {$name} (ID: {$patient_id}, Phone: {$phone})");
        
        header("Location: ../patients.php?success=Patient+added");
        exit;

    } catch (PDOException) {
        $pdo->rollBack();
        header("Location: ../patients.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
} else {
    header("Location: ../patients.php");
    exit;
}

