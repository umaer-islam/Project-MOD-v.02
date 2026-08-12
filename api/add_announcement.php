<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';
restrict_access(['admin']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visibility  = in_array($_POST['visibility'] ?? '', ['Public', 'Staff']) ? $_POST['visibility'] : 'Public';
    $expiry_date = trim($_POST['expiry_date'] ?? '') ?: null;

    if (empty($title)) {
        header("Location: ../announcements.php?error=Title+is+required");
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO announcements (title, description, date_posted, expiry_date, visibility) VALUES (?, ?, CURDATE(), ?, ?)");
        $stmt->execute([$title, $description, $expiry_date, $visibility]);
        log_activity($pdo, 'ADD_ANNOUNCEMENT', "Published announcement: {$title} (Visibility: {$visibility})");
        cache_forget('pub:announcements');
        header("Location: ../announcements.php?success=Announcement+published");
        exit;
    } catch (PDOException) {
        header("Location: ../announcements.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
}
header("Location: ../announcements.php");
exit;


