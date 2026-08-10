<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

$id         = (int)($_POST['id'] ?? 0);
$caption    = trim($_POST['caption'] ?? '');
$sort_order = (int)($_POST['sort_order'] ?? 0);

if (!$id) { header('Location: ../gallery.php?error=Invalid+ID'); exit; }

try {
    $pdo->prepare("UPDATE gallery SET caption = ?, sort_order = ? WHERE id = ?")
        ->execute([$caption, $sort_order, $id]);
    log_activity($pdo, 'UPDATE_GALLERY_IMAGE', "Updated gallery image: " . ($caption ?: 'Untitled'));
    header('Location: ../gallery.php?success=Image+updated.');
} catch (PDOException $e) {
    header('Location: ../gallery.php?error=Database+error.');
}
exit;
