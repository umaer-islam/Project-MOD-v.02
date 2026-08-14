<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';
restrict_access(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    if (!$id) {
        header("Location: ../appointments.php?error=Invalid+appointment");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$id]);
        log_activity($pdo, 'DELETE_APPOINTMENT', "Deleted appointment #{$id}");
        cache_flush('dash:');
        header("Location: ../appointments.php?success=Appointment+deleted+permanently");
        exit;
    } catch (PDOException) {
        header("Location: ../appointments.php?error=" . urlencode("Failed to delete appointment."));
        exit;
    }
}

header("Location: ../appointments.php");
exit;
