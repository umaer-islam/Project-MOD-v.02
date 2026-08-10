<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
header('Content-Type: application/json');
restrict_access(['admin', 'doctor']);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Programmatic DB schema upgrade
if ($pdo !== null) {
    try {
        $pdo->query("SELECT follow_up, rx_date FROM prescriptions LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("ALTER TABLE prescriptions ADD COLUMN follow_up VARCHAR(100) DEFAULT NULL");
            $pdo->exec("ALTER TABLE prescriptions ADD COLUMN rx_date DATE DEFAULT NULL");
        } catch (Exception $ex) {}
    }
    try {
        $pdo->query("SELECT investigations FROM prescriptions LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("ALTER TABLE prescriptions ADD COLUMN investigations TEXT DEFAULT NULL AFTER diagnosis");
        } catch (Exception $ex) {}
    }
}

$doctor_id = $_SESSION['user_id'];
$patient_id = $_POST['patient_id'] ?? '';
$patient_name = trim($_POST['patient_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$age = $_POST['age'] !== '' ? (int)$_POST['age'] : null;
$weight = $_POST['weight'] !== '' ? (float)$_POST['weight'] : null;
$blood_group = $_POST['blood_group'] ?? null;
$diagnosis = trim($_POST['diagnosis'] ?? '');
$investigations = trim($_POST['investigations'] ?? '');
$advice = trim($_POST['advice'] ?? '');
$follow_up = trim($_POST['follow_up'] ?? '');
$rx_date = $_POST['rx_date'] !== '' ? $_POST['rx_date'] : date('Y-m-d');

// Process Medicines
$med_names = $_POST['med_name'] ?? [];
$med_freqs = $_POST['med_frequency'] ?? [];
$med_durations = $_POST['med_duration'] ?? [];
$med_notes = $_POST['med_note'] ?? [];

$medicines = [];
for ($i = 0; $i < count($med_names); $i++) {
    if (!empty(trim($med_names[$i]))) {
        $medicines[] = [
            'name' => trim($med_names[$i]),
            'frequency' => trim($med_freqs[$i] ?? ''),
            'duration' => trim($med_durations[$i] ?? ''),
            'note' => trim($med_notes[$i] ?? ''),
        ];
    }
}

try {
    $pdo->beginTransaction();

    // 1. Create or Update Patient
    if (empty($patient_id)) {
        // Create new patient
        $stmtStatus = $pdo->query("SELECT MAX(id) FROM patients");
        $maxId = $stmtStatus->fetchColumn();
        $newPatientId = 'PT-' . date('ym') . str_pad(($maxId + 1), 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("INSERT INTO patients (patient_id, name, phone, age, weight, blood_group) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$newPatientId, $patient_name, $phone, $age, $weight, $blood_group]);
        $patient_id = $pdo->lastInsertId();
    } else {
        // Update existing patient vitals
        $stmt = $pdo->prepare("UPDATE patients SET phone = ?, age = ?, weight = ?, blood_group = ? WHERE id = ?");
        $stmt->execute([$phone, $age, $weight, $blood_group, $patient_id]);
    }

    // 2. Insert Prescription
    $medsJson = json_encode($medicines);
    // Use Google Charts API to generate a QR code pointing to this prescription or patient ID
    $qrUrl = "https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=PatientID:" . urlencode($patient_id) . "%0ADr:". urlencode($_SESSION['user_name']);

    $stmt = $pdo->prepare("INSERT INTO prescriptions (patient_id, doctor_id, diagnosis, investigations, medicines, advice, follow_up, rx_date, qr_code_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$patient_id, $doctor_id, $diagnosis, $investigations, $medsJson, $advice, $follow_up, $rx_date, $qrUrl]);
    $prescription_id = $pdo->lastInsertId();

    $pdo->commit();

    log_activity($pdo, 'ADD_PRESCRIPTION', "Created prescription for patient ID {$patient_id}: {$diagnosis}");

    echo json_encode(['status' => 'success', 'prescription_id' => $prescription_id]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
