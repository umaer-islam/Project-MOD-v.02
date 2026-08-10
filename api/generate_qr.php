<?php
/**
 * Helper function to generate QR codes.
 * In production, you might use a PHP library like phpqrcode or endroid/qr-code.
 * For this MVP layout, we'll use Google Chart API to generate a QR image URL and save it.
 */

function generateQR($data, $filename_prefix) {
    $size = "300x300";
    $url = "https://chart.googleapis.com/chart?chs={$size}&cht=qr&chl=" . urlencode($data);
    
    $upload_dir = __DIR__ . '/../assets/qr/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filename = $filename_prefix . '_' . time() . '.png';
    $filepath = $upload_dir . $filename;
    
    // Download and save image locally
    $image_content = @file_get_contents($url);
    if ($image_content) {
        file_put_contents($filepath, $image_content);
        return 'assets/qr/' . $filename;
    }
    
    return null; // On failure
}

// If accessed directly via POST (from JS AJAX maybe)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && basename($_SERVER['PHP_SELF']) === 'generate_qr.php') {
    header('Content-Type: application/json');
    $data = $_POST['data'] ?? '';
    $id = $_POST['id'] ?? 'qr';
    
    if (empty($data)) {
        echo json_encode(['status' => 'error', 'message' => 'No data provided']);
        exit;
    }
    
    $path = generateQR($data, $id);
    if ($path) {
        echo json_encode(['status' => 'success', 'path' => $path]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to generate QR code']);
    }
}
?>

