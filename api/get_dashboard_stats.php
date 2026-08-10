<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    // Collect stats array
    $stats = [];
    
    // Today's Patients (seen in appointments)
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status != 'Cancelled'");
    $stats['today_patients'] = $stmt->fetchColumn() ?: 0;
    
    // Payments (Weekly)
    $stmt = $pdo->query("SELECT SUM(amount) FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stats['payments_collected'] = $stmt->fetchColumn() ?: 0;
    
    // Pending Follow-ups
    $stats['pending_followups'] = 0;
    
    // Recent new patients
    $stmt = $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()");
    $stats['new_patients'] = $stmt->fetchColumn() ?: 0;

    echo json_encode(['status' => 'success', 'data' => $stats]);

} catch (PDOException) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

