<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/rate_limiter.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Rate limit: 30 searches per minute per IP
$rateLimiter = new RateLimiter($pdo);
$rateCheck = $rateLimiter->check('search_patient', 30, 60, 0);
if (!$rateCheck['allowed']) {
    echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please slow down.']);
    exit;
}

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

try {
    $search = "%{$query}%";
    $stmt = $pdo->prepare("SELECT id, patient_id, name, phone, address FROM patients WHERE name LIKE ? OR phone LIKE ? OR patient_id LIKE ? LIMIT 10");
    $stmt->execute([$search, $search, $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $results]);
} catch (PDOException) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

