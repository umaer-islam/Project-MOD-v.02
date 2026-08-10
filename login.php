<?php
session_start();
require_once 'database/connection.php';
require_once 'components/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_page.php");
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header("Location: login_page.php?error=1&msg=Please+fill+in+all+fields.");
    exit;
}

// If DB failed to connect entirely
if ($pdo === null) {
    header("Location: login_page.php?error=1&msg=Database+not+connected.+Contact+system+administrator.");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        log_activity($pdo, 'LOGIN', 'Logged in successfully from web browser', $user['id'], $user['name']);

        header("Location: dashboard.php");
        exit;
    } else {
        // Log failed login attempt
        log_activity($pdo, 'FAILED_LOGIN', "Failed login attempt for email: {$email}", null, 'Unknown');
        header("Location: login_page.php?error=1&msg=Invalid+credentials.+Please+try+again.");
        exit;
    }
} catch (PDOException $e) {
    header("Location: login_page.php?error=1&msg=A+database+error+occurred.");
    exit;
}
?>
