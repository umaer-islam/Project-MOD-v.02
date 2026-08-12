<?php
/**
 * Patient Portal API — Mamun's Ortho Dental
 * Returns complete patient data for the public portal.
 * Accessible via: ?pid=MOD-XXXX&token=xxxxx
 *
 * Developer: Umaer Islam (https://umaerislam.com)
 */
session_start();
require_once '../database/connection.php';

header('Content-Type: application/json');

$pid = trim($_GET['pid'] ?? '');
$token = trim($_GET['token'] ?? '');

if (empty($pid) || empty($token)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing patient ID or access token.']);
    exit;
}

if ($pdo === null) {
    http_response_code(503);
    echo json_encode(['status' => 'error', 'message' => 'Database not connected.']);
    exit;
}

try {
    // Fetch patient with token validation
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ? AND access_token = ? LIMIT 1");
    $stmt->execute([$pid, $token]);
    $patient = $stmt->fetch();

    if (!$patient) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Patient not found or invalid access token.']);
        exit;
    }

    $dbId = $patient['id'];

    // Fetch prescriptions with doctor names
    $rxStmt = $pdo->prepare("
        SELECT p.*, u.name AS doctor_name, u.degrees AS doctor_degrees
        FROM prescriptions p
        LEFT JOIN users u ON p.doctor_id = u.id
        WHERE p.patient_id = ?
        ORDER BY p.created_at DESC
    ");
    $rxStmt->execute([$dbId]);
    $prescriptions = $rxStmt->fetchAll();

    // Decode medicines JSON for each prescription
    foreach ($prescriptions as &$rx) {
        $rx['medicines'] = json_decode($rx['medicines'] ?? '[]', true);
    }
    unset($rx);

    // Fetch appointments with doctor names
    $aptStmt = $pdo->prepare("
        SELECT a.*, u.name AS doctor_name
        FROM appointments a
        LEFT JOIN users u ON a.doctor_id = u.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $aptStmt->execute([$dbId]);
    $appointments = $aptStmt->fetchAll();

    // Fetch payments
    $payStmt = $pdo->prepare("
        SELECT * FROM payments WHERE patient_id = ? ORDER BY created_at DESC
    ");
    $payStmt->execute([$dbId]);
    $payments = $payStmt->fetchAll();

    // Fetch cash memos with items
    $memoStmt = $pdo->prepare("
        SELECT * FROM cash_memos WHERE customer_phone = ? ORDER BY created_at DESC
    ");
    $memoStmt->execute([$patient['phone']]);
    $cashMemos = $memoStmt->fetchAll();

    foreach ($memoStmt as &$memo) {
        $itemsStmt = $pdo->prepare("SELECT * FROM cash_memo_items WHERE memo_id = ?");
        $itemsStmt->execute([$memo['id']]);
        $memo['items'] = $itemsStmt->fetchAll();
    }
    unset($memo);

    // Calculate summaries
    $totalPaid = array_sum(array_column($payments, 'amount'));
    $totalVisits = count($appointments);
    $lastVisit = $appointments[0]['appointment_date'] ?? null;
    $activeRx = null;
    foreach ($prescriptions as $rx) {
        if ($rx['follow_up'] && strtotime($rx['follow_up']) >= strtotime('today')) {
            $activeRx = $rx;
            break;
        }
    }

    // Build response
    $response = [
        'status' => 'success',
        'patient' => [
            'id' => $patient['patient_id'],
            'name' => $patient['name'],
            'phone' => $patient['phone'],
            'age' => $patient['age'],
            'gender' => $patient['gender'],
            'blood_group' => $patient['blood_group'] ?? null,
            'address' => $patient['address'] ?? null,
            'notes' => $patient['notes'] ?? null,
            'qr_code_path' => $patient['qr_code_path'] ?? null,
        ],
        'prescriptions' => $prescriptions,
        'appointments' => $appointments,
        'payments' => $payments,
        'cash_memos' => $cashMemos,
        'summary' => [
            'total_paid' => $totalPaid,
            'total_visits' => $totalVisits,
            'last_visit' => $lastVisit,
            'active_rx' => $activeRx,
            'total_prescriptions' => count($prescriptions),
        ],
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred.']);
}
?>
