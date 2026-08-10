<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_page.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) {
        header("Location: ../messages.php?error=Invalid+message");
        exit;
    }
    try {
        $infoStmt = $pdo->prepare("SELECT name, message FROM contact_inquiries WHERE id = ?");
        $infoStmt->execute([$id]);
        $info = $infoStmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM contact_inquiries WHERE id = ?");
        $stmt->execute([$id]);

        if ($info) {
            $preview = mb_substr($info['message'], 0, 50);
            log_activity($pdo, 'DELETE_MESSAGE', "Deleted inquiry from {$info['name']}: \"{$preview}...\"");
        }

        header("Location: ../messages.php?success=Message+deleted");
        exit;
    } catch (PDOException $e) {
        header("Location: ../messages.php?error=" . urlencode("Cannot delete message."));
        exit;
    }
}
header("Location: ../messages.php");
exit;
