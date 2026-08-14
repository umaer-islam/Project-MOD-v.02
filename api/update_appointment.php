<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';
restrict_access(['admin', 'doctor', 'receptionist']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = trim($_POST['action'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $appointment_date = trim($_POST['appointment_date'] ?? '');
    $appointment_time = trim($_POST['appointment_time'] ?? '');

    if (!$id) {
        header("Location: ../appointments.php?error=Invalid+appointment");
        exit;
    }

    try {
        if ($action === 'complete') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
            $stmt->execute([$id]);
            log_activity($pdo, 'COMPLETE_APPOINTMENT', "Marked appointment #{$id} as completed");
            header("Location: ../appointments.php?success=Appointment+marked+as+completed");

        } elseif ($action === 'edit') {
            if (empty($appointment_date) || empty($appointment_time)) {
                header("Location: ../appointments.php?error=Date+and+time+are+required");
                exit;
            }
            $stmt = $pdo->prepare("UPDATE appointments SET appointment_date = ?, appointment_time = ?, description = ? WHERE id = ?");
            $stmt->execute([$appointment_date, $appointment_time, $description, $id]);
            log_activity($pdo, 'UPDATE_APPOINTMENT', "Updated appointment #{$id}");
            header("Location: ../appointments.php?success=Appointment+updated");

        } else {
            header("Location: ../appointments.php?error=Unknown+action");
            exit;
        }

        cache_flush('dash:');
        exit;

    } catch (PDOException) {
        header("Location: ../appointments.php?error=" . urlencode("A database error occurred."));
        exit;
    }
}

header("Location: ../appointments.php");
exit;
