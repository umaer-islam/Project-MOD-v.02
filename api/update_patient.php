<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$id || empty($name) || empty($phone)) {
        header("Location: ../patients.php?error=Missing+required+fields");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE patients SET name = ?, phone = ?, age = ?, gender = ?, address = ?, notes = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $age, $gender, $address, $notes, $id]);
        
        log_activity($pdo, 'UPDATE_PATIENT', "Updated patient record: {$name} (Phone: {$phone})");
        
        header("Location: ../patients.php?success=Patient+updated");
        exit;
    } catch (PDOException) {
        header("Location: ../patients.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
} else {
    header("Location: ../patients.php");
    exit;
}


