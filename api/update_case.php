<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

$id          = (int)($_POST['id'] ?? 0);
$title       = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$allowed     = ['jpg','jpeg','png','webp'];

if (!$id || empty($title)) {
    header('Location: ../cases.php?error=Invalid+data.');
    exit;
}

$uploadDir = '../uploads/cases/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

try {
    // Fetch existing images so we can replace individually
    $existing = $pdo->prepare("SELECT before_image, after_image FROM before_after_cases WHERE id = ?");
    $existing->execute([$id]);
    $row = $existing->fetch();
    if (!$row) { header('Location: ../cases.php?error=Case+not+found.'); exit; }

    $beforePath = $row['before_image'];
    $afterPath  = $row['after_image'];

    // Optional re-upload for before
    if (isset($_FILES['before_image']) && $_FILES['before_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['before_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newFile = 'before_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['before_image']['tmp_name'], $uploadDir . $newFile)) {
                @unlink('../' . $beforePath);
                $beforePath = 'uploads/cases/' . $newFile;
            }
        }
    }

    // Optional re-upload for after
    if (isset($_FILES['after_image']) && $_FILES['after_image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['after_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newFile = 'after_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['after_image']['tmp_name'], $uploadDir . $newFile)) {
                @unlink('../' . $afterPath);
                $afterPath = 'uploads/cases/' . $newFile;
            }
        }
    }

    $pdo->prepare("UPDATE before_after_cases SET title=?, description=?, before_image=?, after_image=? WHERE id=?")
        ->execute([$title, $description, $beforePath, $afterPath, $id]);

    log_activity($pdo, 'UPDATE_CASE_STUDY', "Updated case study: {$title}");
    header('Location: ../cases.php?success=Case+updated.');
} catch (PDOException $e) {
    header('Location: ../cases.php?error=Database+error.');
}
exit;
