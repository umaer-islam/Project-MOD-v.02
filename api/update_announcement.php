<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login_page.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)($_POST['id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $visibility  = in_array($_POST['visibility'] ?? '', ['Public', 'Staff']) ? $_POST['visibility'] : 'Public';
    $expiry_date = trim($_POST['expiry_date'] ?? '') ?: null;

    if (!$id || empty($title)) {
        header("Location: ../announcements.php?error=Title+is+required");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE announcements SET title = ?, description = ?, visibility = ?, expiry_date = ? WHERE id = ?");
        $stmt->execute([$title, $description, $visibility, $expiry_date, $id]);
        log_activity($pdo, 'UPDATE_ANNOUNCEMENT', "Updated announcement: {$title}");
        header("Location: ../announcements.php?success=Announcement+updated");
        cache_forget('pub:announcements');
        cache_forget('dash:notices');
        exit;
    } catch (PDOException) {
        header("Location: ../announcements.php?error=" . urlencode("A database error occurred. Please try again."));
        exit;
    }
}
header("Location: ../announcements.php");
exit;


