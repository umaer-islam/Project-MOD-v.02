<?php
session_start();
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';
require_once '../components/rate_limiter.php';
require_once '../components/math_captcha.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CAPTCHA
    $captcha_key    = $_POST['captcha_key'] ?? '';
    $captcha_answer = $_POST['captcha_answer'] ?? '';
    if (!MathCaptcha::verify($captcha_key, $captcha_answer)) {
        $pid = $_POST['pid'] ?? '';
        $redirect = !empty($pid) ? '../patient_record.php?pid=' . urlencode($pid) : '../index.php';
        header('Location: ' . $redirect . '?error=' . urlencode('Incorrect CAPTCHA. Please try again.'));
        exit;
    }

    // Rate limit: 3 reviews per 10 minutes per IP
    $rateLimiter = new RateLimiter($pdo);
    $rateCheck = $rateLimiter->check('add_guest_review', 3, 600, 600);
    if (!$rateCheck['allowed']) {
        $minutes = ceil($rateCheck['retry_after'] / 60);
        header('Location: ../index.php?error=' . urlencode("Too many reviews. Please try again in {$minutes} minutes."));
        exit;
    }
    $rateLimiter->record('add_guest_review');

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
        cache_forget('pub:testimonials');
        
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
