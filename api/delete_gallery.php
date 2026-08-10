<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: ../gallery.php?error=Invalid+ID'); exit; }

try {
    $stmt = $pdo->prepare("SELECT image_path, caption FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $filePath = '../' . $row['image_path'];
        if (file_exists($filePath)) @unlink($filePath);
        $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);

        log_activity($pdo, 'DELETE_GALLERY_IMAGE', "Deleted gallery image: " . ($row['caption'] ?: 'Untitled'));

        header('Location: ../gallery.php?success=Image+deleted.');
    } else {
        header('Location: ../gallery.php?error=Image+not+found.');
    }
} catch (PDOException $e) {
    header('Location: ../gallery.php?error=Database+error.');
}
exit;
