<?php
session_start();
require_once 'database/connection.php';
require_once 'components/activity_logger.php';

log_activity($pdo, 'LOGOUT', 'Logged out from the system');

session_unset();
session_destroy();
header("Location: login_page.php");
exit;
?>
