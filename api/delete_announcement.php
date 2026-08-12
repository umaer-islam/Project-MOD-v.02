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
        header("Location: ../announcements.php?error=Invalid+announcement");
        exit;
    }
    try {
        $titleStmt = $pdo->prepare("SELECT title FROM announcements WHERE id = ?");
        $titleStmt->execute([$id]);
        $aTitle = $titleStmt->fetchColumn() ?? 'Unknown';

        $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->execute([$id]);

        log_activity($pdo, 'DELETE_ANNOUNCEMENT', "Deleted announcement: {$aTitle}");
        cache_forget('pub:announcements');

        header("Location: ../announcements.php?success=Announcement+deleted");
        exit;
    } catch (PDOException) {
        header("Location: ../announcements.php?error=" . urlencode("Cannot delete announcement."));
        exit;
    }
}
header("Location: ../announcements.php");
exit;

