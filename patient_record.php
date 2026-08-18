<?php
session_start();
require_once 'database/connection.php';
require_once 'components/rate_limiter.php';

$pid = trim($_GET['pid'] ?? '');
$token = trim($_GET['token'] ?? '');

if (empty($pid) || $pdo === null) {
    header('Location: index.php');
    exit;
}

$rateLimiter = new RateLimiter($pdo);
$rateCheck = $rateLimiter->check('patient_portal', 20, 300, 300);
if (!$rateCheck['allowed']) {
    header('Location: index.php?error=Too+many+requests.+Please+try+again+later.');
    exit;
}
$rateLimiter->record('patient_portal');

$isAdminOrDoctor = isset($_SESSION['user_id']) && isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'doctor']);

$patient = null;
$prescriptions = [];
$appointments = [];
$payments = [];
$cashMemos = [];
$summary = ['total_paid' => 0, 'total_visits' => 0, 'last_visit' => null, 'active_rx' => null, 'total_prescriptions' => 0];

if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ? AND access_token = ? LIMIT 1");
        $stmt->execute([$pid, $token]);
        $patient = $stmt->fetch();
    } catch (Exception $e) {}
}

if (!$patient && $isAdminOrDoctor) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ? LIMIT 1");
        $stmt->execute([$pid]);
        $patient = $stmt->fetch();
    } catch (Exception $e) {}
}

if (!$patient && !$isAdminOrDoctor && !empty($pid)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ? LIMIT 1");
        $stmt->execute([$pid]);
        $patient = $stmt->fetch();
    } catch (Exception $e) {}
}

if (!$patient) {
    header('Location: index.php');
    exit;
}

$dbId = $patient['id'];

try {
    $rxStmt = $pdo->prepare("SELECT p.*, u.name AS doctor_name, u.degrees AS doctor_degrees FROM prescriptions p LEFT JOIN users u ON p.doctor_id = u.id WHERE p.patient_id = ? ORDER BY p.created_at DESC");
    $rxStmt->execute([$dbId]);
    $prescriptions = $rxStmt->fetchAll();
    foreach ($prescriptions as &$rx) { $rx['medicines'] = json_decode($rx['medicines'] ?? '[]', true); }
    unset($rx);

    $aptStmt = $pdo->prepare("SELECT a.*, u.name AS doctor_name FROM appointments a LEFT JOIN users u ON a.doctor_id = u.id WHERE a.patient_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $aptStmt->execute([$dbId]);
    $appointments = $aptStmt->fetchAll();

    $payStmt = $pdo->prepare("SELECT * FROM payments WHERE patient_id = ? ORDER BY created_at DESC");
    $payStmt->execute([$dbId]);
    $payments = $payStmt->fetchAll();

    $memoStmt = $pdo->prepare("SELECT * FROM cash_memos WHERE customer_phone = ? ORDER BY created_at DESC");
    $memoStmt->execute([$patient['phone']]);
    $cashMemos = $memoStmt->fetchAll();
    foreach ($cashMemos as &$memo) {
        $itemsStmt = $pdo->prepare("SELECT * FROM cash_memo_items WHERE memo_id = ?");
        $itemsStmt->execute([$memo['id']]);
        $memo['items'] = $itemsStmt->fetchAll();
    }
    unset($memo);

    $summary['total_paid'] = array_sum(array_column($payments, 'amount'));
    $summary['total_visits'] = count($appointments);
    $summary['last_visit'] = $appointments[0]['appointment_date'] ?? null;
    $summary['total_prescriptions'] = count($prescriptions);
} catch (Exception $e) {}

$portalUrl = "https://mamunorthodental.com/patient_record.php?pid={$pid}" . ($token ? "&token={$token}" : '');
$initial = strtoupper(substr($patient['name'], 0, 1));
$patientName = htmlspecialchars($patient['name']);
$patientId = htmlspecialchars($patient['patient_id']);
$age = $patient['age'] ?? null;
$gender = $patient['gender'] ?? null;
$bloodGroup = $patient['blood_group'] ?? null;
$phone = $patient['phone'] ?? null;
$address = $patient['address'] ?? null;
$notes = $patient['notes'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=$patientName?> — Patient Portal</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#004591">
<link rel="icon" type="image/png" href="Logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#004591','navy-dark':'#003070',gold:'#ea741b'},fontFamily:{serif:['"Playfair Display"','serif'],sans:['"Outfit"','sans-serif']}}}}</script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Outfit',sans-serif;background:#f6f8fb;color:#1a1a2e;-webkit-font-smoothing:antialiased}

.pill{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:10px;font-size:11px;font-weight:600;white-space:nowrap}
.pill-navy{background:#004591;color:#fff}
.pill-gold{background:rgba(234,116,27,.1);color:#ea741b}
.pill-green{background:rgba(34,197,94,.1);color:#16a34a}
.pill-red{background:rgba(239,68,68,.1);color:#dc2626}
.pill-purple{background:rgba(139,92,246,.1);color:#7c3aed}
.pill-gray{background:#f1f5f9;color:#64748b}
.pill-amber{background:rgba(245,158,11,.1);color:#d97706}
.pill-blue{background:rgba(59,130,246,.1);color:#2563eb}

.empty-state{text-align:center;padding:60px 20px}
.empty-state i{font-size:40px;color:#e2e8f0;margin-bottom:16px;display:block}
.empty-state p{color:#94a3b8;font-size:14px}

.card{background:#fff;border:1px solid #e8ecf1;border-radius:16px;transition:box-shadow .2s ease,transform .15s ease}
.card:hover{box-shadow:0 8px 30px rgba(0,69,145,.06)}
.card-header{padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between}
.card-body{padding:24px}

.tab-btn{position:relative;padding:10px 20px;border-radius:12px;font-size:12px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;cursor:pointer;transition:all .25s ease;border:none;background:transparent;color:#94a3b8;white-space:nowrap}
.tab-btn:hover{color:#475569;background:#f1f5f9}
.tab-btn.active{color:#fff;background:#004591;box-shadow:0 4px 12px rgba(0,69,145,.2)}

.rx-card{border:1px solid #e8ecf1;border-radius:16px;background:#fff;transition:all .2s ease;overflow:hidden}
.rx-card:hover{box-shadow:0 8px 30px rgba(0,69,145,.06)}

.timeline-item{position:relative;padding-left:32px;padding-bottom:28px}
.timeline-item::before{content:'';position:absolute;left:5px;top:8px;width:10px;height:10px;border-radius:50%;background:#004591;border:2px solid #fff;box-shadow:0 0 0 2px #004591;z-index:1}
.timeline-item::after{content:'';position:absolute;left:9px;top:22px;width:2px;bottom:0;background:linear-gradient(to bottom,#e2e8f0,#f1f5f9)}
.timeline-item:last-child::after{display:none}
.timeline-item.completed::before{background:#16a34a;box-shadow:0 0 0 2px #16a34a}
.timeline-item.cancelled::before{background:#dc2626;box-shadow:0 0 0 2px #dc2626}
.timeline-item.follow-up::before{background:#7c3aed;box-shadow:0 0 0 2px #7c3aed}
.timeline-item.waiting::before{background:#d97706;box-shadow:0 0 0 2px #d97706}

.stat-metric{display:flex;align-items:center;gap:14px;padding:16px 20px;background:#fff;border:1px solid #e8ecf1;border-radius:14px;transition:all .2s ease}
.stat-metric:hover{box-shadow:0 4px 16px rgba(0,0,0,.04);transform:translateY(-1px)}
.stat-icon{width:40px;height:40px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}

.detail-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f8fafc}
.detail-row:last-child{border-bottom:none}
.detail-label{color:#94a3b8;font-size:13px;font-weight:500}
.detail-value{font-size:13px;font-weight:600;color:#1e293b;text-align:right;max-width:60%}

.table-wrap{overflow-x:auto}
.table-wrap table{width:100%;border-collapse:collapse}
.table-wrap th{text-align:left;padding:10px 16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e8ecf1}
.table-wrap td{padding:12px 16px;font-size:13px;color:#475569;border-bottom:1px solid #f1f5f9}
.table-wrap tr:last-child td{border-bottom:none}
.table-wrap tr:hover td{background:#f8fafc}

.fade-in{animation:fadeUp .4s ease forwards;opacity:0}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

@media print{
  .no-print{display:none!important}
  body{background:#fff}
  .card{border:1px solid #e2e8f0;box-shadow:none}
}
</style>
</head>
<body class="min-h-screen">

<!-- Slim Header -->
<header class="bg-white border-b border-gray-100 sticky top-0 z-50 no-print">
  <div class="max-w-4xl mx-auto px-5 h-14 flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-2.5">
      <img src="Logo.png" alt="Logo" class="w-8 h-8 object-contain">
      <div class="flex flex-col leading-none">
        <span class="font-serif text-[15px] font-bold text-[#004591]">Mamun's <span class="text-[#ea741b]">Ortho</span></span>
        <span class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400">Patient Portal</span>
      </div>
    </a>
    <div class="flex items-center gap-2">
      <button onclick="window.print()" class="w-8 h-8 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center transition-colors text-gray-400 hover:text-[#004591]" title="Print"><i class="fas fa-print text-xs"></i></button>
      <a href="index.php#track" class="h-8 px-3 rounded-lg bg-gray-50 hover:bg-gray-100 flex items-center justify-center transition-colors text-gray-400 hover:text-[#004591]"><i class="fas fa-search text-xs mr-1.5"></i><span class="text-[11px] font-bold uppercase tracking-wide">Search</span></a>
    </div>
  </div>
</header>

<!-- Patient Hero -->
<div class="max-w-4xl mx-auto px-5 mt-6 mb-5">
  <div class="card card-body fade-in">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
      <div class="relative flex-shrink-0">
        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-[#004591] to-[#002d5c] flex items-center justify-center text-white font-serif text-xl font-bold shadow-lg shadow-[#004591]/15"><?=$initial?></div>
        <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-emerald-400 border-2 border-white"></div>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
          <h1 class="font-serif text-xl font-bold text-[#004591]"><?=$patientName?></h1>
          <span class="pill pill-gold" style="font-size:10px;padding:3px 8px"><?=$patientId?></span>
        </div>
        <div class="flex flex-wrap items-center gap-3 text-gray-400 text-xs">
          <?php if($age): ?><span class="flex items-center gap-1"><i class="fas fa-cake-candles text-[10px]"></i> <?=$age?> yrs</span><?php endif; ?>
          <?php if($gender): ?><span class="flex items-center gap-1"><i class="fas fa-venus-mars text-[10px]"></i> <?=htmlspecialchars($gender)?></span><?php endif; ?>
          <?php if($bloodGroup): ?><span class="flex items-center gap-1"><i class="fas fa-droplet text-[10px] text-red-400"></i> <?=htmlspecialchars($bloodGroup)?></span><?php endif; ?>
          <?php if($phone): ?><span class="flex items-center gap-1"><i class="fas fa-phone text-[10px]"></i> <?=htmlspecialchars($phone)?></span><?php endif; ?>
        </div>
      </div>
      <div class="flex items-center gap-5 sm:gap-6">
        <div class="text-center">
          <p class="font-serif text-xl font-bold text-[#004591]"><?=$summary['total_visits']?></p>
          <p class="text-[9px] font-bold uppercase tracking-[.1em] text-gray-400 mt-0.5">Visits</p>
        </div>
        <div class="w-px h-8 bg-gray-100"></div>
        <div class="text-center">
          <p class="font-serif text-xl font-bold text-[#ea741b]"><?=number_format($summary['total_paid'])?></p>
          <p class="text-[9px] font-bold uppercase tracking-[.1em] text-gray-400 mt-0.5">Paid (৳)</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="max-w-4xl mx-auto px-5 mb-5 no-print">
  <div class="flex gap-1.5 overflow-x-auto pb-1 -mx-1 px-1" id="tabBar">
    <button onclick="showTab('overview')" class="tab-btn active" data-tab="overview"><i class="fas fa-th-large mr-1.5 text-[10px]"></i>Overview</button>
    <button onclick="showTab('prescriptions')" class="tab-btn" data-tab="prescriptions"><i class="fas fa-pills mr-1.5 text-[10px]"></i>Prescriptions <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-[9px] font-bold"><?=count($prescriptions)?></span></button>
    <button onclick="showTab('appointments')" class="tab-btn" data-tab="appointments"><i class="fas fa-calendar-check mr-1.5 text-[10px]"></i>Appointments <span class="ml-1 inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 text-[9px] font-bold"><?=count($appointments)?></span></button>
    <button onclick="showTab('payments')" class="tab-btn" data-tab="payments"><i class="fas fa-receipt mr-1.5 text-[10px]"></i>Payments</button>
    <button onclick="showTab('billing')" class="tab-btn" data-tab="billing"><i class="fas fa-file-invoice mr-1.5 text-[10px]"></i>Billing</button>
  </div>
</div>

<!-- Tab Content -->
<main class="max-w-4xl mx-auto px-5 pb-16">

  <!-- OVERVIEW -->
  <div id="tab-overview" class="tab-content">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
      <div class="stat-metric">
        <div class="stat-icon bg-[#004591]/8 text-[#004591]"><i class="fas fa-pills"></i></div>
        <div>
          <p class="font-serif text-lg font-bold text-[#004591] leading-none"><?=$summary['total_prescriptions']?></p>
          <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mt-1">Prescriptions</p>
        </div>
      </div>
      <div class="stat-metric">
        <div class="stat-icon bg-purple-500/8 text-purple-600"><i class="fas fa-calendar-check"></i></div>
        <div>
          <p class="font-serif text-lg font-bold text-purple-600 leading-none"><?=$summary['total_visits']?></p>
          <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mt-1">Total Visits</p>
        </div>
      </div>
      <div class="stat-metric">
        <div class="stat-icon bg-[#ea741b]/8 text-[#ea741b]"><i class="fas fa-receipt"></i></div>
        <div>
          <p class="font-serif text-lg font-bold text-[#ea741b] leading-none"><?=number_format($summary['total_paid'])?></p>
          <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mt-1">Total Paid (৳)</p>
        </div>
      </div>
      <div class="stat-metric">
        <div class="stat-icon bg-emerald-500/8 text-emerald-600"><i class="fas fa-clock"></i></div>
        <div>
          <p class="font-serif text-lg font-bold text-emerald-600 leading-none"><?=$summary['last_visit'] ? date('d M', strtotime($summary['last_visit'])) : '—'?></p>
          <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 mt-1">Last Visit</p>
        </div>
      </div>
    </div>

    <!-- Patient Details -->
    <div class="card mb-5">
      <div class="card-header">
        <h3 class="font-serif text-base font-bold text-[#004591] flex items-center gap-2"><i class="fas fa-id-card text-[#ea741b] text-xs"></i> Patient Details</h3>
      </div>
      <div class="card-body" style="padding:8px 24px 16px">
        <div class="grid sm:grid-cols-2">
          <div class="detail-row sm:pr-6"><span class="detail-label">Patient ID</span><span class="detail-value font-mono text-[#004591]"><?=$patientId?></span></div>
          <div class="detail-row"><span class="detail-label">Full Name</span><span class="detail-value"><?=$patientName?></span></div>
          <div class="detail-row sm:pr-6"><span class="detail-label">Phone</span><span class="detail-value"><?=htmlspecialchars($phone ?? '—')?></span></div>
          <div class="detail-row"><span class="detail-label">Age</span><span class="detail-value"><?=$age ? $age.' years' : '—'?></span></div>
          <div class="detail-row sm:pr-6"><span class="detail-label">Gender</span><span class="detail-value"><?=htmlspecialchars($gender ?? '—')?></span></div>
          <div class="detail-row"><span class="detail-label">Blood Group</span><span class="detail-value"><?=htmlspecialchars($bloodGroup ?? '—')?></span></div>
        </div>
        <?php if($address): ?>
        <div class="detail-row"><span class="detail-label">Address</span><span class="detail-value"><?=htmlspecialchars($address)?></span></div>
        <?php endif; ?>
        <?php if($notes): ?>
        <div class="mt-3 p-3 bg-amber-50/60 border border-amber-100 rounded-xl">
          <p class="text-[10px] font-bold uppercase tracking-wide text-amber-600 mb-1"><i class="fas fa-notes-medical mr-1"></i> Medical Notes</p>
          <p class="text-xs text-gray-600 leading-relaxed"><?=htmlspecialchars($notes)?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Latest Prescription -->
    <?php if(!empty($prescriptions[0])): ?>
    <?php $latest = $prescriptions[0]; ?>
    <div class="card">
      <div class="card-header">
        <h3 class="font-serif text-base font-bold text-[#004591] flex items-center gap-2"><i class="fas fa-prescription text-[#ea741b] text-xs"></i> Latest Prescription</h3>
        <button onclick="showTab('prescriptions')" class="text-[#ea741b] text-[11px] font-bold hover:underline">View All <i class="fas fa-arrow-right text-[9px] ml-0.5"></i></button>
      </div>
      <div class="card-body">
        <div class="flex items-start justify-between mb-3">
          <div>
            <span class="pill pill-gold" style="font-size:9px;padding:3px 8px"><?=date('d M Y', strtotime($latest['rx_date'] ?? $latest['created_at']))?></span>
            <p class="text-xs text-gray-500 mt-2"><?=htmlspecialchars($latest['doctor_name'] ?? 'Doctor')?></p>
          </div>
          <?php if($isAdminOrDoctor): ?>
          <a href="print_prescription.php?id=<?=$latest['id']?>&pid=<?=urlencode($patient['patient_id'])?>" target="_blank" class="pill pill-navy" style="font-size:9px;padding:4px 10px"><i class="fas fa-print"></i> Print</a>
          <?php else: ?>
          <a href="print_prescription_designed.php?id=<?=$latest['id']?>" target="_blank" class="pill pill-navy" style="font-size:9px;padding:4px 10px"><i class="fas fa-print"></i> Print</a>
          <?php endif; ?>
        </div>
        <?php if(!empty($latest['diagnosis'])): ?>
        <div class="bg-gray-50 rounded-xl p-3.5 mb-3">
          <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1">Diagnosis</p>
          <p class="text-sm text-gray-700 font-medium"><?=htmlspecialchars($latest['diagnosis'])?></p>
        </div>
        <?php endif; ?>
        <?php if(!empty($latest['medicines'])): ?>
        <div class="flex flex-wrap gap-1.5">
          <?php foreach($latest['medicines'] as $med): ?>
          <span class="inline-flex items-center gap-1.5 bg-[#004591]/5 border border-[#004591]/10 rounded-lg px-3 py-1.5 text-xs font-medium text-[#004591]"><i class="fas fa-pill text-[9px]"></i><?=htmlspecialchars($med['name'])?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- PRESCRIPTIONS -->
  <div id="tab-prescriptions" class="tab-content hidden">
    <?php if(empty($prescriptions)): ?>
    <div class="empty-state"><i class="fas fa-pills"></i><p>No prescriptions yet.</p></div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach($prescriptions as $rx): ?>
      <div class="rx-card">
        <div class="p-5">
          <div class="flex items-start justify-between mb-3">
            <div class="flex flex-wrap items-center gap-2">
              <span class="pill pill-gold" style="font-size:9px;padding:3px 8px"><?=date('d M Y', strtotime($rx['rx_date'] ?? $rx['created_at']))?></span>
              <?php if($rx['follow_up']): ?><span class="pill pill-purple" style="font-size:9px;padding:3px 8px"><i class="fas fa-redo text-[8px]"></i> Follow-up: <?=htmlspecialchars($rx['follow_up'])?></span><?php endif; ?>
            </div>
            <?php if($isAdminOrDoctor): ?>
            <a href="print_prescription.php?id=<?=$rx['id']?>&pid=<?=urlencode($patient['patient_id'])?>" target="_blank" class="pill pill-navy" style="font-size:9px;padding:4px 10px"><i class="fas fa-print"></i> Print</a>
            <?php else: ?>
            <a href="print_prescription_designed.php?id=<?=$rx['id']?>" target="_blank" class="pill pill-navy" style="font-size:9px;padding:4px 10px"><i class="fas fa-print"></i> Print</a>
            <?php endif; ?>
          </div>

          <p class="text-xs text-gray-500 mb-3"><?=htmlspecialchars($rx['doctor_name'] ?? 'Doctor')?> <?=$rx['doctor_degrees'] ? '<span class="text-gray-400">('.htmlspecialchars($rx['doctor_degrees']).')</span>' : ''?></p>

          <?php if(!empty($rx['diagnosis'])): ?>
          <div class="bg-gray-50 rounded-xl p-3.5 mb-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-1">Diagnosis</p>
            <p class="text-sm text-gray-700"><?=htmlspecialchars($rx['diagnosis'])?></p>
          </div>
          <?php endif; ?>

          <?php if(!empty($rx['investigations'])): ?>
          <div class="bg-blue-50/60 border border-blue-100 rounded-xl p-3.5 mb-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-blue-500 mb-1"><i class="fas fa-microscope mr-1"></i> Investigations</p>
            <p class="text-sm text-gray-600"><?=nl2br(htmlspecialchars($rx['investigations']))?></p>
          </div>
          <?php endif; ?>

          <?php if(!empty($rx['medicines'])): ?>
          <div class="mb-3">
            <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400 mb-2">Medicines</p>
            <div class="space-y-2">
              <?php foreach($rx['medicines'] as $i => $med): ?>
              <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
                <span class="w-6 h-6 rounded-full bg-[#004591]/10 text-[#004591] text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5"><?=$i+1?></span>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-gray-700"><?=htmlspecialchars($med['name'])?></p>
                  <div class="flex flex-wrap gap-x-3 gap-y-1 mt-1">
                    <?php if(!empty($med['frequency'])): ?><span class="text-[10px] text-gray-500"><i class="fas fa-clock text-[8px] mr-1 text-gray-400"></i><?=htmlspecialchars(str_replace('+', ' + ', $med['frequency']))?></span><?php endif; ?>
                    <?php if(!empty($med['duration'])): ?><span class="text-[10px] text-gray-500"><i class="fas fa-calendar text-[8px] mr-1 text-gray-400"></i><?=htmlspecialchars($med['duration'])?></span><?php endif; ?>
                    <?php if(!empty($med['note'])): ?><span class="text-[10px] text-[#ea741b]"><i class="fas fa-info-circle text-[8px] mr-1"></i><?=htmlspecialchars($med['note'])?></span><?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if(!empty($rx['advice'])): ?>
          <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-3.5">
            <p class="text-[10px] font-bold uppercase tracking-wide text-amber-600 mb-1"><i class="fas fa-lightbulb mr-1"></i> Advice</p>
            <p class="text-sm text-gray-600"><?=nl2br(htmlspecialchars($rx['advice']))?></p>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- APPOINTMENTS -->
  <div id="tab-appointments" class="tab-content hidden">
    <?php if(empty($appointments)): ?>
    <div class="empty-state"><i class="fas fa-calendar-check"></i><p>No appointments yet.</p></div>
    <?php else: ?>
    <div class="card card-body">
      <?php foreach($appointments as $apt):
        $status = strtolower($apt['status'] ?? 'waiting');
        $statusPill = match($status) {
          'completed' => 'pill-green',
          'waiting' => 'pill-amber',
          'in treatment' => 'pill-blue',
          'follow-up', 'follow up' => 'pill-purple',
          'cancelled' => 'pill-red',
          default => 'pill-gray'
        };
        $tlClass = match($status) {
          'completed' => 'completed',
          'cancelled' => 'cancelled',
          'follow-up', 'follow up' => 'follow-up',
          'waiting' => 'waiting',
          default => ''
        };
      ?>
      <div class="timeline-item <?=$tlClass?>">
        <div class="bg-gray-50 rounded-xl p-4 -mt-1">
          <div class="flex items-start justify-between mb-2">
            <div>
              <p class="text-xs font-bold text-gray-500"><?=date('l, d M Y', strtotime($apt['appointment_date']))?></p>
              <?php if($apt['appointment_time']): ?><p class="text-sm font-semibold text-gray-700 mt-1"><i class="fas fa-clock text-gray-300 mr-1 text-[10px]"></i><?=date('h:i A', strtotime($apt['appointment_time']))?></p><?php endif; ?>
            </div>
            <span class="pill <?=$statusPill?>" style="font-size:9px;padding:3px 8px"><?=htmlspecialchars($apt['status'] ?? 'Waiting')?></span>
          </div>
          <?php if($apt['doctor_name']): ?><p class="text-xs text-gray-500 mt-2"><i class="fas fa-user-doctor mr-1 text-gray-400 text-[10px]"></i><?=htmlspecialchars($apt['doctor_name'])?></p><?php endif; ?>
          <?php if($apt['description']): ?><p class="text-xs text-gray-500 mt-2 bg-white rounded-lg p-3 border border-gray-100"><?=htmlspecialchars($apt['description'])?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- PAYMENTS -->
  <div id="tab-payments" class="tab-content hidden">
    <?php if(empty($payments)): ?>
    <div class="empty-state"><i class="fas fa-receipt"></i><p>No payments recorded yet.</p></div>
    <?php else: ?>
    <div class="card">
      <div class="card-header">
        <h3 class="font-serif text-base font-bold text-[#004591]">Payment History</h3>
        <div class="text-right">
          <p class="text-[9px] font-bold uppercase tracking-wide text-gray-400">Total Paid</p>
          <p class="font-serif text-lg font-bold text-[#ea741b]">৳ <?=number_format($summary['total_paid'])?></p>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Notes</th></tr></thead>
          <tbody>
            <?php foreach($payments as $pay): ?>
            <tr>
              <td><?=date('d M Y', strtotime($pay['created_at']))?></td>
              <td class="font-semibold text-[#004591]">৳ <?=number_format($pay['amount'])?></td>
              <td><span class="pill pill-gray" style="font-size:9px;padding:2px 8px"><?=htmlspecialchars($pay['payment_method'] ?? 'Cash')?></span></td>
              <td class="text-gray-400 text-xs"><?=htmlspecialchars($pay['notes'] ?? '—')?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- BILLING -->
  <div id="tab-billing" class="tab-content hidden">
    <?php if(empty($cashMemos)): ?>
    <div class="empty-state"><i class="fas fa-file-invoice"></i><p>No billing records yet.</p></div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach($cashMemos as $memo): ?>
      <div class="card overflow-hidden">
        <div class="card-header">
          <div>
            <p class="pill pill-gold" style="font-size:9px;padding:3px 8px"><?=htmlspecialchars($memo['memo_number'] ?? 'Cash Memo')?></p>
            <p class="text-xs text-gray-400 mt-2"><?=date('d M Y', strtotime($memo['memo_date'] ?? $memo['created_at']))?></p>
          </div>
          <div class="text-right">
            <p class="text-[9px] font-bold uppercase tracking-wide text-gray-400">Total</p>
            <p class="font-serif text-lg font-bold text-[#004591]">৳ <?=number_format($memo['grand_total'] ?? 0, 2)?></p>
          </div>
        </div>
        <?php if(!empty($memo['items'])): ?>
        <div class="card-body" style="padding-top:0">
          <div class="table-wrap">
            <table>
              <thead><tr><th>Item</th><th class="text-center">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
              <tbody>
                <?php foreach($memo['items'] as $item): ?>
                <tr>
                  <td><?=htmlspecialchars($item['description'])?></td>
                  <td class="text-center"><?=$item['quantity']?></td>
                  <td class="text-right">৳ <?=number_format($item['unit_price'], 2)?></td>
                  <td class="text-right font-semibold text-gray-700">৳ <?=number_format($item['total'], 2)?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="mt-3 pt-3 border-t border-gray-100 space-y-1 text-sm text-right">
            <p class="text-gray-400">Subtotal: ৳ <?=number_format($memo['subtotal'] ?? 0, 2)?></p>
            <?php if(($memo['discount'] ?? 0) > 0): ?><p class="text-emerald-500">Discount: -৳ <?=number_format($memo['discount'], 2)?></p><?php endif; ?>
            <p class="font-bold text-[#004591]">Grand Total: ৳ <?=number_format($memo['grand_total'] ?? 0, 2)?></p>
          </div>
          <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
            <span><i class="fas fa-credit-card mr-1"></i> <?=htmlspecialchars($memo['payment_method'] ?? 'Cash')?></span>
            <a href="print_cash_memo.php?id=<?=$memo['id']?>" target="_blank" class="pill pill-navy" style="font-size:9px;padding:3px 8px"><i class="fas fa-print mr-1"></i> Print</a>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

</main>

<!-- Footer -->
<footer class="border-t border-gray-100 py-5 no-print">
  <p class="text-center text-gray-300 text-[11px] font-medium">&copy; <?=date('Y')?> Mamun's Ortho Dental &middot; Lalmatia, Dhaka-1207</p>
</footer>

<script>
function showTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-' + tab).classList.remove('hidden');
  document.querySelector('[data-tab="' + tab + '"]').classList.add('active');
}
</script>
</body>
</html>
