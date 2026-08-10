<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

$id = (int)($_POST['id'] ?? 0);
if (!$id) { header('Location: ../cases.php?error=Invalid+ID'); exit; }

try {
    $stmt = $pdo->prepare("SELECT before_image, after_image, title FROM before_after_cases WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        foreach (['before_image', 'after_image'] as $col) {
            $f = '../' . $row[$col];
            if (file_exists($f)) @unlink($f);
        }
        $pdo->prepare("DELETE FROM before_after_cases WHERE id = ?")->execute([$id]);

        log_activity($pdo, 'DELETE_CASE_STUDY', "Deleted case study: {$row['title']}");

        header('Location: ../cases.php?success=Case+deleted.');
    } else {
        header('Location: ../cases.php?error=Case+not+found.');
    }
} catch (PDOException $e) {
    header('Location: ../cases.php?error=Database+error.');
}
exit;
