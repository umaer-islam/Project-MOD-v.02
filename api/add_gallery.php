<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../gallery.php');
    exit;
}

// Ensure uploads dir exists
$uploadDir = '../uploads/gallery/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$caption    = trim($_POST['caption'] ?? '');
$sort_order = (int)($_POST['sort_order'] ?? 0);

// Handle file upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../gallery.php?error=Please+select+a+valid+image+file.');
    exit;
}

$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
if (!in_array($ext, $allowed)) {
    header('Location: ../gallery.php?error=Only+JPG,+PNG,+WebP+or+GIF+allowed.');
    exit;
}

$filename  = 'gallery_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath  = $uploadDir . $filename;
$dbPath    = 'uploads/gallery/' . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
    header('Location: ../gallery.php?error=File+upload+failed.');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO gallery (image_path, caption, sort_order) VALUES (?, ?, ?)");
    $stmt->execute([$dbPath, $caption, $sort_order]);
    log_activity($pdo, 'UPLOAD_GALLERY_IMAGE', "Uploaded gallery image: " . ($caption ?: 'Untitled'));
    header('Location: ../gallery.php?success=Image+added+successfully.');
} catch (PDOException $e) {
    @unlink($destPath);
    header('Location: ../gallery.php?error=Database+error+occurred.');
}
exit;
