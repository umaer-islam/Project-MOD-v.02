<?php
/**
 * Activity Logger — Mamun's Ortho Dental
 * Logs every admin/staff activity to the database for the hidden monitor page.
 * Developer: Umaer Islam (https://umaerislam.com)
 */

/**
 * Ensure the activity_logs table exists. Call once per request if needed.
 */
function ensure_activity_logs_table($pdo) {
    static $tableExists = false;
    if ($tableExists) return;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            user_name VARCHAR(255) NOT NULL,
            user_role VARCHAR(50) DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(500) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        $tableExists = true;
    } catch (PDOException $e) {
        // Silently fail — don't break the app if logging fails
    }
}

/**
 * Log an activity to the database.
 *
 * @param PDO    $pdo      Database connection
 * @param string $action   Action type (LOGIN, LOGOUT, ADD_PATIENT, etc.)
 * @param string $details  Human-readable description of what happened
 * @param int    $user_id  User ID (optional, falls back to session)
 * @param string $user_name User name (optional, falls back to session)
 */
function log_activity($pdo, $action, $details, $user_id = null, $user_name = null) {
    if ($pdo === null) return;

    ensure_activity_logs_table($pdo);

    // Fallback to session if not provided
    if ($user_id === null) $user_id = $_SESSION['user_id'] ?? null;
    if ($user_name === null) $user_name = $_SESSION['user_name'] ?? 'System';
    $user_role = $_SESSION['user_role'] ?? null;

    // Get client IP
    $ip = get_client_ip();

    // Get user agent
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 500);

    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, user_name, user_role, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $user_name, $user_role, $action, $details, $ip, $ua]);
    } catch (PDOException $e) {
        // Silently fail — logging should never break the app
    }
}

/**
 * Get the real client IP address.
 */
function get_client_ip() {
    $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '127.0.0.1';
}

/**
 * Format a MySQL timestamp to human-readable AM/PM format.
 * Example: "Sunday, 10 August 2026, 02:30:15 AM"
 */
function format_activity_time($timestamp) {
    $dt = new DateTime($timestamp);
    return $dt->format('l, d M Y, h:i:s A');
}

/**
 * Get a human-readable action label.
 */
function get_action_label($action) {
    $labels = [
        'LOGIN'                => 'Logged In',
        'LOGOUT'               => 'Logged Out',
        'FAILED_LOGIN'         => 'Failed Login Attempt',
        'ADD_PATIENT'          => 'Added a New Patient',
        'UPDATE_PATIENT'       => 'Updated Patient Record',
        'DELETE_PATIENT'       => 'Deleted a Patient',
        'BOOK_APPOINTMENT'     => 'Booked an Appointment',
        'ADD_PRESCRIPTION'     => 'Created a Prescription',
        'DELETE_PRESCRIPTION'  => 'Deleted a Prescription',
        'ADD_PAYMENT'          => 'Recorded a Payment',
        'DELETE_PAYMENT'       => 'Deleted a Payment Record',
        'CREATE_CASH_MEMO'     => 'Created a Cash Memo',
        'UPDATE_CASH_MEMO'     => 'Updated a Cash Memo',
        'DELETE_CASH_MEMO'     => 'Deleted a Cash Memo',
        'ADD_ANNOUNCEMENT'     => 'Published an Announcement',
        'UPDATE_ANNOUNCEMENT'  => 'Updated an Announcement',
        'DELETE_ANNOUNCEMENT'  => 'Deleted an Announcement',
        'CREATE_USER'          => 'Created a Staff Account',
        'UPDATE_USER'          => 'Updated a Staff Account',
        'DELETE_USER'          => 'Deleted a Staff Account',
        'ADD_REVIEW'           => 'Added a Patient Review',
        'UPDATE_REVIEW'        => 'Updated a Patient Review',
        'DELETE_REVIEW'        => 'Deleted a Patient Review',
        'APPROVE_REVIEW'       => 'Approved a Patient Review',
        'UPLOAD_GALLERY_IMAGE' => 'Uploaded a Gallery Image',
        'UPDATE_GALLERY_IMAGE' => 'Updated Gallery Image Details',
        'DELETE_GALLERY_IMAGE' => 'Deleted a Gallery Image',
        'ADD_CASE_STUDY'       => 'Added a Before & After Case',
        'UPDATE_CASE_STUDY'    => 'Updated a Case Study',
        'DELETE_CASE_STUDY'    => 'Deleted a Case Study',
        'DELETE_MESSAGE'       => 'Deleted a Contact Inquiry',
        'UPDATE_PROFILE'       => 'Updated Own Profile',
        'UPDATE_RX_SETTINGS'   => 'Updated Prescription Settings',
        'VISITOR_CONTACT'      => 'Public Contact Form Submission',
        'GUEST_REVIEW'         => 'Guest Submitted a Review',
    ];
    return $labels[$action] ?? str_replace('_', ' ', ucfirst($action));
}

/**
 * Get the Font Awesome icon class for an action.
 */
function get_action_icon($action) {
    $icons = [
        'LOGIN'                => 'fas fa-sign-in-alt text-green-500',
        'LOGOUT'               => 'fas fa-sign-out-alt text-gray-400',
        'FAILED_LOGIN'         => 'fas fa-exclamation-triangle text-red-500',
        'ADD_PATIENT'          => 'fas fa-user-plus text-blue-500',
        'UPDATE_PATIENT'       => 'fas fa-user-edit text-blue-400',
        'DELETE_PATIENT'       => 'fas fa-user-minus text-red-500',
        'BOOK_APPOINTMENT'     => 'fas fa-calendar-plus text-purple-500',
        'ADD_PRESCRIPTION'     => 'fas fa-prescription text-indigo-500',
        'DELETE_PRESCRIPTION'  => 'fas fa-trash text-red-500',
        'ADD_PAYMENT'          => 'fas fa-money-bill-wave text-green-500',
        'DELETE_PAYMENT'       => 'fas fa-trash text-red-500',
        'CREATE_CASH_MEMO'     => 'fas fa-file-invoice-dollar text-yellow-500',
        'UPDATE_CASH_MEMO'     => 'fas fa-file-invoice text-yellow-400',
        'DELETE_CASH_MEMO'     => 'fas fa-trash text-red-500',
        'ADD_ANNOUNCEMENT'     => 'fas fa-bullhorn text-orange-500',
        'UPDATE_ANNOUNCEMENT'  => 'fas fa-bullhorn text-orange-400',
        'DELETE_ANNOUNCEMENT'  => 'fas fa-trash text-red-500',
        'CREATE_USER'          => 'fas fa-user-plus text-emerald-500',
        'UPDATE_USER'          => 'fas fa-user-cog text-emerald-400',
        'DELETE_USER'          => 'fas fa-user-slash text-red-500',
        'ADD_REVIEW'           => 'fas fa-star text-amber-400',
        'UPDATE_REVIEW'        => 'fas fa-star text-amber-400',
        'DELETE_REVIEW'        => 'fas fa-trash text-red-500',
        'APPROVE_REVIEW'       => 'fas fa-check-circle text-green-500',
        'UPLOAD_GALLERY_IMAGE' => 'fas fa-image text-pink-500',
        'UPDATE_GALLERY_IMAGE' => 'fas fa-image text-pink-400',
        'DELETE_GALLERY_IMAGE' => 'fas fa-trash text-red-500',
        'ADD_CASE_STUDY'       => 'fas fa-images text-violet-500',
        'UPDATE_CASE_STUDY'    => 'fas fa-images text-violet-400',
        'DELETE_CASE_STUDY'    => 'fas fa-trash text-red-500',
        'DELETE_MESSAGE'       => 'fas fa-trash text-red-500',
        'UPDATE_PROFILE'       => 'fas fa-user-cog text-gray-500',
        'UPDATE_RX_SETTINGS'   => 'fas fa-cog text-gray-500',
        'VISITOR_CONTACT'      => 'fas fa-envelope text-blue-300',
        'GUEST_REVIEW'         => 'fas fa-comment-dots text-pink-400',
    ];
    return $icons[$action] ?? 'fas fa-circle text-gray-400';
}
