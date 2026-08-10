<?php
$host = 'localhost';
$dbname = 'mamunort_clinic_db';
$username = 'mamunort_manager';
$password = '01712718527s';

$pdo = null;
$db_error = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = "Connection failed. Please contact the administrator.";
    // Don't die — let the page render and show a notice
}
?>

