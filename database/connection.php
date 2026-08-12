<?php
$host = 'localhost';
$dbname = 'mamunort_clinic_db';
$username = 'mamunort_manager';
$password = '01712718527s';

$pdo = null;
$db_error = null;

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_PERSISTENT        => true,
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    ]);
} catch (PDOException $e) {
    // Log full error to server error_log (visible in cPanel > Error Logs)
    error_log('[DB CONNECTION FAILED] ' . $e->getMessage());
    $db_error = "Database connection failed. Please contact the administrator.";
}
?>

