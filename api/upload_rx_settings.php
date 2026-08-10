<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

header('Content-Type: application/json');
restrict_access(['admin', 'doctor']);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Safe schema upgrade (run inline to ensure column presence)
if ($pdo !== null) {
    try {
        $pdo->query("SELECT rx_template_path FROM users LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN rx_template_path VARCHAR(255) DEFAULT NULL");
            $pdo->exec("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL");
        } catch (Exception $ex) {}
    }
}

$template_path = null;
$signature_path = null;

// Helper function for safe upload
function uploadFile($fileKey, $subFolder, $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf']) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $file = $_FILES[$fileKey];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['error' => 'Invalid file format. Only JPG, PNG, and PDF are allowed.'];
    }

    $uploadDir = __DIR__ . '/../uploads/' . $subFolder . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    // Generate unique name to prevent collisions
    $filename = $subFolder . '_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . $subFolder . '/' . $filename;
    }

    return ['error' => 'Failed to move uploaded file. Check directory permissions.'];
}

// 1. Process prescription template background
if (isset($_FILES['template_file']) && $_FILES['template_file']['size'] > 0) {
    $res = uploadFile('template_file', 'templates', ['image/jpeg', 'image/png', 'application/pdf']);
    if (is_array($res) && isset($res['error'])) {
        echo json_encode(['status' => 'error', 'message' => $res['error']]);
        exit;
    }
    $template_path = $res;
}

// 2. Process digital signature
if (isset($_FILES['signature_file']) && $_FILES['signature_file']['size'] > 0) {
    $res = uploadFile('signature_file', 'signatures', ['image/jpeg', 'image/png']);
    if (is_array($res) && isset($res['error'])) {
        echo json_encode(['status' => 'error', 'message' => $res['error']]);
        exit;
    }
    $signature_path = $res;
}

try {
    // Fetch current settings to preserve existing ones if only one is uploaded
    $stmt = $pdo->prepare("SELECT rx_template_path, signature_path FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $curr = $stmt->fetch();

    $final_template = $template_path ?? $curr['rx_template_path'] ?? null;
    $final_signature = $signature_path ?? $curr['signature_path'] ?? null;

    $update = $pdo->prepare("UPDATE users SET rx_template_path = ?, signature_path = ? WHERE id = ?");
    $update->execute([$final_template, $final_signature, $user_id]);

    log_activity($pdo, 'UPDATE_RX_SETTINGS', "Updated prescription template and signature settings");

    echo json_encode([
        'status' => 'success',
        'message' => 'Settings saved successfully.',
        'template_path' => $final_template,
        'signature_path' => $final_signature
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
