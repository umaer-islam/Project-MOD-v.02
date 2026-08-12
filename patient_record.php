<?php
session_start();
require_once 'database/connection.php';

$pid = trim($_GET['pid'] ?? '');
$token = trim($_GET['token'] ?? '');

if (empty($pid) || $pdo === null) {
    header('Location: index.php');
    exit;
}

// Fetch patient data with token if available
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

// Fallback: fetch without token (for old MOD-XXXX IDs without tokens)
if (!$patient) {
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
    // Prescriptions
    $rxStmt = $pdo->prepare("SELECT p.*, u.name AS doctor_name, u.degrees AS doctor_degrees FROM prescriptions p LEFT JOIN users u ON p.doctor_id = u.id WHERE p.patient_id = ? ORDER BY p.created_at DESC");
    $rxStmt->execute([$dbId]);
    $prescriptions = $rxStmt->fetchAll();
    foreach ($prescriptions as &$rx) { $rx['medicines'] = json_decode($rx['medicines'] ?? '[]', true); }
    unset($rx);

    // Appointments
    $aptStmt = $pdo->prepare("SELECT a.*, u.name AS doctor_name FROM appointments a LEFT JOIN users u ON a.doctor_id = u.id WHERE a.patient_id = ? ORDER BY a.appointment_date DESC, a.appointment_time DESC");
    $aptStmt->execute([$dbId]);
    $appointments = $aptStmt->fetchAll();

    // Payments
    $payStmt = $pdo->prepare("SELECT * FROM payments WHERE patient_id = ? ORDER BY created_at DESC");
    $payStmt->execute([$dbId]);
    $payments = $payStmt->fetchAll();

    // Cash Memos
    $memoStmt = $pdo->prepare("SELECT * FROM cash_memos WHERE customer_phone = ? ORDER BY created_at DESC");
    $memoStmt->execute([$patient['phone']]);
    $cashMemos = $memoStmt->fetchAll();
    foreach ($cashMemos as &$memo) {
        $itemsStmt = $pdo->prepare("SELECT * FROM cash_memo_items WHERE memo_id = ?");
        $itemsStmt->execute([$memo['id']]);
        $memo['items'] = $itemsStmt->fetchAll();
    }
    unset($memo);

    // Summary
    $summary['total_paid'] = array_sum(array_column($payments, 'amount'));
    $summary['total_visits'] = count($appointments);
    $summary['last_visit'] = $appointments[0]['appointment_date'] ?? null;
    $summary['total_prescriptions'] = count($prescriptions);
} catch (Exception $e) {}

// Generate portal URL for QR
$portalUrl = "https://mamunorthodental.com/patient_record.php?pid={$pid}" . ($token ? "&token={$token}" : '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?=$patient['name']?> — Patient Portal | Mamun's Ortho Dental</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#004591">
<link rel="icon" type="image/png" href="Logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500;1,600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{colors:{navy:'#004591','navy-dark':'#003070',gold:'#ea741b'},fontFamily:{serif:['"Playfair Display"','serif'],sans:['"Outfit"','sans-serif']}}}}</script>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Outfit',sans-serif;background:#F8FAFD;color:#1a1a2e}
.tab-active{color:#004591;border-bottom:2px solid #ea741b;background:rgba(234,116,27,.05)}
.status-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:9999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em}
.status-completed{background:#dcfce7;color:#166534}
.status-waiting{background:#fef3c7;color:#92400e}
.status-in-treatment{background:#dbeafe;color:#1e40af}
.status-follow-up{background:#ede9fe;color:#5b21b6}
.status-cancelled{background:#fee2e2;color:#991b1b}
.status-default{background:#f1f5f9;color:#475569}
.rx-card{border:1px solid #e2e8f0;border-radius:16px;padding:20px;background:#fff;transition:box-shadow .2s}
.rx-card:hover{box-shadow:0 8px 24px rgba(0,69,145,.08)}
.med-pill{display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:10px;padding:6px 12px;font-size:12px;color:#334155}
.timeline-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0;border:2px solid #fff;box-shadow:0 0 0 2px #004591}
.timeline-line{width:2px;background:linear-gradient(to bottom,#004591,#e2e8f0);flex-shrink:0;margin-left:5px}
@media print{.no-print{display:none!important}body{background:#fff}}
</style>
</head>
<body class="min-h-screen">

<!-- Header -->
<header class="bg-[#004591] text-white no-print">
  <div class="max-w-5xl mx-auto px-5 py-6 flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-3">
      <img src="Logo.png" alt="Logo" class="w-9 h-9 object-contain">
      <div class="flex flex-col leading-none">
        <span class="font-serif text-lg font-bold">Mamun's <span class="text-[#ea741b]">Ortho</span></span>
        <span class="text-white/40 text-[8px] tracking-[.2em] uppercase font-bold">Patient Portal</span>
      </div>
    </a>
    <div class="flex items-center gap-3">
      <button onclick="window.print()" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all" title="Print"><i class="fas fa-print text-sm"></i></button>
      <a href="index.php#track" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-[11px] font-bold uppercase tracking-widest transition-all"><i class="fas fa-search text-xs mr-1"></i> New Search</a>
    </div>
  </div>
</header>

<!-- Patient Info Card -->
<div class="max-w-5xl mx-auto px-5 -mt-0 relative z-10 no-print">
  <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mt-8 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
      <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#004591] to-[#003070] flex items-center justify-center text-white font-serif text-2xl font-bold shadow-lg">
        <?= strtoupper(substr($patient['name'], 0, 1)) ?>
      </div>
      <div class="flex-1">
        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mb-2">
          <h1 class="font-serif text-2xl font-bold text-[#004591]"><?= htmlspecialchars($patient['name']) ?></h1>
          <span class="inline-flex items-center gap-1 px-3 py-1 bg-[#ea741b]/10 text-[#ea741b] text-[10px] font-bold uppercase tracking-widest rounded-full"><?= htmlspecialchars($patient['patient_id']) ?></span>
        </div>
        <div class="flex flex-wrap items-center gap-4 text-gray-400 text-xs">
          <?php if($patient['age']): ?><span><i class="fas fa-cake-candles mr-1"></i> <?= $patient['age'] ?> years</span><?php endif; ?>
          <?php if($patient['gender']): ?><span><i class="fas fa-venus-mars mr-1"></i> <?= htmlspecialchars($patient['gender']) ?></span><?php endif; ?>
          <?php if($patient['blood_group']): ?><span><i class="fas fa-droplet mr-1 text-red-400"></i> <?= htmlspecialchars($patient['blood_group']) ?></span><?php endif; ?>
          <?php if($patient['phone']): ?><span><i class="fas fa-phone mr-1"></i> <?= htmlspecialchars($patient['phone']) ?></span><?php endif; ?>
        </div>
      </div>
      <div class="flex gap-3">
        <div class="text-center px-4">
          <p class="font-serif text-2xl font-bold text-[#004591]"><?= $summary['total_visits'] ?></p>
          <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Visits</p>
        </div>
        <div class="w-px bg-gray-100"></div>
        <div class="text-center px-4">
          <p class="font-serif text-2xl font-bold text-[#ea741b]"><?= number_format($summary['total_paid']) ?></p>
          <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Total Paid</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabs -->
<div class="max-w-5xl mx-auto px-5 mb-6 no-print">
  <div class="flex gap-1 overflow-x-auto bg-gray-50 rounded-2xl p-1 border border-gray-100">
    <button onclick="showTab('overview')" class="tab-btn tab-active flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-widest whitespace-nowrap transition-all" data-tab="overview"><i class="fas fa-user"></i> Overview</button>
    <button onclick="showTab('prescriptions')" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-[#004591] whitespace-nowrap transition-all" data-tab="prescriptions"><i class="fas fa-pills"></i> Prescriptions <span class="ml-1 w-5 h-5 rounded-full bg-[#004591]/10 text-[#004591] text-[9px] flex items-center justify-center"><?= count($prescriptions) ?></span></button>
    <button onclick="showTab('appointments')" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-[#004591] whitespace-nowrap transition-all" data-tab="appointments"><i class="fas fa-calendar-check"></i> Appointments <span class="ml-1 w-5 h-5 rounded-full bg-[#004591]/10 text-[#004591] text-[9px] flex items-center justify-center"><?= count($appointments) ?></span></button>
    <button onclick="showTab('payments')" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-[#004591] whitespace-nowrap transition-all" data-tab="payments"><i class="fas fa-money-bill-wave"></i> Payments</button>
    <button onclick="showTab('billing')" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-[#004591] whitespace-nowrap transition-all" data-tab="billing"><i class="fas fa-file-invoice-dollar"></i> Billing</button>
  </div>
</div>

<!-- Tab Content -->
<main class="max-w-5xl mx-auto px-5 pb-20">

  <!-- OVERVIEW -->
  <div id="tab-overview" class="tab-content">
    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
        <div class="w-10 h-10 rounded-xl bg-[#004591]/10 flex items-center justify-center mx-auto mb-3"><i class="fas fa-pills text-[#004591]"></i></div>
        <p class="font-serif text-2xl font-bold text-[#004591]"><?= $summary['total_prescriptions'] ?></p>
        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mt-1">Prescriptions</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
        <div class="w-10 h-10 rounded-xl bg-purple-500/10 flex items-center justify-center mx-auto mb-3"><i class="fas fa-calendar-check text-purple-500"></i></div>
        <p class="font-serif text-2xl font-bold text-purple-600"><?= $summary['total_visits'] ?></p>
        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mt-1">Total Visits</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
        <div class="w-10 h-10 rounded-xl bg-[#ea741b]/10 flex items-center justify-center mx-auto mb-3"><i class="fas fa-money-bill-wave text-[#ea741b]"></i></div>
        <p class="font-serif text-2xl font-bold text-[#ea741b]"><?= number_format($summary['total_paid']) ?></p>
        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mt-1">Total Paid (৳)</p>
      </div>
      <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center mx-auto mb-3"><i class="fas fa-clock text-emerald-500"></i></div>
        <p class="font-serif text-2xl font-bold text-emerald-600"><?= $summary['last_visit'] ? date('d M', strtotime($summary['last_visit'])) : '—' ?></p>
        <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 mt-1">Last Visit</p>
      </div>
    </div>

    <!-- Patient Details -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
      <h3 class="font-serif text-lg font-bold text-[#004591] mb-4 flex items-center gap-2"><i class="fas fa-id-card text-[#ea741b] text-sm"></i> Patient Details</h3>
      <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Patient ID</span><span class="font-semibold text-[#004591]"><?= htmlspecialchars($patient['patient_id']) ?></span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Full Name</span><span class="font-semibold"><?= htmlspecialchars($patient['name']) ?></span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Phone</span><span class="font-semibold"><?= htmlspecialchars($patient['phone'] ?? '—') ?></span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Age</span><span class="font-semibold"><?= $patient['age'] ? $patient['age'].' years' : '—' ?></span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Gender</span><span class="font-semibold"><?= htmlspecialchars($patient['gender'] ?? '—') ?></span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Blood Group</span><span class="font-semibold"><?= htmlspecialchars($patient['blood_group'] ?? '—') ?></span></div>
        <?php if(!empty($patient['address'])): ?>
        <div class="flex justify-between py-2 border-b border-gray-50 sm:col-span-2"><span class="text-gray-400">Address</span><span class="font-semibold text-right max-w-xs"><?= htmlspecialchars($patient['address']) ?></span></div>
        <?php endif; ?>
        <?php if(!empty($patient['notes'])): ?>
        <div class="py-2 sm:col-span-2"><span class="text-gray-400 block mb-1">Medical Notes</span><p class="text-gray-600 bg-gray-50 rounded-xl p-3 text-xs leading-relaxed"><?= htmlspecialchars($patient['notes']) ?></p></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Recent Prescription -->
    <?php if(!empty($prescriptions[0])): ?>
    <?php $latest = $prescriptions[0]; ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h3 class="font-serif text-lg font-bold text-[#004591] mb-4 flex items-center gap-2"><i class="fas fa-prescription text-[#ea741b] text-sm"></i> Latest Prescription</h3>
      <div class="flex items-start justify-between mb-3">
        <div>
          <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($latest['rx_date'] ?? $latest['created_at'])) ?></p>
          <p class="text-sm font-semibold text-gray-700 mt-1"><?= htmlspecialchars($latest['doctor_name'] ?? 'Doctor') ?></p>
        </div>
        <a href="print_prescription.php?id=<?=$latest['id']?>&pid=<?=urlencode($patient['patient_id'])?>" target="_blank" class="text-[#ea741b] text-xs font-bold hover:underline"><i class="fas fa-print mr-1"></i> Print</a>
      </div>
      <div class="bg-gray-50 rounded-xl p-4 mb-3">
        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Diagnosis</p>
        <p class="text-sm text-gray-700"><?= htmlspecialchars($latest['diagnosis']) ?></p>
      </div>
      <?php if(!empty($latest['medicines'])): ?>
      <div class="flex flex-wrap gap-2">
        <?php foreach($latest['medicines'] as $med): ?>
        <span class="med-pill"><i class="fas fa-pills text-[#004591] text-[10px]"></i> <?= htmlspecialchars($med['name']) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- PRESCRIPTIONS -->
  <div id="tab-prescriptions" class="tab-content hidden">
    <?php if(empty($prescriptions)): ?>
    <div class="text-center py-16"><i class="fas fa-pills text-gray-200 text-4xl mb-3"></i><p class="text-gray-400 text-sm">No prescriptions yet.</p></div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach($prescriptions as $rx): ?>
      <div class="rx-card">
        <div class="flex items-start justify-between mb-3">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="text-[10px] font-bold uppercase tracking-widest text-[#ea741b] bg-[#ea741b]/10 px-2 py-0.5 rounded-full"><?= date('d M Y', strtotime($rx['rx_date'] ?? $rx['created_at'])) ?></span>
              <?php if($rx['follow_up']): ?><span class="text-[10px] font-bold uppercase tracking-widest text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">Follow-up: <?= htmlspecialchars($rx['follow_up']) ?></span><?php endif; ?>
            </div>
            <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($rx['doctor_name'] ?? 'Doctor') ?> <?= $rx['doctor_degrees'] ? '<span class="text-gray-400 font-normal">('.htmlspecialchars($rx['doctor_degrees']).')</span>' : '' ?></p>
          </div>
          <a href="print_prescription.php?id=<?=$rx['id']?>&pid=<?=urlencode($patient['patient_id'])?>" target="_blank" class="text-[#ea741b] text-xs font-bold hover:underline flex items-center gap-1"><i class="fas fa-print"></i> Print</a>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 mb-3">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Diagnosis</p>
          <p class="text-sm text-gray-700"><?= htmlspecialchars($rx['diagnosis']) ?></p>
        </div>

        <?php if(!empty($rx['investigations'])): ?>
        <div class="bg-blue-50 rounded-xl p-4 mb-3">
          <p class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1">Investigations</p>
          <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($rx['investigations'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if(!empty($rx['medicines'])): ?>
        <div class="mb-3">
          <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Medicines</p>
          <div class="space-y-2">
            <?php foreach($rx['medicines'] as $i => $med): ?>
            <div class="flex items-start gap-3 bg-gray-50 rounded-xl p-3">
              <span class="w-6 h-6 rounded-full bg-[#004591]/10 text-[#004591] text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5"><?= $i+1 ?></span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($med['name']) ?></p>
                <div class="flex flex-wrap gap-2 mt-1">
                  <?php if(!empty($med['frequency'])): ?><span class="text-[10px] text-gray-500"><i class="fas fa-clock text-[8px] mr-1"></i><?= htmlspecialchars(str_replace('+', ' + ', $med['frequency'])) ?></span><?php endif; ?>
                  <?php if(!empty($med['duration'])): ?><span class="text-[10px] text-gray-500"><i class="fas fa-calendar text-[8px] mr-1"></i><?= htmlspecialchars($med['duration']) ?></span><?php endif; ?>
                  <?php if(!empty($med['note'])): ?><span class="text-[10px] text-[#ea741b]"><i class="fas fa-info-circle text-[8px] mr-1"></i><?= htmlspecialchars($med['note']) ?></span><?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($rx['advice'])): ?>
        <div class="bg-amber-50 rounded-xl p-4">
          <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-1">Advice</p>
          <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($rx['advice'])) ?></p>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- APPOINTMENTS -->
  <div id="tab-appointments" class="tab-content hidden">
    <?php if(empty($appointments)): ?>
    <div class="text-center py-16"><i class="fas fa-calendar-check text-gray-200 text-4xl mb-3"></i><p class="text-gray-400 text-sm">No appointments yet.</p></div>
    <?php else: ?>
    <div class="space-y-0">
      <?php foreach($appointments as $apt):
        $status = strtolower($apt['status'] ?? 'waiting');
        $statusClass = match($status) {
          'completed' => 'status-completed',
          'waiting' => 'status-waiting',
          'in treatment' => 'status-in-treatment',
          'follow-up', 'follow up' => 'status-follow-up',
          'cancelled' => 'status-cancelled',
          default => 'status-default'
        };
      ?>
      <div class="flex gap-4 pb-6">
        <div class="flex flex-col items-center">
          <div class="timeline-dot" style="background:<?= $status === 'completed' ? '#166534' : ($status === 'cancelled' ? '#991b1b' : '#004591') ?>"></div>
          <?php if(!$loop->last): ?><div class="timeline-line flex-1"></div><?php endif; ?>
        </div>
        <div class="flex-1 bg-white rounded-2xl border border-gray-100 p-5 -mt-1">
          <div class="flex items-start justify-between mb-2">
            <div>
              <p class="text-xs font-bold text-gray-400"><?= date('l, d M Y', strtotime($apt['appointment_date'])) ?></p>
              <?php if($apt['appointment_time']): ?><p class="text-sm font-semibold text-gray-700 mt-1"><i class="fas fa-clock text-gray-300 mr-1"></i> <?= date('h:i A', strtotime($apt['appointment_time'])) ?></p><?php endif; ?>
            </div>
            <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($apt['status'] ?? 'Waiting') ?></span>
          </div>
          <?php if($apt['doctor_name']): ?><p class="text-xs text-gray-500 mt-2"><i class="fas fa-user-doctor mr-1"></i> <?= htmlspecialchars($apt['doctor_name']) ?></p><?php endif; ?>
          <?php if($apt['description']): ?><p class="text-sm text-gray-600 mt-2 bg-gray-50 rounded-xl p-3"><?= htmlspecialchars($apt['description']) ?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- PAYMENTS -->
  <div id="tab-payments" class="tab-content hidden">
    <?php if(empty($payments)): ?>
    <div class="text-center py-16"><i class="fas fa-money-bill-wave text-gray-200 text-4xl mb-3"></i><p class="text-gray-400 text-sm">No payments recorded yet.</p></div>
    <?php else: ?>
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
      <div class="p-5 border-b border-gray-50 flex items-center justify-between">
        <h3 class="font-serif text-lg font-bold text-[#004591]">Payment History</h3>
        <div class="text-right">
          <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Total Paid</p>
          <p class="font-serif text-xl font-bold text-[#ea741b]">৳ <?= number_format($summary['total_paid']) ?></p>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="bg-gray-50 text-left"><th class="px-5 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Date</th><th class="px-5 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Amount</th><th class="px-5 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Method</th><th class="px-5 py-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Notes</th></tr></thead>
          <tbody>
            <?php foreach($payments as $pay): ?>
            <tr class="border-b border-gray-50 hover:bg-gray-50/50">
              <td class="px-5 py-3 text-gray-600"><?= date('d M Y', strtotime($pay['created_at'])) ?></td>
              <td class="px-5 py-3 font-semibold text-[#004591]">৳ <?= number_format($pay['amount']) ?></td>
              <td class="px-5 py-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 rounded-full text-[10px] font-bold text-gray-600"><?= htmlspecialchars($pay['payment_method'] ?? 'Cash') ?></span></td>
              <td class="px-5 py-3 text-gray-400 text-xs"><?= htmlspecialchars($pay['notes'] ?? '—') ?></td>
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
    <div class="text-center py-16"><i class="fas fa-file-invoice-dollar text-gray-200 text-4xl mb-3"></i><p class="text-gray-400 text-sm">No billing records yet.</p></div>
    <?php else: ?>
    <div class="space-y-4">
      <?php foreach($cashMemos as $memo): ?>
      <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-50 flex items-start justify-between">
          <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#ea741b] mb-1"><?= htmlspecialchars($memo['memo_number'] ?? 'Cash Memo') ?></p>
            <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($memo['memo_date'] ?? $memo['created_at'])) ?></p>
          </div>
          <div class="text-right">
            <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">Grand Total</p>
            <p class="font-serif text-xl font-bold text-[#004591]">৳ <?= number_format($memo['grand_total'] ?? 0, 2) ?></p>
          </div>
        </div>
        <?php if(!empty($memo['items'])): ?>
        <div class="p-5">
          <table class="w-full text-sm">
            <thead><tr class="text-left"><th class="pb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400">Item</th><th class="pb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 text-center">Qty</th><th class="pb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 text-right">Price</th><th class="pb-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 text-right">Total</th></tr></thead>
            <tbody>
              <?php foreach($memo['items'] as $item): ?>
              <tr class="border-t border-gray-50">
                <td class="py-2 text-gray-600"><?= htmlspecialchars($item['description']) ?></td>
                <td class="py-2 text-center text-gray-500"><?= $item['quantity'] ?></td>
                <td class="py-2 text-right text-gray-500">৳ <?= number_format($item['unit_price'], 2) ?></td>
                <td class="py-2 text-right font-semibold text-gray-700">৳ <?= number_format($item['total'], 2) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div class="mt-3 pt-3 border-t border-gray-100 space-y-1 text-sm text-right">
            <p class="text-gray-400">Subtotal: ৳ <?= number_format($memo['subtotal'] ?? 0, 2) ?></p>
            <?php if(($memo['discount'] ?? 0) > 0): ?><p class="text-emerald-500">Discount: -৳ <?= number_format($memo['discount'], 2) ?></p><?php endif; ?>
            <p class="font-bold text-[#004591]">Grand Total: ৳ <?= number_format($memo['grand_total'] ?? 0, 2) ?></p>
          </div>
          <div class="mt-3 flex items-center justify-between text-xs text-gray-400">
            <span><i class="fas fa-credit-card mr-1"></i> <?= htmlspecialchars($memo['payment_method'] ?? 'Cash') ?></span>
            <a href="print_cash_memo.php?id=<?=$memo['id']?>" target="_blank" class="text-[#ea741b] font-bold hover:underline"><i class="fas fa-print mr-1"></i> Print Memo</a>
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
<footer class="bg-[#000e22] py-6 no-print">
  <div class="max-w-5xl mx-auto px-5 text-center">
    <p class="text-white/20 text-xs">&copy; <?= date('Y') ?> Mamun's Ortho Dental | Lalmatia, Dhaka-1207</p>
  </div>
</footer>

<script>
function showTab(tab) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
  document.querySelectorAll('.tab-btn').forEach(el => { el.classList.remove('tab-active'); el.classList.add('text-gray-400'); });

  document.getElementById('tab-' + tab).classList.remove('hidden');
  const activeBtn = document.querySelector('[data-tab="' + tab + '"]');
  activeBtn.classList.add('tab-active');
  activeBtn.classList.remove('text-gray-400');
}
</script>
</body>
</html>
