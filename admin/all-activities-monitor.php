<?php
/**
 * ═══════════════════════════════════════════════════════════════════
 *  HIDDEN ACTIVITY MONITOR — Mamun's Ortho Dental
 *  URL: /admin/all-activities-monitor.php
 *  Developer: Umaer Islam (https://umaerislam.com)
 * ═══════════════════════════════════════════════════════════════════
 */

session_start();
require_once __DIR__ . '/../components/auth_guard.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../components/activity_logger.php';

restrict_access(['admin']);

$SECRET_PIN = 'MOD-2026';
$PIN_SESSION_KEY = 'monitor_pin_verified';

$pin_error = '';
if (isset($_POST['verify_pin'])) {
    $entered_pin = trim($_POST['pin'] ?? '');
    if ($entered_pin === $SECRET_PIN) {
        $_SESSION[$PIN_SESSION_KEY] = true;
        $_SESSION[$PIN_SESSION_KEY . '_time'] = time();
        log_activity($pdo, 'MONITOR_ACCESS', 'Accessed the hidden activity monitor panel');
    } else {
        $pin_error = 'Invalid PIN. Access denied.';
        log_activity($pdo, 'MONITOR_FAILED_ACCESS', 'Failed attempt to access activity monitor with wrong PIN');
    }
}

if (isset($_GET['monitor_logout'])) {
    unset($_SESSION[$PIN_SESSION_KEY]);
    unset($_SESSION[$PIN_SESSION_KEY . '_time']);
    header('Location: all-activities-monitor.php');
    exit;
}

$pin_verified = false;
if (isset($_SESSION[$PIN_SESSION_KEY]) && $_SESSION[$PIN_SESSION_KEY] === true) {
    $elapsed = time() - ($_SESSION[$PIN_SESSION_KEY . '_time'] ?? 0);
    if ($elapsed < 14400) {
        $pin_verified = true;
    } else {
        unset($_SESSION[$PIN_SESSION_KEY]);
        unset($_SESSION[$PIN_SESSION_KEY . '_time']);
    }
}

// ─── PIN ENTRY SCREEN ───
if (!$pin_verified) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Required — Mamun's Ortho Dental</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f0f2f5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:20px;box-shadow:0 4px 24px rgba(0,0,0,.06);padding:48px 40px;width:100%;max-width:400px;text-align:center}
        .icon-wrap{width:72px;height:72px;margin:0 auto 24px;border-radius:18px;background:#fef3e2;display:flex;align-items:center;justify-content:center}
        .icon-wrap i{font-size:28px;color:#ea741b}
        h1{font-size:20px;font-weight:700;color:#1a1a2e;margin-bottom:6px}
        .sub{color:#8b8fa3;font-size:13px;margin-bottom:28px;line-height:1.6}
        .pin-input{width:100%;padding:14px 18px;background:#f8f9fb;border:1.5px solid #e5e7eb;border-radius:12px;color:#1a1a2e;font-size:20px;font-family:'Inter',sans-serif;letter-spacing:10px;text-align:center;outline:none;transition:all .2s}
        .pin-input:focus{border-color:#ea741b;box-shadow:0 0 0 3px rgba(234,116,27,.12)}
        .pin-input::placeholder{color:#c5c9d6;font-size:13px;letter-spacing:1px}
        .btn{width:100%;padding:14px;background:#ea741b;color:#fff;border:none;border-radius:12px;font-size:13px;font-weight:600;cursor:pointer;margin-top:16px;transition:all .2s;font-family:'Inter',sans-serif}
        .btn:hover{background:#d4620a;transform:translateY(-1px);box-shadow:0 4px 16px rgba(234,116,27,.25)}
        .error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:10px;font-size:13px;margin-top:14px}
        .warning{color:#c5c9d6;font-size:11px;margin-top:20px}
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap"><i class="fas fa-shield-halved"></i></div>
        <h1>Restricted Access</h1>
        <p class="sub">Enter your secret PIN to access the Activity Monitor.</p>
        <?php if ($pin_error): ?>
        <div class="error"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($pin_error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="pin" class="pin-input" placeholder="Enter PIN" maxlength="20" autofocus required>
            <button type="submit" name="verify_pin" class="btn"><i class="fas fa-arrow-right-to-bracket"></i>&nbsp; Verify & Enter</button>
        </form>
        <p class="warning"><i class="fas fa-lock"></i>&nbsp; All access attempts are recorded.</p>
    </div>
</body>
</html>
<?php exit; }


// ═══════════════════════════════════════════════════════════════════
//  PIN VERIFIED — ACTIVITY MONITOR (WHITE THEME)
// ═══════════════════════════════════════════════════════════════════

$filter_user = $_GET['user'] ?? '';
$filter_action = $_GET['action'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';
$filter_search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

$where = [];
$params = [];
if ($filter_user !== '') { $where[] = "user_name = ?"; $params[] = $filter_user; }
if ($filter_action !== '') { $where[] = "action = ?"; $params[] = $filter_action; }
if ($filter_date_from !== '') { $where[] = "DATE(created_at) >= ?"; $params[] = $filter_date_from; }
if ($filter_date_to !== '') { $where[] = "DATE(created_at) <= ?"; $params[] = $filter_date_to; }
if ($filter_search !== '') {
    $where[] = "(details LIKE ? OR user_name LIKE ? OR action LIKE ?)";
    $s = "%{$filter_search}%";
    $params[] = $s; $params[] = $s; $params[] = $s;
}
$whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

try {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs {$whereClause}");
    $countStmt->execute($params);
    $total_records = $countStmt->fetchColumn();
    $total_pages = max(1, ceil($total_records / $per_page));

    $sql = "SELECT * FROM activity_logs {$whereClause} ORDER BY created_at DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll();

    $unique_users = $pdo->query("SELECT DISTINCT user_name FROM activity_logs ORDER BY user_name")->fetchAll(PDO::FETCH_COLUMN);
    $unique_actions = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);

    $stats = $pdo->query("SELECT COUNT(*) as total, COUNT(DISTINCT user_id) as unique_users, SUM(action='LOGIN') as logins, SUM(action='FAILED_LOGIN') as failed_logins FROM activity_logs")->fetch();
} catch (PDOException $e) {
    $activities = []; $total_records = 0; $total_pages = 1;
    $unique_users = []; $unique_actions = [];
    $stats = ['total'=>0,'unique_users'=>0,'logins'=>0,'failed_logins'=>0];
}

date_default_timezone_set('Asia/Dhaka');

// Color maps
$action_colors = [
    'LOGIN'=>['#10b981','#ecfdf5'],'LOGOUT'=>['#6b7280','#f9fafb'],'FAILED_LOGIN'=>['#ef4444','#fef2f2'],
    'ADD_PATIENT'=>['#3b82f6','#eff6ff'],'UPDATE_PATIENT'=>['#6366f1','#eef2ff'],'DELETE_PATIENT'=>['#ef4444','#fef2f2'],
    'BOOK_APPOINTMENT'=>['#8b5cf6','#f5f3ff'],
    'ADD_PRESCRIPTION'=>['#6366f1','#eef2ff'],'DELETE_PRESCRIPTION'=>['#ef4444','#fef2f2'],
    'ADD_PAYMENT'=>['#10b981','#ecfdf5'],'DELETE_PAYMENT'=>['#ef4444','#fef2f2'],
    'CREATE_CASH_MEMO'=>['#f59e0b','#fffbeb'],'UPDATE_CASH_MEMO'=>['#f59e0b','#fffbeb'],    'DELETE_CASH_MEMO'=>['#ef4444','#fef2f2'],
    'ADD_ANNOUNCEMENT'=>['#f97316','#fff7ed'],'UPDATE_ANNOUNCEMENT'=>['#f97316','#fff7ed'],'DELETE_ANNOUNCEMENT'=>['#ef4444','#fef2f2'],
    'CREATE_USER'=>['#10b981','#ecfdf5'],'UPDATE_USER'=>['#10b981','#ecfdf5'],    'DELETE_USER'=>['#ef4444','#fef2f2'],
    'ADD_REVIEW'=>['#f59e0b','#fffbeb'],'UPDATE_REVIEW'=>['#f59e0b','#fffbeb'],'DELETE_REVIEW'=>['#ef4444','#fef2f2'],'APPROVE_REVIEW'=>['#10b981','#ecfdf5'],
    'UPLOAD_GALLERY_IMAGE'=>['#ec4899','#fdf2f8'],'UPDATE_GALLERY_IMAGE'=>['#ec4899','#fdf2f8'],'DELETE_GALLERY_IMAGE'=>['#ef4444','#fef2f2'],
    'ADD_CASE_STUDY'=>['#8b5cf6','#f5f3ff'],'UPDATE_CASE_STUDY'=>['#8b5cf6','#f5f3ff'],'DELETE_CASE_STUDY'=>['#ef4444','#fef2f2'],
    'DELETE_MESSAGE'=>['#ef4444','#fef2f2'],'MARK_MESSAGES_READ'=>['#6b7280','#f9fafb'],
    'UPDATE_PROFILE'=>['#6b7280','#f9faffb'],'UPDATE_RX_SETTINGS'=>['#6b7280','#f9fafb'],
    'VISITOR_CONTACT'=>['#3b82f6','#eff6ff'],'GUEST_REVIEW'=>['#f59e0b','#fffbeb'],
    'MONITOR_ACCESS'=>['#ea741b','#fff7ed'],'MONITOR_FAILED_ACCESS'=>['#ef4444','#fef2f2'],
];
$default_color = ['#6b7280','#f9fafb'];

$user_colors = ['#ea741b','#3b82f6','#10b981','#8b5cf6','#ec4899','#f59e0b','#06b6d4','#ef4444'];
$user_color_map = [];
$ci = 0;
foreach ($unique_users as $u) {
    $user_color_map[$u] = $user_colors[$ci % count($user_colors)];
    $ci++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Monitor — Mamun's Ortho Dental</title>
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noodp">
    <meta name="googlebot" content="noindex, nofollow">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#f0f2f5;color:#1a1a2e;min-height:100vh}

        /* ── Layout ── */
        .wrapper{max-width:1320px;margin:0 auto;padding:24px}

        /* ── Top Bar ── */
        .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
        .topbar-left{display:flex;align-items:center;gap:14px}
        .topbar-icon{width:44px;height:44px;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.06)}
        .topbar-icon i{font-size:18px;color:#ea741b}
        .topbar h1{font-size:20px;font-weight:700;color:#1a1a2e}
        .topbar p{font-size:12px;color:#8b8fa3;margin-top:1px}
        .topbar-right{display:flex;gap:8px;flex-wrap:wrap}
        .btn{padding:9px 16px;border-radius:10px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:7px;text-decoration:none;font-family:'Inter',sans-serif}
        .btn-light{background:#fff;color:#6b7280;border:1px solid #e5e7eb}
        .btn-light:hover{background:#f9fafb;color:#1a1a2e;border-color:#d1d5db}
        .btn-orange{background:#ea741b;color:#fff}
        .btn-orange:hover{background:#d4620a;box-shadow:0 2px 8px rgba(234,116,27,.25)}
        .btn-red{background:#fff;color:#ef4444;border:1px solid #fecaca}
        .btn-red:hover{background:#fef2f2}

        /* ── Stats Grid ── */
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
        .stat{background:#fff;border-radius:16px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.04);border:1px solid #f0f0f0}
        .stat-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
        .stat-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center}
        .stat-icon i{font-size:16px}
        .stat-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:#8b8fa3}
        .stat-value{font-size:28px;font-weight:800;color:#1a1a2e}

        /* ── Filter Bar ── */
        .filter-bar{background:#fff;border-radius:16px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04);border:1px solid #f0f0f0}
        .filter-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
        .fg{display:flex;flex-direction:column;gap:4px}
        .fg label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#8b8fa3}
        .fg input,.fg select{background:#f8f9fb;border:1px solid #e5e7eb;border-radius:8px;padding:9px 12px;color:#1a1a2e;font-size:13px;font-family:'Inter',sans-serif;outline:none;transition:all .2s}
        .fg input:focus,.fg select:focus{border-color:#ea741b;box-shadow:0 0 0 2px rgba(234,116,27,.1)}
        .fg select{min-width:150px}
        .fg select option{background:#fff;color:#1a1a2e}

        /* ── Custom Dropdown ── */
        .mod-dropdown{position:relative;width:100%}
        .mod-dropdown-trigger{display:flex;align-items:center;justify-content:space-between;gap:6px;width:100%;background:#f8f9fb;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;cursor:pointer;transition:all .2s;font-size:13px;color:#1a1a2e;font-family:'Inter',sans-serif;min-height:36px}
        .mod-dropdown-trigger:hover{border-color:#d1d5db}
        .mod-dropdown.is-open .mod-dropdown-trigger{border-color:#ea741b;box-shadow:0 0 0 2px rgba(234,116,27,.1)}
        .mod-dropdown-selected{flex:1;min-width:0;font-size:13px;color:#1a1a2e;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .mod-dropdown-chevron{flex-shrink:0;width:10px;color:#8b8fa3;transition:transform .25s ease}
        .mod-dropdown.is-open .mod-dropdown-chevron{transform:rotate(180deg)}
        .mod-dropdown-panel{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:4px;z-index:50;max-height:0;overflow:hidden;opacity:0;transform:translateY(-4px);transition:all .25s ease;pointer-events:none}
        .mod-dropdown.is-open .mod-dropdown-panel{max-height:200px;opacity:1;transform:translateY(0);box-shadow:0 8px 24px rgba(0,0,0,.1);pointer-events:auto;overflow-y:auto}
        .mod-dropdown-option{display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:6px;font-size:12px;color:#555;cursor:pointer;transition:all .15s}
        .mod-dropdown-option:hover{background:#f3f4f6;color:#1a1a2e}
        .mod-dropdown-option.is-selected{background:rgba(234,116,27,.08);color:#ea741b;font-weight:600}

        /* ── Custom Calendar ── */
        .mod-calendar{position:relative;width:100%}
        .mod-calendar-trigger{display:flex;flex-direction:column;justify-content:flex-end;width:100%;background:#f8f9fb;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;cursor:pointer;transition:all .2s;font-size:13px;color:#1a1a2e;font-family:'Inter',sans-serif;min-height:36px;position:relative}
        .mod-calendar-trigger:hover{border-color:#d1d5db}
        .mod-calendar.is-open .mod-calendar-trigger{border-color:#ea741b;box-shadow:0 0 0 2px rgba(234,116,27,.1)}
        .mod-calendar.has-value .mod-calendar-trigger{color:#1a1a2e}
        .mod-calendar-label{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:#8b8fa3;pointer-events:none;transition:all .2s;white-space:nowrap}
        .mod-calendar.has-value .mod-calendar-label,.mod-calendar.is-open .mod-calendar-label{top:5px;transform:none;font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:#ea741b}
        .mod-calendar-value{display:flex;align-items:center;gap:6px;opacity:0;transform:translateY(4px);transition:all .2s}
        .mod-calendar.has-value .mod-calendar-value,.mod-calendar.is-open .mod-calendar-value{opacity:1;transform:translateY(0)}
        .mod-calendar-icon{color:#8b8fa3;flex-shrink:0;transition:color .2s;font-size:12px}
        .mod-calendar.is-open .mod-calendar-icon{color:#ea741b}
        .mod-calendar-text{flex:1;font-size:13px}
        .mod-calendar-panel{position:absolute;top:calc(100% + 4px);left:0;width:280px;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:10px;z-index:50;max-height:0;overflow:hidden;opacity:0;transform:translateY(-6px);transition:all .25s ease;pointer-events:none}
        .mod-calendar.is-open .mod-calendar-panel{max-height:360px;opacity:1;transform:translateY(0);box-shadow:0 8px 24px rgba(0,0,0,.1);pointer-events:auto}
        .cal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
        .cal-header-btn{width:28px;height:28px;border-radius:6px;border:none;background:transparent;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;font-size:11px}
        .cal-header-btn:hover{background:#f3f4f6;color:#1a1a2e}
        .cal-month-year{font-size:13px;font-weight:700;color:#1a1a2e;display:flex;gap:4px}
        .cal-today-btn{font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#ea741b;background:rgba(234,116,27,.08);border:none;border-radius:5px;padding:3px 8px;cursor:pointer;transition:all .15s}
        .cal-today-btn:hover{background:rgba(234,116,27,.16)}
        .cal-weekdays{display:grid;grid-template-columns:repeat(7,1fr);margin-bottom:2px}
        .cal-weekday{text-align:center;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8b8fa3;padding:2px 0}
        .cal-days{display:grid;grid-template-columns:repeat(7,1fr);gap:1px}
        .cal-day{width:100%;aspect-ratio:1;border-radius:6px;border:none;background:transparent;font-size:12px;font-weight:500;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .12s;font-family:'Inter',sans-serif}
        .cal-day:hover:not(.cal-day--disabled):not(.cal-day--empty){background:#f3f4f6}
        .cal-day--today{font-weight:700;color:#ea741b}
        .cal-day--today::after{content:'';position:absolute;bottom:2px;left:50%;transform:translateX(-50%);width:3px;height:3px;border-radius:50%;background:#ea741b}
        .cal-day--selected{background:#ea741b!important;color:#fff!important;font-weight:700;box-shadow:0 2px 6px rgba(234,116,27,.3)}
        .cal-day--selected::after{display:none}
        .cal-day--other-month{color:#cbd5e1}
        .cal-days--slide-left{animation:calSL .2s ease}
        .cal-days--slide-right{animation:calSR .2s ease}
        @keyframes calSL{from{opacity:0;transform:translateX(10px)}to{opacity:1;transform:translateX(0)}}
        @keyframes calSR{from{opacity:0;transform:translateX(-10px)}to{opacity:1;transform:translateX(0)}}
        .filter-btns{display:flex;gap:6px;align-items:flex-end}

        /* ── Table Card ── */
        .table-card{background:#fff;border-radius:16px;box-shadow:0 1px 4px rgba(0,0,0,.04);border:1px solid #f0f0f0;overflow:hidden}
        .table-top{padding:16px 20px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;justify-content:space-between}
        .table-top h3{font-size:14px;font-weight:700;color:#1a1a2e;display:flex;align-items:center;gap:8px}
        .table-top h3 i{color:#ea741b;font-size:14px}
        .table-top .count{font-size:12px;color:#8b8fa3;font-weight:500}

        table{width:100%;border-collapse:collapse}
        thead th{padding:12px 16px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#8b8fa3;background:#fafbfc;border-bottom:1px solid #f0f0f0}
        tbody tr{border-bottom:1px solid #f5f5f5;transition:background .15s}
        tbody tr:hover{background:#fafbfc}
        tbody td{padding:12px 16px;font-size:13px;vertical-align:middle}

        .action-pill{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap}
        .action-pill i{font-size:10px}

        .user-cell{display:flex;align-items:center;gap:10px}
        .avatar{width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0}
        .user-info .name{font-weight:600;color:#1a1a2e;font-size:13px}
        .user-info .role{font-size:10px;color:#8b8fa3;text-transform:uppercase;letter-spacing:.5px;font-weight:600}

        .time-col{white-space:nowrap}
        .time-col .date{color:#6b7280;font-size:12px;font-weight:500}
        .time-col .time{color:#ea741b;font-size:12px;font-weight:600}

        .details-col{color:#6b7280;font-size:13px;max-width:320px;line-height:1.5}

        .ip-col{font-size:11px;color:#b0b5c3;font-family:'SF Mono',Monaco,Menlo,monospace}

        .empty{text-align:center;padding:60px 20px}
        .empty i{font-size:40px;color:#e5e7eb;margin-bottom:12px}
        .empty h3{color:#8b8fa3;font-size:15px;margin-bottom:4px}
        .empty p{color:#c5c9d6;font-size:12px}

        /* ── Pagination ── */
        .pagination{display:flex;align-items:center;justify-content:center;gap:4px;padding:16px}
        .pg{padding:7px 12px;border-radius:8px;font-size:12px;font-weight:500;color:#6b7280;background:#fff;border:1px solid #e5e7eb;text-decoration:none;transition:all .15s}
        .pg:hover{background:#f9fafb;color:#1a1a2e;border-color:#d1d5db}
        .pg.active{background:#ea741b;color:#fff;border-color:#ea741b}

        /* ── Footer ── */
        .footer{text-align:center;padding:20px;color:#c5c9d6;font-size:11px}
        .footer a{color:#ea741b;text-decoration:none;font-weight:600}

        /* ── Mobile: Card View ── */
        .mobile-card{display:none;background:#fff;border-radius:14px;padding:16px;margin-bottom:10px;border:1px solid #f0f0f0;box-shadow:0 1px 4px rgba(0,0,0,.03)}
        .mobile-card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
        .mobile-card .details{color:#6b7280;font-size:12px;line-height:1.5;margin-top:8px;padding-top:8px;border-top:1px solid #f5f5f5}
        .mobile-card .meta{display:flex;gap:12px;flex-wrap:wrap;margin-top:8px}
        .mobile-card .meta span{font-size:11px;color:#8b8fa3;display:flex;align-items:center;gap:4px}

        /* ── Responsive ── */
        @media(max-width:1024px){.stats{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:768px){
            .wrapper{padding:16px}
            .stats{grid-template-columns:1fr 1fr;gap:10px}
            .stat{padding:14px}
            .stat-value{font-size:22px}
            .topbar{flex-direction:column;align-items:flex-start}
            .filter-row{flex-direction:column}
            .fg{width:100%}
            .fg input,.fg select{width:100%}
            .desktop-table{display:none}
            .mobile-card{display:block}
        }
        @media(max-width:480px){
            .stats{grid-template-columns:1fr}
            .topbar-right{width:100%}
            .topbar-right .btn{flex:1;justify-content:center}
        }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar{width:5px;height:5px}
        ::-webkit-scrollbar-track{background:transparent}
        ::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:3px}
        ::-webkit-scrollbar-thumb:hover{background:#b0b5c3}
    </style>
</head>
<body>

<div class="wrapper">
    <!-- ── Top Bar ── -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <h1>Activity Monitor</h1>
                <p>Complete audit trail of all system activities</p>
            </div>
        </div>
        <div class="topbar-right">
            <a href="?date_from=<?= date('Y-m-d') ?>" class="btn btn-light"><i class="fas fa-calendar-day"></i> Today</a>
            <a href="?date_from=<?= date('Y-m-d', strtotime('-7 days')) ?>" class="btn btn-light"><i class="fas fa-calendar-week"></i> 7 Days</a>
            <a href="?monitor_logout=1" class="btn btn-red"><i class="fas fa-lock"></i> Lock</a>
        </div>
    </div>

    <!-- ── Stats ── -->
    <div class="stats">
        <div class="stat">
            <div class="stat-top">
                <span class="stat-label">Total Activities</span>
                <div class="stat-icon" style="background:#fff7ed"><i class="fas fa-layer-group" style="color:#ea741b"></i></div>
            </div>
            <div class="stat-value"><?= number_format($stats['total'] ?? 0) ?></div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <span class="stat-label">Active Users</span>
                <div class="stat-icon" style="background:#eff6ff"><i class="fas fa-users" style="color:#3b82f6"></i></div>
            </div>
            <div class="stat-value"><?= (int)($stats['unique_users'] ?? 0) ?></div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <span class="stat-label">Successful Logins</span>
                <div class="stat-icon" style="background:#ecfdf5"><i class="fas fa-right-to-bracket" style="color:#10b981"></i></div>
            </div>
            <div class="stat-value"><?= number_format($stats['logins'] ?? 0) ?></div>
        </div>
        <div class="stat">
            <div class="stat-top">
                <span class="stat-label">Failed Logins</span>
                <div class="stat-icon" style="background:#fef2f2"><i class="fas fa-shield-halved" style="color:#ef4444"></i></div>
            </div>
            <div class="stat-value"><?= number_format($stats['failed_logins'] ?? 0) ?></div>
        </div>
    </div>

    <!-- ── Filters ── -->
    <div class="filter-bar">
        <form method="GET" class="filter-row">
            <div class="fg">
                <label>Search</label>
                <input type="text" name="search" placeholder="Search details..." value="<?= htmlspecialchars($filter_search) ?>" style="min-width:180px">
            </div>
            <div class="fg">
                <label>User</label>
                <div class="mod-dropdown" data-name="user" data-placeholder="All Users">
                    <input type="hidden" name="user" value="<?= htmlspecialchars($filter_user) ?>">
                    <div class="mod-dropdown-trigger">
                        <span class="mod-dropdown-selected"><?= $filter_user ? htmlspecialchars($filter_user) : 'All Users' ?></span>
                        <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                    </div>
                    <div class="mod-dropdown-panel">
                        <div class="mod-dropdown-option <?= $filter_user===''?'is-selected':'' ?>" data-value=""><span>All Users</span></div>
                        <?php foreach ($unique_users as $u): ?>
                        <div class="mod-dropdown-option <?= $filter_user===$u?'is-selected':'' ?>" data-value="<?= htmlspecialchars($u) ?>"><span><?= htmlspecialchars($u) ?></span></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="fg">
                <label>Action</label>
                <div class="mod-dropdown" data-name="action" data-placeholder="All Actions">
                    <input type="hidden" name="action" value="<?= htmlspecialchars($filter_action) ?>">
                    <div class="mod-dropdown-trigger">
                        <span class="mod-dropdown-selected"><?= $filter_action ? get_action_label($filter_action) : 'All Actions' ?></span>
                        <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6l4 4 4-4"/></svg>
                    </div>
                    <div class="mod-dropdown-panel">
                        <div class="mod-dropdown-option <?= $filter_action===''?'is-selected':'' ?>" data-value=""><span>All Actions</span></div>
                        <?php foreach ($unique_actions as $a): ?>
                        <div class="mod-dropdown-option <?= $filter_action===$a?'is-selected':'' ?>" data-value="<?= htmlspecialchars($a) ?>"><span><?= get_action_label($a) ?></span></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="fg">
                <div class="mod-calendar" data-placeholder="From date">
                    <input type="hidden" name="date_from" value="<?= htmlspecialchars($filter_date_from) ?>">
                    <div class="mod-calendar-trigger" style="min-height:36px">
                        <span class="mod-calendar-label" style="font-size:13px">From</span>
                        <div class="mod-calendar-value">
                            <i class="fas fa-calendar-day mod-calendar-icon" style="font-size:11px"></i>
                            <span class="mod-calendar-text" style="font-size:13px"><?= $filter_date_from ? date('d M Y', strtotime($filter_date_from)) : 'From date' ?></span>
                        </div>
                    </div>
                    <div class="mod-calendar-panel">
                        <div class="cal-header">
                            <button type="button" class="cal-header-btn cal-prev"><i class="fas fa-chevron-left"></i></button>
                            <div class="cal-month-year"></div>
                            <button type="button" class="cal-today-btn">Today</button>
                            <button type="button" class="cal-header-btn cal-next"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="cal-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
                        <div class="cal-days"></div>
                    </div>
                </div>
            </div>
            <div class="fg">
                <div class="mod-calendar" data-placeholder="To date">
                    <input type="hidden" name="date_to" value="<?= htmlspecialchars($filter_date_to) ?>">
                    <div class="mod-calendar-trigger" style="min-height:36px">
                        <span class="mod-calendar-label" style="font-size:13px">To</span>
                        <div class="mod-calendar-value">
                            <i class="fas fa-calendar-day mod-calendar-icon" style="font-size:11px"></i>
                            <span class="mod-calendar-text" style="font-size:13px"><?= $filter_date_to ? date('d M Y', strtotime($filter_date_to)) : 'To date' ?></span>
                        </div>
                    </div>
                    <div class="mod-calendar-panel">
                        <div class="cal-header">
                            <button type="button" class="cal-header-btn cal-prev"><i class="fas fa-chevron-left"></i></button>
                            <div class="cal-month-year"></div>
                            <button type="button" class="cal-today-btn">Today</button>
                            <button type="button" class="cal-header-btn cal-next"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="cal-weekdays"><span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span></div>
                        <div class="cal-days"></div>
                    </div>
                </div>
            </div>
            <div class="filter-btns">
                <button type="submit" class="btn btn-orange"><i class="fas fa-search"></i> Filter</button>
                <a href="all-activities-monitor.php" class="btn btn-light"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>

    <!-- ── Desktop Table ── -->
    <div class="table-card desktop-table">
        <div class="table-top">
            <h3><i class="fas fa-stream"></i> Activity Stream</h3>
            <span class="count"><?= number_format($total_records) ?> records</span>
        </div>

        <?php if (empty($activities)): ?>
        <div class="empty"><i class="fas fa-inbox"></i><h3>No activities found</h3><p>No records match your filters.</p></div>
        <?php else: ?>
        <div style="overflow-x:auto">
        <table>
            <thead><tr>
                <th style="width:45px">#</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>Date & Time</th>
                <th>IP</th>
            </tr></thead>
            <tbody>
            <?php foreach ($activities as $i => $log):
                $dt = new DateTime($log['created_at'], new DateTimeZone('Asia/Dhaka'));
                $initials = strtoupper(substr($log['user_name'], 0, 1));
                $uc = $user_color_map[$log['user_name']] ?? '#6b7280';
                $ac = $action_colors[$log['action']] ?? $default_color;
            ?>
            <tr>
                <td style="color:#c5c9d6;font-size:11px;font-weight:500"><?= $offset + $i + 1 ?></td>
                <td>
                    <div class="user-cell">
                        <div class="avatar" style="background:<?= $uc ?>"><?= $initials ?></div>
                        <div class="user-info">
                            <div class="name"><?= htmlspecialchars($log['user_name']) ?></div>
                            <div class="role"><?= htmlspecialchars($log['user_role'] ?? '—') ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="action-pill" style="background:<?= $ac[1] ?>;color:<?= $ac[0] ?>">
                        <i class="<?= get_action_icon($log['action']) ?>"></i>
                        <?= get_action_label($log['action']) ?>
                    </span>
                </td>
                <td class="details-col"><?= htmlspecialchars($log['details']) ?></td>
                <td class="time-col">
                    <span class="date"><?= $dt->format('d M Y') ?></span><br>
                    <span class="time"><?= $dt->format('h:i:s A') ?></span>
                </td>
                <td class="ip-col"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $qp = $_GET; unset($qp['page']);
            $bq = http_build_query($qp) . ($qp ? '&' : '') . 'page=';
            if ($page > 1): ?>
            <a href="?<?= $bq.($page-1) ?>" class="pg"><i class="fas fa-chevron-left"></i></a>
            <?php endif;
            for ($p = max(1,$page-3); $p <= min($total_pages,$page+3); $p++): ?>
            <a href="?<?= $bq.$p ?>" class="pg <?= $p===$page?'active':'' ?>"><?= $p ?></a>
            <?php endfor;
            if ($page < $total_pages): ?>
            <a href="?<?= $bq.($page+1) ?>" class="pg"><i class="fas fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- ── Mobile Card View ── -->
    <div class="mobile-cards" style="display:none">
        <?php if (empty($activities)): ?>
        <div class="empty"><i class="fas fa-inbox"></i><h3>No activities found</h3></div>
        <?php else: ?>
        <?php foreach ($activities as $i => $log):
            $dt = new DateTime($log['created_at'], new DateTimeZone('Asia/Dhaka'));
            $initials = strtoupper(substr($log['user_name'], 0, 1));
            $uc = $user_color_map[$log['user_name']] ?? '#6b7280';
            $ac = $action_colors[$log['action']] ?? $default_color;
        ?>
        <div class="mobile-card">
            <div class="mobile-card-top">
                <div class="user-cell">
                    <div class="avatar" style="background:<?= $uc ?>;width:28px;height:28px;font-size:10px;border-radius:8px"><?= $initials ?></div>
                    <div class="user-info">
                        <div class="name" style="font-size:12px"><?= htmlspecialchars($log['user_name']) ?></div>
                        <div class="role" style="font-size:9px"><?= htmlspecialchars($log['user_role'] ?? '—') ?></div>
                    </div>
                </div>
                <span class="action-pill" style="background:<?= $ac[1] ?>;color:<?= $ac[0] ?>;font-size:10px;padding:4px 8px">
                    <i class="<?= get_action_icon($log['action']) ?>" style="font-size:9px"></i>
                    <?= get_action_label($log['action']) ?>
                </span>
            </div>
            <div class="details"><?= htmlspecialchars($log['details']) ?></div>
            <div class="meta">
                <span><i class="fas fa-clock"></i> <?= $dt->format('d M, h:i A') ?></span>
                <span><i class="fas fa-globe"></i> <?= htmlspecialchars($log['ip_address'] ?? '—') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="footer">
        Activity Monitor v1.0 &mdash; Mamun's Ortho Dental &copy; <?= date('Y') ?> &middot; Developed by <a href="https://umaerislam.com" target="_blank">Umaer Islam</a>
    </div>
</div>

<script>
// Custom Dropdown
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.mod-dropdown').forEach(root => {
        const trigger = root.querySelector('.mod-dropdown-trigger');
        const selected = root.querySelector('.mod-dropdown-selected');
        const panel = root.querySelector('.mod-dropdown-panel');
        const input = root.querySelector('input[type="hidden"]');
        const options = root.querySelectorAll('.mod-dropdown-option');
        function open() { root.classList.add('is-open'); }
        function close() { root.classList.remove('is-open'); }
        trigger.addEventListener('click', e => { e.stopPropagation(); root.classList.contains('is-open') ? close() : open(); });
        options.forEach(opt => {
            opt.addEventListener('click', () => {
                input.value = opt.dataset.value;
                selected.textContent = opt.querySelector('span').textContent;
                options.forEach(o => o.classList.remove('is-selected'));
                opt.classList.add('is-selected');
                close();
            });
        });
        document.addEventListener('click', e => { if (!root.contains(e.target)) close(); });
    });
    // Init calendars
    document.querySelectorAll('.mod-calendar').forEach(root => {
        const input = root.querySelector('input[type="hidden"]');
        const text = root.querySelector('.mod-calendar-text');
        const panel = root.querySelector('.mod-calendar-panel');
        const trigger = root.querySelector('.mod-calendar-trigger');
        const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        let viewDate = input.value ? new Date(input.value+'T00:00:00') : new Date();
        let selected = input.value || '';
        function fmtDate(d){return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0')}
        function fmtDisplay(d){return d.getDate()+' '+MONTHS[d.getMonth()].slice(0,3)+' '+d.getFullYear()}
        function render(dir){
            const daysEl=panel.querySelector('.cal-days'), myEl=panel.querySelector('.cal-month-year');
            const y=viewDate.getFullYear(),m=viewDate.getMonth();
            myEl.innerHTML='<span>'+MONTHS[m]+'</span><span>'+y+'</span>';
            const first=new Date(y,m,1).getDay(),dim=new Date(y,m+1,0).getDate(),dp=new Date(y,m,0).getDate();
            let h='';
            for(let i=first-1;i>=0;i--)h+='<button type="button" class="cal-day cal-day--other-month" data-date="'+fmtDate(new Date(y,m-1,dp-i))+'">'+(dp-i)+'</button>';
            const t=new Date();t.setHours(0,0,0,0);
            for(let d=1;d<=dim;d++){const dt=new Date(y,m,d),ds=fmtDate(dt);let c='cal-day';if(ds===fmtDate(t))c+=' cal-day--today';if(ds===selected)c+=' cal-day--selected';h+='<button type="button" class="'+c+'" data-date="'+ds+'">'+d+'</button>'}
            const tc=first+dim,rem=tc%7===0?0:7-(tc%7);for(let i=1;i<=rem;i++)h+='<button type="button" class="cal-day cal-day--other-month" data-date="'+fmtDate(new Date(y,m+1,i))+'">'+i+'</button>';
            daysEl.innerHTML=h;
            if(dir){daysEl.classList.remove('cal-days--slide-left','cal-days--slide-right');void daysEl.offsetWidth;daysEl.classList.add(dir==='left'?'cal-days--slide-left':'cal-days--slide-right')}
            daysEl.querySelectorAll('.cal-day').forEach(btn=>{btn.addEventListener('click',()=>{selected=btn.dataset.date;input.value=selected;text.textContent=fmtDisplay(new Date(selected+'T00:00:00'));root.classList.add('has-value');root.classList.remove('is-open')})});
        }
        function open(){if(input.value)viewDate=new Date(input.value+'T00:00:00');render();root.classList.add('is-open')}
        function close(){root.classList.remove('is-open')}
        trigger.addEventListener('click',e=>{e.stopPropagation();root.classList.contains('is-open')?close():open()});
        panel.querySelector('.cal-prev').addEventListener('click',e=>{e.stopPropagation();viewDate.setMonth(viewDate.getMonth()-1);render('right')});
        panel.querySelector('.cal-next').addEventListener('click',e=>{e.stopPropagation();viewDate.setMonth(viewDate.getMonth()+1);render('left')});
        panel.querySelector('.cal-today-btn').addEventListener('click',e=>{e.stopPropagation();const t=new Date();viewDate=new Date(t);selected=fmtDate(t);input.value=selected;text.textContent=fmtDisplay(t);root.classList.add('has-value');render();close()});
        panel.addEventListener('click',e=>e.stopPropagation());
        if(selected){text.textContent=fmtDisplay(new Date(selected+'T00:00:00'));root.classList.add('has-value')}
    });
    document.addEventListener('click',()=>{document.querySelectorAll('.mod-calendar.is-open').forEach(c=>c.classList.remove('is-open'))});
});

// Show mobile cards on small screens
function checkMobile(){
    const mc = document.querySelector('.mobile-cards');
    const dt = document.querySelector('.desktop-table');
    if(window.innerWidth <= 768){
        if(mc) mc.style.display = 'block';
        if(dt) dt.style.display = 'none';
    } else {
        if(mc) mc.style.display = 'none';
        if(dt) dt.style.display = 'block';
    }
}
checkMobile();
window.addEventListener('resize', checkMobile);

// Auto-refresh every 30s
setTimeout(() => location.reload(), 30000);
</script>
</body>
</html>
