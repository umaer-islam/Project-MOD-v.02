<?php
require_once '../database/connection.php';
require_once '../components/auth_guard.php';
require_once '../components/activity_logger.php';

// Only admins can manage users
restrict_access(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') exit;

$action = $_POST['action'] ?? 'add';
$id = $_POST['id'] ?? null;

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role = strtolower(trim($_POST['role'] ?? 'receptionist'));

try {
    if ($action === 'add') {
        if(empty($name) || empty($email) || empty($password)) throw new Exception("Required fields missing");
        
        // Check if email exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if($check->fetch()) throw new Exception("Email already registered");

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $role]);
        log_activity($pdo, 'CREATE_USER', "Created staff account: {$name} (Email: {$email}, Role: {$role})");
        header("Location: ../users.php?success=" . urlencode("User account created successfully."));
        
    } elseif ($action === 'update' && $id) {
        if(empty($name) || empty($email)) throw new Exception("Name and email required");

        // Check if email exists for other users
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);
        if($check->fetch()) throw new Exception("Email already registered to another user");

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, password_hash=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $hash, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $role, $id]);
        }
        log_activity($pdo, 'UPDATE_USER', "Updated staff account: {$name} (Role: {$role})");
        header("Location: ../users.php?success=" . urlencode("User updated successfully."));

    } elseif ($action === 'delete' && $id) {
        // Prevent deleting self
        if($id == $_SESSION['user_id']) throw new Exception("You cannot delete your own account");
        
        // Get user name before deleting
        $delStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $delStmt->execute([$id]);
        $delName = $delStmt->fetchColumn() ?? 'Unknown';

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        log_activity($pdo, 'DELETE_USER', "Deleted staff account: {$delName}");
        header("Location: ../users.php?success=" . urlencode("User deleted successfully."));
    }
} catch (Exception $e) {
    header("Location: ../users.php?error=" . urlencode($e->getMessage()));
}
exit;
