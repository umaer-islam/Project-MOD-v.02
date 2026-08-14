<?php
session_start();
require_once 'database/connection.php';
require_once 'components/activity_logger.php';
require_once 'components/rate_limiter.php';

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

if ($pdo === null) {
    header("Location: login_page.php?error=1&msg=Database+not+connected.+Contact+system+administrator.");
    exit;
}

$rateLimiter = new RateLimiter($pdo);

// Check rate limit: 5 attempts per 15 minutes, lock for 15 minutes
$rateCheck = $rateLimiter->check('login', 5, 900, 900);
if (!$rateCheck['allowed']) {
    $minutes = ceil($rateCheck['retry_after'] / 60);
    log_activity($pdo, 'RATE_LIMITED_LOGIN', "Login rate limited for email: {$email}", null, 'Unknown');
    header("Location: login_page.php?error=1&msg=Too+many+failed+attempts.+Please+try+again+in+{$minutes}+minutes.");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Success — reset rate limiter
        $rateLimiter->reset('login');
        
        session_regenerate_id(true);
        
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        log_activity($pdo, 'LOGIN', 'Logged in successfully from web browser', $user['id'], $user['name']);

        header("Location: dashboard.php");
        exit;
    } else {
        // Failed — record attempt
        $rateLimiter->record('login');
        $remaining = $rateCheck['remaining'];
        
        log_activity($pdo, 'FAILED_LOGIN', "Failed login attempt for email: {$email} ({$remaining} attempts remaining)", null, 'Unknown');
        
        if ($remaining <= 0) {
            header("Location: login_page.php?error=1&msg=Account+locked+due+to+too+many+failed+attempts.+Try+again+in+15+minutes.");
        } else {
            header("Location: login_page.php?error=1&msg=Invalid+credentials.+Please+try+again.+({$remaining}+attempts+remaining)");
        }
        exit;
    }
} catch (PDOException $e) {
    header("Location: login_page.php?error=1&msg=A+database+error+occurred.");
    exit;
}
?>
