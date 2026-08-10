<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cases.php');
    exit;
}

$uploadDir = '../uploads/cases/';
if (!is_dir($uploadDir))
    mkdir($uploadDir, 0755, true);

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (empty($title)) {
    header('Location: ../cases.php?error=Title+is+required.');
    exit;
}

function uploadCaseImage(string $key, string $uploadDir, array $allowed): string|false
{
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK)
        return false;
    $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed))
        return false;
    $filename = $key . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . $filename;
    if (!move_uploaded_file($_FILES[$key]['tmp_name'], $dest))
        return false;
    return 'uploads/cases/' . $filename;
}

$beforePath = uploadCaseImage('before_image', $uploadDir, $allowed);
$afterPath = uploadCaseImage('after_image', $uploadDir, $allowed);

if (!$beforePath || !$afterPath) {
    if ($beforePath)
        @unlink('../' . $beforePath);
    if ($afterPath)
        @unlink('../' . $afterPath);
    header('Location: ../cases.php?error=Both+before+and+after+images+are+required.');
    exit;
}

try {
    $pdo->prepare("INSERT INTO before_after_cases (title, description, before_image, after_image) VALUES (?,?,?,?)")
        ->execute([$title, $description, $beforePath, $afterPath]);
    log_activity($pdo, 'ADD_CASE_STUDY', "Added before & after case study: {$title}");
    header('Location: ../cases.php?success=Case+study+added.');
}
catch (PDOException $e) {
    @unlink('../' . $beforePath);
    @unlink('../' . $afterPath);
    header('Location: ../cases.php?error=Database+error.');
}
exit;
