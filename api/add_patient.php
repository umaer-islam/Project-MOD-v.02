<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';
require_once '../components/patient_id_generator.php';
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

        // Generate unique MOD-XXXX patient ID
        $patient_id = get_next_patient_id($pdo);

        // Generate secure access token for patient portal
        $access_token = generate_access_token();

        // Insert Patient with access token
        $insert = $pdo->prepare("INSERT INTO patients (patient_id, name, phone, age, gender, address, notes, access_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->execute([$patient_id, $name, $phone, $age, $gender, $address, $notes, $access_token]);
        $db_id = $pdo->lastInsertId();

        // Generate QR code linking to patient portal with token
        $portal_url = "https://mamunorthodental.com/patient_record.php?pid={$patient_id}&token={$access_token}";
        $qr_url = generateQR($portal_url, $patient_id);

        // Update patient record with QR code path
        if ($qr_url) {
            $updateQr = $pdo->prepare("UPDATE patients SET qr_code_path = ? WHERE id = ?");
            $updateQr->execute([$qr_url, $db_id]);
        }

        $pdo->commit();

        log_activity($pdo, 'ADD_PATIENT', "Added new patient: {$name} (ID: {$patient_id}, Phone: {$phone})");

        header("Location: ../patients.php?success=Patient+added");
        cache_flush('dash:');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('[ADD_PATIENT FAILED] ' . $e->getMessage());
        header("Location: ../patients.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
} else {
    header("Location: ../patients.php");
    exit;
}
?>
