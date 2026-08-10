<?php
// Session Auth Guard — must be included before any protected operations
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

if (!isset($_SESSION['user_id'])) {
    // Check if it's an AJAX request to return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized Access. Please log in.']);
        exit;
    }
    
    // Determine redirect path relative to current script depth
    $isApi = strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false;
    $redirectUrl = $isApi ? '../login_page.php' : 'login_page.php';
    
    header("Location: $redirectUrl");
    exit;
}

// Ensure the local session always reflects the real database role
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../database/connection.php';
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $real_role = $stmt->fetchColumn();
            if ($real_role) {
                $_SESSION['user_role'] = $real_role;
            }
        } catch (Exception $e) {
            // Ignore if DB fails
        }
    }
}

/**
 * Validates if the current user has one of the allowed roles.
 * @param array $allowed_roles Array of string roles (e.g., ['admin'], ['admin', 'doctor'])
 */
function restrict_access($allowed_roles) {
    if (!isset($_SESSION['user_role'])) {
        header("Location: login_page.php");
        exit;
    }

    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        // API vs Page redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => 'Permission Denied. Your role does not have access.']);
            exit;
        }

        $isApi = strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false;
        $redirectUrl = $isApi ? '../dashboard.php?error=Access+Denied.+You+do+not+have+permission+to+view+this+feature.' : 'dashboard.php?error=Access+Denied.+You+do+not+have+permission+to+view+this+page.';
        
        header("Location: $redirectUrl");
        exit;
    }
}
?>
