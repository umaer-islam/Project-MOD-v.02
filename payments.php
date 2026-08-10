<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
restrict_access(['admin', 'doctor', 'receptionist']);
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

// Filters (shared logic, but mainly applies to Payments currently)
$filterDate   = $_GET['date'] ?? '';
$filterMethod = $_GET['method'] ?? '';

// Determine active tab
$activeTab = $_GET['tab'] ?? 'payments'; // 'payments' or 'memos'

// 1. Fetch Patient Payments
try {
    $where = [];
    $params = [];
    if ($filterDate) { $where[] = "DATE(py.created_at) = ?"; $params[] = $filterDate; }
    if ($filterMethod) { $where[] = "py.payment_method = ?"; $params[] = $filterMethod; }
    $whereSQL = $where ? 'AND ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("SELECT py.*, p.name as patient_name, p.patient_id as p_id, p.phone as patient_phone FROM payments py JOIN patients p ON py.patient_id = p.id WHERE 1=1 $whereSQL ORDER BY py.created_at DESC LIMIT 100");
    $stmt->execute($params);
    $payments = $stmt->fetchAll();

    $patientsStmt = $pdo->query("SELECT id, name, patient_id FROM patients ORDER BY name ASC");
    $allPatients = $patientsStmt->fetchAll();

    $todayStmt = $pdo->query("SELECT COALESCE(SUM(amount),0) as today_rev, COUNT(*) as today_count FROM payments WHERE DATE(created_at) = CURDATE()");
    $todayData = $todayStmt->fetch();
} catch (PDOException $e) {
    $payments = []; $allPatients = []; $todayData = ['today_rev'=>0,'today_count'=>0];
    $error_msg = "Error fetching payments.";
}
$totalRevenue = array_sum(array_column($payments, 'amount'));

// Auto-create Cash Memos tables if missing
try {
    $pdo->query("SELECT 1 FROM cash_memos LIMIT 1");
} catch (PDOException $e) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS cash_memos (
            id INT AUTO_INCREMENT PRIMARY KEY, memo_number VARCHAR(30) UNIQUE NOT NULL,
            customer_name VARCHAR(255) NOT NULL, customer_phone VARCHAR(30) DEFAULT NULL,
            customer_address TEXT DEFAULT NULL, memo_date DATE NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00, discount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            grand_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            payment_method ENUM('Cash','Bkash','Nagad','Card') NOT NULL DEFAULT 'Cash',
            notes TEXT, created_by INT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS cash_memo_items (
            id INT AUTO_INCREMENT PRIMARY KEY, memo_id INT NOT NULL,
            description VARCHAR(255) NOT NULL, quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
            unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00, total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            FOREIGN KEY (memo_id) REFERENCES cash_memos(id) ON DELETE CASCADE
        )");
    } catch (Exception $ex) {}
}

// 2. Fetch Cash Memos
try {
    $memos = $pdo->query("SELECT cm.*, u.name as created_by_name FROM cash_memos cm LEFT JOIN users u ON cm.created_by = u.id ORDER BY cm.created_at DESC LIMIT 100")->fetchAll();
    $todayMemoStmt = $pdo->query("SELECT COALESCE(SUM(grand_total),0) as t, COUNT(*) as c FROM cash_memos WHERE DATE(created_at) = CURDATE()");
    $todayMemoData = $todayMemoStmt->fetch();
} catch (PDOException $e) {
    $memos = []; $todayMemoData = ['t'=>0,'c'=>0];
}
$totalMemoRevenue = array_sum(array_column($memos, 'grand_total'));

// For edit modal - fetch memo + items if ?edit=ID
$editMemo = null; $editItems = [];
if (!empty($_GET['edit'])) {
    $activeTab = 'memos'; // force switch to memos tab if editing
    try {
        $eStmt = $pdo->prepare("SELECT * FROM cash_memos WHERE id = ?");
        $eStmt->execute([(int)$_GET['edit']]);
        $editMemo = $eStmt->fetch();
        if ($editMemo) {
            $eiStmt = $pdo->prepare("SELECT * FROM cash_memo_items WHERE memo_id = ? ORDER BY id");
            $eiStmt->execute([$editMemo['id']]);
            $editItems = $eiStmt->fetchAll();
        }
    } catch (Exception $e) {}
}

// Redirect helpers for the API endpoints
if (isset($_GET['success']) && strpos($_GET['success'], 'Cash memo') !== false) {
    $activeTab = 'memos';
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <!-- Header -->
    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Finance</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Billing & Payments</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Manage patient payments and custom cash memos</p>
        </div>
        <div class="flex gap-2">
            <button onclick="document.getElementById('addPaymentModal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                <i class="fas fa-file-medical text-xs"></i> Add Patient Payment
            </button>
            <button onclick="openMemoModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-[#004591] border border-[#004591]/20 hover:border-[#ea741b] hover:text-[#ea741b] text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-sm transition-all duration-300">
                <i class="fas fa-receipt text-xs"></i> New Custom Memo
            </button>
        </div>
    </div>

    <?php if ($success_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="successAlert">
        <i class="fas fa-check-circle text-green-500"></i> <?= $success_msg ?>
        <button onclick="document.getElementById('successAlert').remove()" class="ml-auto text-green-400 hover:text-green-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
        <button onclick="document.getElementById('errorAlert').remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>

    <!-- Tabs UI -->
    <div class="flex border-b border-gray-200 mb-6">
        <button onclick="switchTab('payments')" id="btn-tab-payments" class="pb-3 px-4 font-bold text-sm tracking-wide transition-all <?= $activeTab === 'payments' ? 'border-b-2 border-[#ea741b] text-[#004591]' : 'text-gray-400 hover:text-gray-600' ?>">
            <i class="fas fa-user-injured mr-1"></i> Patient Payments
        </button>
        <button onclick="switchTab('memos')" id="btn-tab-memos" class="pb-3 px-4 font-bold text-sm tracking-wide transition-all <?= $activeTab === 'memos' ? 'border-b-2 border-[#ea741b] text-[#004591]' : 'text-gray-400 hover:text-gray-600' ?>">
            <i class="fas fa-receipt mr-1"></i> Custom Cash Memos
        </button>
    </div>

    <!-- ==================== TAB 1: PATIENT PAYMENTS ==================== -->
    <div id="tab-payments" class="<?= $activeTab === 'payments' ? '' : 'hidden' ?>">
        
        <!-- Revenue Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-[#ea741b]/10 flex items-center justify-center"><i class="fas fa-coins text-[#ea741b] text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Total Shown</p>
                </div>
                <p class="font-serif text-xl md:text-2xl font-bold text-[#004591]"><span class="text-sm">৳</span><?= number_format($totalRevenue, 2) ?></p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-green-50 flex items-center justify-center"><i class="fas fa-receipt text-green-600 text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Transactions</p>
                </div>
                <p class="font-serif text-xl md:text-2xl font-bold text-[#004591]"><?= count($payments) ?></p>
            </div>
            <div class="bg-gradient-to-br from-[#ea741b] to-[#cf5e0e] rounded-2xl p-5 shadow-[0_4px_20px_rgba(234,116,27,0.15)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><i class="fas fa-calendar-day text-white text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-white/60">Today</p>
                </div>
                <p class="font-serif text-xl md:text-2xl font-bold text-white"><span class="text-sm">৳</span><?= number_format($todayData['today_rev'], 2) ?></p>
                <p class="text-[10px] text-white/50 font-semibold mt-0.5"><?= $todayData['today_count'] ?> payments today</p>
            </div>
            <div class="bg-[#004591] rounded-2xl p-5 shadow-[0_4px_20px_rgba(0,69,145,0.15)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><i class="fas fa-chart-line text-white text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-white/60">Average</p>
                </div>
                <p class="font-serif text-xl md:text-2xl font-bold text-white"><span class="text-sm">৳</span><?= count($payments) > 0 ? number_format($totalRevenue / count($payments), 2) : '0.00' ?></p>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <input type="hidden" name="tab" value="payments">
            <div>
                <div class="mod-calendar" data-placeholder="All Dates">
                    <input type="hidden" name="date" value="<?= htmlspecialchars($filterDate) ?>">
                    <div class="mod-calendar-trigger" style="min-height:36px">
                        <span class="mod-calendar-label" style="font-size:13px">Date</span>
                        <div class="mod-calendar-value">
                            <i class="fas fa-calendar-day mod-calendar-icon text-xs"></i>
                            <span class="mod-calendar-text" style="font-size:13px"><?= $filterDate ? date('d M Y', strtotime($filterDate)) : 'All Dates' ?></span>
                            <span class="mod-calendar-clear"><i class="fas fa-times text-[8px]"></i></span>
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
            <div>
                <label class="block text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-1">Method</label>
                <div class="mod-dropdown mod-dropdown--filter" data-name="method" data-placeholder="All Methods">
                    <input type="hidden" name="method" value="<?= htmlspecialchars($filterMethod) ?>">
                    <div class="mod-dropdown-trigger">
                        <span class="mod-dropdown-selected"><?= $filterMethod ? htmlspecialchars($filterMethod) : 'All Methods' ?></span>
                        <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                    </div>
                    <div class="mod-dropdown-panel">
                        <div class="mod-dropdown-option <?= $filterMethod===''?'is-selected':'' ?>" data-value=""><span class="opt-check"></span><span>All Methods</span></div>
                        <div class="mod-dropdown-option <?= $filterMethod==='Cash'?'is-selected':'' ?>" data-value="Cash"><span class="opt-check"></span><span>Cash</span></div>
                        <div class="mod-dropdown-option <?= $filterMethod==='Bkash'?'is-selected':'' ?>" data-value="Bkash"><span class="opt-check"></span><span>Bkash</span></div>
                        <div class="mod-dropdown-option <?= $filterMethod==='Nagad'?'is-selected':'' ?>" data-value="Nagad"><span class="opt-check"></span><span>Nagad</span></div>
                        <div class="mod-dropdown-option <?= $filterMethod==='Card'?'is-selected':'' ?>" data-value="Card"><span class="opt-check"></span><span>Card</span></div>
                    </div>
                </div>
            </div>
            <button type="submit" class="px-5 py-2 bg-[#004591] hover:bg-[#ea741b] text-white text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all"><i class="fas fa-filter mr-1"></i> Filter</button>
            <?php if ($filterDate || $filterMethod): ?>
            <a href="payments.php?tab=payments" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-[10px] font-bold uppercase tracking-widest rounded-xl transition-all"><i class="fas fa-times mr-1"></i> Clear</a>
            <?php endif; ?>
        </form>

        <div class="admin-card bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Date</th>
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Patient</th>
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Amount</th>
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Method</th>
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (count($payments) > 0): ?>
                            <?php foreach($payments as $py):
                                $mc = 'bg-gray-100 text-gray-600';
                                if($py['payment_method'] === 'Cash') $mc = 'bg-green-50 text-green-700';
                                if($py['payment_method'] === 'Bkash') $mc = 'bg-pink-50 text-pink-700';
                                if($py['payment_method'] === 'Nagad') $mc = 'bg-orange-50 text-orange-700';
                                if($py['payment_method'] === 'Card') $mc = 'bg-blue-50 text-blue-700';
                            ?>
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 text-[#7c7c7c] whitespace-nowrap"><?= date('M d, Y', strtotime($py['created_at'])) ?><br><span class="text-[10px]"><?= date('h:i A', strtotime($py['created_at'])) ?></span></td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-[#e8f0fa] flex items-center justify-center text-[#004591] text-sm font-bold flex-shrink-0">
                                            <?= strtoupper(substr($py['patient_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-[#004591]"><?= htmlspecialchars($py['patient_name']) ?></p>
                                            <p class="text-[10px] text-[#7c7c7c]"><?= htmlspecialchars($py['p_id']) ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <p class="font-bold text-[#004591] text-base"><span class="text-xs mr-0.5 font-semibold text-[#7c7c7c]">৳</span><?= number_format($py['amount'], 2) ?></p>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span class="text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest <?= $mc ?>"><?= htmlspecialchars($py['payment_method']) ?></span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="print_bill.php?id=<?= $py['id'] ?>" target="_blank"
                                           class="w-8 h-8 rounded-lg bg-[#ea741b]/10 hover:bg-[#ea741b] hover:text-white flex items-center justify-center text-[#ea741b] transition-all" title="Print Bill">
                                            <i class="fas fa-print text-xs"></i>
                                        </a>
                                        <button onclick="this.closest('tr').querySelector('.payment-notes-row')?.classList.toggle('hidden')"
                                                class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#004591] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="View Notes">
                                            <i class="fas fa-file-invoice text-xs"></i>
                                        </button>
                                        <form method="POST" action="api/delete_payment.php" onsubmit="return confirm('Delete this payment record?')" style="display:inline">
                                            <input type="hidden" name="id" value="<?= $py['id'] ?>">
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-red-500 hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Delete">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4">
                                        <i class="fas fa-wallet text-[#7c7c7c] text-xl md:text-2xl"></i>
                                    </div>
                                    <p class="text-[#7c7c7c] font-semibold">No payments recorded yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- ==================== TAB 2: CUSTOM CASH MEMOS ==================== -->
    <div id="tab-memos" class="<?= $activeTab === 'memos' ? '' : 'hidden' ?>">
        
        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-[#ea741b]/10 flex items-center justify-center"><i class="fas fa-file-invoice text-[#ea741b] text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">All Custom Memos</p>
                </div>
                <p class="font-serif text-xl font-bold text-[#004591]"><?= count($memos) ?></p>
            </div>
            <div class="bg-gradient-to-br from-[#ea741b] to-[#cf5e0e] rounded-2xl p-5 shadow-[0_4px_20px_rgba(234,116,27,0.15)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><i class="fas fa-calendar-day text-white text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-white/60">Today</p>
                </div>
                <p class="font-serif text-xl font-bold text-white"><span class="text-sm">৳</span><?= number_format($todayMemoData['t'], 2) ?></p>
                <p class="text-[10px] text-white/50 font-semibold mt-0.5"><?= $todayMemoData['c'] ?> memos today</p>
            </div>
            <div class="bg-[#004591] rounded-2xl p-5 shadow-[0_4px_20px_rgba(0,69,145,0.15)]">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><i class="fas fa-coins text-white text-sm"></i></div>
                    <p class="text-[9px] font-bold uppercase tracking-widest text-white/60">Total Value</p>
                </div>
                <p class="font-serif text-xl font-bold text-white"><span class="text-sm">৳</span><?= number_format($totalMemoRevenue, 2) ?></p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead><tr class="bg-[#F8FAFC]">
                        <th class="whitespace-nowrap px-5 py-3 text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Memo #</th>
                        <th class="whitespace-nowrap px-5 py-3 text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Date</th>
                        <th class="whitespace-nowrap px-5 py-3 text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Customer</th>
                        <th class="whitespace-nowrap px-5 py-3 text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Total</th>
                        <th class="whitespace-nowrap px-5 py-3 text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">Method</th>
                        <th class="whitespace-nowrap px-5 py-3 text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Actions</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-50">
                    <?php if (count($memos) > 0): ?>
                        <?php foreach($memos as $m):
                            $mc = 'bg-gray-100 text-gray-600';
                            if($m['payment_method'] === 'Cash') $mc = 'bg-green-50 text-green-700';
                            if($m['payment_method'] === 'Bkash') $mc = 'bg-pink-50 text-pink-700';
                            if($m['payment_method'] === 'Nagad') $mc = 'bg-orange-50 text-orange-700';
                            if($m['payment_method'] === 'Card') $mc = 'bg-blue-50 text-blue-700';
                        ?>
                        <tr class="hover:bg-[#F8FAFC] transition-colors">
                            <td class="whitespace-nowrap px-5 py-4"><span class="font-bold text-[#004591] text-xs bg-[#e8f0fa] px-2.5 py-1 rounded-lg"><?= htmlspecialchars($m['memo_number']) ?></span></td>
                            <td class="whitespace-nowrap px-5 py-4 text-[#7c7c7c]"><?= date('d M Y', strtotime($m['memo_date'])) ?></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-[#004591]"><?= htmlspecialchars($m['customer_name']) ?></p>
                                <?php if($m['customer_phone']): ?><p class="text-[10px] text-[#7c7c7c]"><?= htmlspecialchars($m['customer_phone']) ?></p><?php endif; ?>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4"><p class="font-bold text-[#004591] text-base"><span class="text-xs mr-0.5 font-semibold text-[#7c7c7c]">৳</span><?= number_format($m['grand_total'], 2) ?></p></td>
                            <td class="whitespace-nowrap px-5 py-4"><span class="text-[9px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest <?= $mc ?>"><?= htmlspecialchars($m['payment_method']) ?></span></td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="print_cash_memo.php?id=<?= $m['id'] ?>" target="_blank" class="w-8 h-8 rounded-lg bg-[#ea741b]/10 hover:bg-[#ea741b] hover:text-white flex items-center justify-center text-[#ea741b] transition-all" title="Print"><i class="fas fa-print text-xs"></i></a>
                                    <a href="payments.php?tab=memos&edit=<?= $m['id'] ?>" class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#004591] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Edit"><i class="fas fa-pen text-xs"></i></a>
                                    <form method="POST" action="api/delete_cash_memo.php" onsubmit="return confirm('Delete this cash memo permanently?')" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-red-500 hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 rounded-full bg-[#F4F7FC] flex items-center justify-center mx-auto mb-4"><i class="fas fa-file-invoice text-[#7c7c7c] text-2xl"></i></div>
                            <p class="text-[#7c7c7c] font-semibold">No custom cash memos created yet.</p>
                            <button onclick="openMemoModal()" class="mt-4 px-5 py-2 bg-[#004591] text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-[#ea741b] transition-all">Create First Memo</button>
                        </td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>


<!-- ═══ ADD PAYMENT MODAL (Patient) ═══ -->
<div id="addPaymentModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm bg-[#004591]/20">
    <div class="relative w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">New Entry</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold">Record Payment</h3>
                </div>
                <button onclick="document.getElementById('addPaymentModal').classList.add('hidden')"
                        class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
            <form action="api/add_payment.php" method="POST" class="p-6 space-y-4">
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Select Patient *</label>
                    <div class="mod-dropdown" data-name="patient_id" data-placeholder="-- Choose Patient --">
                        <input type="hidden" name="patient_id" value="" required>
                        <div class="mod-dropdown-trigger">
                            <span class="mod-dropdown-selected">-- Choose Patient --</span>
                            <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                        </div>
                        <div class="mod-dropdown-panel">
                            <div class="mod-dropdown-option" data-value=""><span class="opt-check"></span><span>-- Choose Patient --</span></div>
                            <?php foreach($allPatients as $p): ?>
                            <div class="mod-dropdown-option" data-value="<?= $p['id'] ?>"><span class="opt-check"></span><span><?= htmlspecialchars($p['name']) ?> (<?= $p['patient_id'] ?>)</span></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Amount (BDT) *</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-[#004591] outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Payment Method *</label>
                        <div class="mod-dropdown" data-name="payment_method" data-placeholder="Select Method">
                            <input type="hidden" name="payment_method" value="Cash" required>
                            <div class="mod-dropdown-trigger">
                                <span class="mod-dropdown-selected">Cash</span>
                                <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                            </div>
                            <div class="mod-dropdown-panel">
                                <div class="mod-dropdown-option is-selected" data-value="Cash"><span class="opt-check"></span><span>Cash</span></div>
                                <div class="mod-dropdown-option" data-value="Bkash"><span class="opt-check"></span><span>Bkash</span></div>
                                <div class="mod-dropdown-option" data-value="Nagad"><span class="opt-check"></span><span>Nagad</span></div>
                                <div class="mod-dropdown-option" data-value="Card"><span class="opt-check"></span><span>Card</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Notes / Reference</label>
                    <input type="text" name="notes" placeholder="Optional reference or note" class="w-full border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-[#004591] outline-none">
                </div>
                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit" class="flex-1 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300">
                        <i class="fas fa-check mr-2"></i> Save Payment
                    </button>
                    <button type="button" onclick="document.getElementById('addPaymentModal').classList.add('hidden')" class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══ CREATE / EDIT CASH MEMO MODAL ═══ -->
<div id="memoModal" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 pt-8 backdrop-blur-sm bg-[#004591]/20 overflow-y-auto">
    <div class="relative w-full max-w-2xl mb-8">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5" id="memoModalLabel">New</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold" id="memoModalTitle">Create Custom Cash Memo</h3>
                </div>
                <button onclick="closeMemoModal()" class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all"><i class="fas fa-times text-sm"></i></button>
            </div>
            <!-- NOTE: Using save_cash_memo.php to handle save, API redirects to cash_memos.php inside... we'll fix the redirect inside the API next! -->
            <form action="api/save_cash_memo.php" method="POST" class="p-6 space-y-5" id="memoForm">
                <input type="hidden" name="memo_id" id="memo_id" value="0">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Customer Name *</label>
                        <input type="text" name="customer_name" id="f_name" required placeholder="Full name" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#004591] focus:ring-1 focus:ring-[#004591]/20 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Phone</label>
                        <input type="text" name="customer_phone" id="f_phone" placeholder="01XXXXXXXXX" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#004591] focus:ring-1 focus:ring-[#004591]/20 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Address</label>
                    <input type="text" name="customer_address" id="f_address" placeholder="Optional address" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#004591] focus:ring-1 focus:ring-[#004591]/20 outline-none transition">
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="mod-calendar" id="f_date" data-placeholder="Select date">
                            <input type="hidden" name="memo_date" value="<?= date('Y-m-d') ?>" required>
                            <div class="mod-calendar-trigger">
                                <span class="mod-calendar-label">Date *</span>
                                <div class="mod-calendar-value">
                                    <i class="fas fa-calendar-day mod-calendar-icon text-sm"></i>
                                    <span class="mod-calendar-text"></span>
                                    <span class="mod-calendar-clear"><i class="fas fa-times text-[8px]"></i></span>
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
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Payment Method</label>
                        <div class="mod-dropdown" id="f_method" data-name="payment_method" data-placeholder="Select Method">
                            <input type="hidden" name="payment_method" value="Cash">
                            <div class="mod-dropdown-trigger">
                                <span class="mod-dropdown-selected">Cash</span>
                                <svg class="mod-dropdown-chevron" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6l4 4 4-4"/></svg>
                            </div>
                            <div class="mod-dropdown-panel">
                                <div class="mod-dropdown-option is-selected" data-value="Cash"><span class="opt-check"></span><span>Cash</span></div>
                                <div class="mod-dropdown-option" data-value="Bkash"><span class="opt-check"></span><span>Bkash</span></div>
                                <div class="mod-dropdown-option" data-value="Nagad"><span class="opt-check"></span><span>Nagad</span></div>
                                <div class="mod-dropdown-option" data-value="Card"><span class="opt-check"></span><span>Card</span></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Discount (৳)</label>
                        <input type="number" step="0.01" name="discount" id="f_discount" value="0" min="0" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#004591] outline-none transition" oninput="calcTotal()">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-[#004591]"><i class="fas fa-list-ul mr-1 text-[#ea741b]"></i> Line Items</label>
                        <button type="button" onclick="addItemRow()" class="text-[10px] font-bold uppercase tracking-widest text-[#ea741b] hover:text-[#004591] transition"><i class="fas fa-plus mr-1"></i> Add Item</button>
                    </div>
                    <div class="bg-[#F8FAFC] rounded-xl p-3 border border-gray-100">
                        <div class="grid grid-cols-[1fr_70px_90px_80px_36px] gap-2 mb-2 px-1">
                            <span class="text-[8px] font-bold uppercase tracking-widest text-[#7c7c7c]">Description</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-[#7c7c7c]">Qty</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-[#7c7c7c]">Price</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-[#7c7c7c]">Total</span>
                            <span></span>
                        </div>
                        <div id="itemsContainer"></div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <div class="w-56 space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-[#7c7c7c]">Subtotal</span><span class="font-bold text-[#004591]" id="dispSubtotal">৳0.00</span></div>
                        <div class="flex justify-between"><span class="text-[#7c7c7c]">Discount</span><span class="font-bold text-green-600" id="dispDiscount">- ৳0.00</span></div>
                        <div class="flex justify-between pt-2 border-t border-gray-200"><span class="font-bold text-[#004591]">Grand Total</span><span class="font-serif text-lg font-bold text-[#ea741b]" id="dispGrand">৳0.00</span></div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Notes</label>
                    <input type="text" name="notes" id="f_notes" placeholder="Optional notes" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#004591] outline-none transition">
                </div>

                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit" class="flex-1 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300"><i class="fas fa-check mr-2"></i> <span id="submitBtnText">Save Memo</span></button>
                    <button type="button" onclick="closeMemoModal()" class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'components/footer.php'; ?>

<script>
// ─── TABS ───
function switchTab(tabId) {
    document.getElementById('tab-payments').classList.add('hidden');
    document.getElementById('tab-memos').classList.add('hidden');
    document.getElementById('btn-tab-payments').className = 'pb-3 px-4 font-bold text-sm tracking-wide transition-all text-gray-400 hover:text-gray-600';
    document.getElementById('btn-tab-memos').className = 'pb-3 px-4 font-bold text-sm tracking-wide transition-all text-gray-400 hover:text-gray-600';
    
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    document.getElementById('btn-tab-' + tabId).className = 'pb-3 px-4 font-bold text-sm tracking-wide transition-all border-b-2 border-[#ea741b] text-[#004591]';
    // update URL without reload
    window.history.replaceState(null, '', '?tab=' + tabId);
}

// ─── CASH MEMO ITEM ROWS ───
let itemIdx = 0;
function addItemRow(desc='', qty=1, price=0) {
    const c = document.getElementById('itemsContainer');
    const row = document.createElement('div');
    row.className = 'grid grid-cols-[1fr_70px_90px_80px_36px] gap-2 mb-2 items-center item-row';
    row.innerHTML = `
        <input type="text" name="item_desc[]" value="${esc(desc)}" placeholder="Service / Item" required class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-[#004591] outline-none">
        <input type="number" name="item_qty[]" value="${qty}" min="0.01" step="0.01" required class="border border-gray-200 rounded-lg px-2 py-2 text-sm text-center focus:border-[#004591] outline-none" oninput="calcTotal()">
        <input type="number" name="item_price[]" value="${price}" min="0" step="0.01" required class="border border-gray-200 rounded-lg px-2 py-2 text-sm text-center focus:border-[#004591] outline-none" oninput="calcTotal()">
        <span class="text-sm font-bold text-[#004591] text-center line-total">৳0.00</span>
        <button type="button" onclick="this.closest('.item-row').remove();calcTotal()" class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-500 text-red-400 hover:text-white flex items-center justify-center transition-all"><i class="fas fa-times text-xs"></i></button>
    `;
    c.appendChild(row);
    calcTotal();
}
function esc(s) { const d=document.createElement('div'); d.textContent=s; return d.innerHTML.replace(/"/g,'&quot;'); }

function calcTotal() {
    let sub = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const q = parseFloat(row.querySelector('[name="item_qty[]"]').value) || 0;
        const p = parseFloat(row.querySelector('[name="item_price[]"]').value) || 0;
        const t = q * p;
        sub += t;
        row.querySelector('.line-total').textContent = '৳' + t.toFixed(2);
    });
    const disc = parseFloat(document.getElementById('f_discount').value) || 0;
    const grand = Math.max(0, sub - disc);
    document.getElementById('dispSubtotal').textContent = '৳' + sub.toFixed(2);
    document.getElementById('dispDiscount').textContent = '- ৳' + disc.toFixed(2);
    document.getElementById('dispGrand').textContent = '৳' + grand.toFixed(2);
}

// ─── MODALS ───
function openMemoModal() {
    document.getElementById('memo_id').value = 0;
    document.getElementById('memoModalLabel').textContent = 'New';
    document.getElementById('memoModalTitle').textContent = 'Create Custom Cash Memo';
    document.getElementById('submitBtnText').textContent = 'Save Memo';
    document.getElementById('memoForm').reset();
    document.getElementById('f_date').querySelector('input[type="hidden"]').value = new Date().toISOString().split('T')[0];
    document.getElementById('f_discount').value = 0;
    document.getElementById('itemsContainer').innerHTML = '';
    addItemRow();
    document.getElementById('memoModal').classList.remove('hidden');
}
function closeMemoModal() { document.getElementById('memoModal').classList.add('hidden'); }
document.getElementById('memoModal')?.addEventListener('click', e => { if(e.target.id === 'memoModal') closeMemoModal(); });

// ─── INIT ───
document.addEventListener('DOMContentLoaded', () => {
    // Dismiss alerts
    setTimeout(() => {
        ['successAlert','errorAlert'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = 0; setTimeout(() => el.remove(), 500); }
        });
    }, 5000);

    // Edit memo mode
    <?php if ($editMemo): ?>
    document.getElementById('memo_id').value = <?= $editMemo['id'] ?>;
    document.getElementById('memoModalLabel').textContent = 'Edit';
    document.getElementById('memoModalTitle').textContent = 'Edit Memo — <?= htmlspecialchars($editMemo['memo_number']) ?>';
    document.getElementById('submitBtnText').textContent = 'Update Memo';
    document.getElementById('f_name').value = <?= json_encode($editMemo['customer_name']) ?>;
    document.getElementById('f_phone').value = <?= json_encode($editMemo['customer_phone'] ?? '') ?>;
    document.getElementById('f_address').value = <?= json_encode($editMemo['customer_address'] ?? '') ?>;
    setModCalendar('f_date', <?= json_encode($editMemo['memo_date']) ?>);
    setModDropdown('f_method', <?= json_encode($editMemo['payment_method']) ?>);
    document.getElementById('f_discount').value = <?= $editMemo['discount'] ?>;
    document.getElementById('f_notes').value = <?= json_encode($editMemo['notes'] ?? '') ?>;
    document.getElementById('itemsContainer').innerHTML = '';
    <?php foreach ($editItems as $item): ?>
    addItemRow(<?= json_encode($item['description']) ?>, <?= $item['quantity'] ?>, <?= $item['unit_price'] ?>);
    <?php endforeach; ?>
    document.getElementById('memoModal').classList.remove('hidden');
    <?php endif; ?>
});
</script>
