<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT age, weight, blood_group FROM patients WHERE id = ?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch();
    
    if ($patient) {
        echo json_encode(['status' => 'success', 'data' => $patient]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Not found']);
    }
} catch (PDOException) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

