<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = (int)($_POST['patient_id'] ?? 0);
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $doctor_id = $_SESSION['user_id'] ?? null;

    if (!$patient_id || empty($appointment_date) || empty($appointment_time)) {
        header("Location: ../appointments.php?error=Missing+required+fields");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $description]);
        
        // Get patient name for logging
        $pStmt = $pdo->prepare("SELECT name FROM patients WHERE id = ?");
        $pStmt->execute([$patient_id]);
        $pName = $pStmt->fetchColumn() ?? 'Unknown';

        log_activity($pdo, 'BOOK_APPOINTMENT', "Booked appointment for {$pName} on {$appointment_date} at {$appointment_time}");
        
        header("Location: ../appointments.php?success=Appointment+booked");
        cache_flush('dash:');
        exit;

    } catch (PDOException) {
        header("Location: ../appointments.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
} else {
    header("Location: ../appointments.php");
    exit;
}



