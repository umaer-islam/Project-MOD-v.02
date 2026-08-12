<?php
/**
 * Database Migration & Setup Script
 * Run once after deploying to a new server: https://yoursite.com/api/setup_database.php
 * Safe to re-run — all operations are idempotent.
 */

session_start();
require_once '../components/auth_guard.php';
restrict_access(['admin']);

require_once '../database/connection.php';

if (!$pdo) {
    die("Cannot connect to database.");
}

$results = [];

function run($pdo, $label, $sql) {
    global $results;
    try {
        $pdo->exec($sql);
        $results[] = "✅ {$label}";
    } catch (PDOException $e) {
        // Column already exists or table exists — skip silently
        if (str_contains($e->getMessage(), 'Duplicate column') || str_contains($e->getMessage(), 'already exists')) {
            $results[] = "⏭️ {$label} (already done)";
        } else {
            $results[] = "❌ {$label}: " . $e->getMessage();
        }
    }
}

// ── users ──
run($pdo, 'Create users table', "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','doctor','receptionist') DEFAULT 'receptionist',
    degrees VARCHAR(500) DEFAULT NULL,
    rx_template_path VARCHAR(255) DEFAULT NULL,
    signature_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── patients ──
run($pdo, 'Create patients table', "CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    age INT DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    blood_group VARCHAR(10) DEFAULT NULL,
    weight DECIMAL(5,2) DEFAULT NULL,
    access_token VARCHAR(64) DEFAULT NULL,
    qr_code_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'Patients: add access_token', "ALTER TABLE patients ADD COLUMN access_token VARCHAR(64) DEFAULT NULL");
run($pdo, 'Patients: add qr_code_path', "ALTER TABLE patients ADD COLUMN qr_code_path VARCHAR(255) DEFAULT NULL");
run($pdo, 'Patients: add blood_group', "ALTER TABLE patients ADD COLUMN blood_group VARCHAR(10) DEFAULT NULL");
run($pdo, 'Patients: add weight', "ALTER TABLE patients ADD COLUMN weight DECIMAL(5,2) DEFAULT NULL");

// ── appointments ──
run($pdo, 'Create appointments table', "CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT DEFAULT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('Scheduled','Completed','Cancelled','No-Show') DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── prescriptions ──
run($pdo, 'Create prescriptions table', "CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT DEFAULT NULL,
    diagnosis TEXT DEFAULT NULL,
    investigations TEXT DEFAULT NULL,
    medicines JSON DEFAULT NULL,
    advice TEXT DEFAULT NULL,
    follow_up VARCHAR(100) DEFAULT NULL,
    rx_date DATE DEFAULT NULL,
    qr_code_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'Prescriptions: add investigations', "ALTER TABLE prescriptions ADD COLUMN investigations TEXT DEFAULT NULL AFTER diagnosis");
run($pdo, 'Prescriptions: add follow_up', "ALTER TABLE prescriptions ADD COLUMN follow_up VARCHAR(100) DEFAULT NULL");
run($pdo, 'Prescriptions: add rx_date', "ALTER TABLE prescriptions ADD COLUMN rx_date DATE DEFAULT NULL");

// ── payments ──
run($pdo, 'Create payments table', "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Cash',
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── cash_memos ──
run($pdo, 'Create cash_memos table', "CREATE TABLE IF NOT EXISTS cash_memos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    memo_number VARCHAR(50) UNIQUE NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    customer_phone VARCHAR(30) DEFAULT NULL,
    subtotal DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    grand_total DECIMAL(10,2) DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'Create cash_memo_items table', "CREATE TABLE IF NOT EXISTS cash_memo_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    memo_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) DEFAULT 1,
    unit_price DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (memo_id) REFERENCES cash_memos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── announcements ──
run($pdo, 'Create announcements table', "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    date_posted DATE DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    visibility ENUM('Public','Staff') DEFAULT 'Public',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── services ──
run($pdo, 'Create services table', "CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'fa-tooth',
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'Services: add icon', "ALTER TABLE services ADD COLUMN icon VARCHAR(50) DEFAULT 'fa-tooth' AFTER name");

// ── testimonials ──
run($pdo, 'Create testimonials table', "CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(255) NOT NULL,
    location VARCHAR(255) DEFAULT NULL,
    stars INT DEFAULT 5,
    review TEXT DEFAULT NULL,
    status ENUM('Published','Hidden','Pending') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'Testimonials: fix status enum', "ALTER TABLE testimonials MODIFY COLUMN status ENUM('Published','Hidden','Pending') DEFAULT 'Pending'");

// ── gallery ──
run($pdo, 'Create gallery table', "CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── before_after_cases ──
run($pdo, 'Create before_after_cases table', "CREATE TABLE IF NOT EXISTS before_after_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    before_image VARCHAR(255) NOT NULL,
    after_image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── contact_inquiries ──
run($pdo, 'Create contact_inquiries table', "CREATE TABLE IF NOT EXISTS contact_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    service VARCHAR(255) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('unread','read') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

run($pdo, 'Contact: add status column', "ALTER TABLE contact_inquiries ADD COLUMN status ENUM('unread','read') DEFAULT 'unread' AFTER message");

// ── activity_logs ──
run($pdo, 'Create activity_logs table', "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(255) DEFAULT 'System',
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Ensure default admin user exists ──
try {
    $adminCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if ($adminCheck == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (name, email, password_hash, role) VALUES ('Admin', 'admin@mamunorthodental.com', '$hash', 'admin')");
        $results[] = "✅ Default admin user created (admin@mamunorthodental.com / admin123)";
    } else {
        $results[] = "⏭️ Admin user already exists";
    }
} catch (PDOException $e) {
    $results[] = "❌ Admin check: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup — Mamun's Ortho Dental</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; background: #f4f7fc; color: #004591; }
        h1 { color: #ea741b; }
        .result { padding: 8px 12px; margin: 4px 0; background: #fff; border-radius: 8px; border-left: 3px solid #004591; font-size: 14px; }
        .result.ok { border-left-color: #22c55e; }
        .result.skip { border-left-color: #f59e0b; }
        .result.err { border-left-color: #ef4444; }
    </style>
</head>
<body>
    <h1>Database Setup Results</h1>
    <?php foreach ($results as $r): ?>
        <div class="result <?= str_starts_with($r, '✅') ? 'ok' : (str_starts_with($r, '⏭️') ? 'skip' : 'err') ?>">
            <?= htmlspecialchars($r) ?>
        </div>
    <?php endforeach; ?>
    <br>
    <a href="../dashboard.php" style="color:#ea741b;font-weight:600;">← Go to Dashboard</a>
</body>
</html>
