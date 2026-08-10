<?php
require_once '../database/connection.php';
require_once '../components/activity_logger.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = $_POST['patient_name'] ?? '';
    $location = $_POST['location'] ?? 'Dhaka';
    $stars = $_POST['stars'] ?? 5;
    $review = $_POST['review'] ?? '';

    // Automatically set status to Pending
    $status = 'Pending';

    $pid = $_POST['pid'] ?? '';

    if (empty(trim($patient_name)) || empty(trim($review))) {
        if (!empty($pid)) {
            header('Location: ../patient_record.php?pid=' . urlencode($pid) . '&error=' . urlencode('Please fill all fields.'));
        } else {
            header('Location: ../index.php?error=' . urlencode('Please fill all required fields.'));
        }
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO testimonials (patient_name, location, stars, review, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$patient_name, $location, $stars, $review, $status]);

        log_activity($pdo, 'GUEST_REVIEW', "Guest review submitted by {$patient_name} ({$stars} stars) — Status: Pending", null, $patient_name);
        
        if (!empty($pid)) {
            header('Location: ../patient_record.php?pid=' . urlencode($pid) . '&review_success=1');
        } else {
            header('Location: ../index.php?review_success=1#testimonials');
        }
        exit;
    } catch (PDOException $e) {
        header('Location: ../index.php?error=' . urlencode('Failed to submit review.'));
        exit;
    }
}
