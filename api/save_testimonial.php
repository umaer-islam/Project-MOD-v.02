<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
restrict_access(['admin', 'doctor', 'receptionist']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$action = $_POST['action'] ?? 'add';
$id = $_POST['id'] ?? null;
$patient_name = trim($_POST['patient_name'] ?? '');
$location = trim($_POST['location'] ?? 'Dhaka');
$stars = (int)($_POST['stars'] ?? 5);
$review = trim($_POST['review'] ?? '');
$status = $_POST['status'] ?? 'Published';

try {
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO testimonials (patient_name, location, stars, review, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_name, $location, $stars, $review, $status]);
        log_activity($pdo, 'ADD_REVIEW', "Added review for {$patient_name} ({$stars} stars)");
        header("Location: ../testimonials.php?success=" . urlencode("Review added."));
    } elseif ($action === 'update' && $id) {
        $stmt = $pdo->prepare("UPDATE testimonials SET patient_name=?, location=?, stars=?, review=?, status=? WHERE id=?");
        $stmt->execute([$patient_name, $location, $stars, $review, $status, $id]);
        log_activity($pdo, 'UPDATE_REVIEW', "Updated review for {$patient_name}");
        header("Location: ../testimonials.php?success=" . urlencode("Review updated."));
    } elseif ($action === 'delete' && $id) {
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id=?");
        $stmt->execute([$id]);
        log_activity($pdo, 'DELETE_REVIEW', "Deleted review for {$patient_name}");
        header("Location: ../testimonials.php?success=" . urlencode("Review deleted."));
    } elseif ($action === 'approve' && $id) {
        $stmt = $pdo->prepare("UPDATE testimonials SET status='Published' WHERE id=?");
        $stmt->execute([$id]);
        log_activity($pdo, 'APPROVE_REVIEW', "Approved and published review for {$patient_name}");
        header("Location: ../testimonials.php?success=" . urlencode("Review approved and published."));
    }
} catch (PDOException) {
    header("Location: ../testimonials.php?error=" . urlencode("Error saving."));
}
exit;

