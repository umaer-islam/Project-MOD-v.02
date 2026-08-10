<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
restrict_access(['admin', 'doctor', 'receptionist']);
require_once 'database/connection.php';

$success_msg = htmlspecialchars($_GET['success'] ?? '');
$error_msg   = htmlspecialchars($_GET['error'] ?? '');

// Auto-create tables if missing
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

// Fetch memos
try {
    $memos = $pdo->query("SELECT cm.*, u.name as created_by_name FROM cash_memos cm LEFT JOIN users u ON cm.created_by = u.id ORDER BY cm.created_at DESC LIMIT 100")->fetchAll();
    $todayStmt = $pdo->query("SELECT COALESCE(SUM(grand_total),0) as t, COUNT(*) as c FROM cash_memos WHERE DATE(created_at) = CURDATE()");
    $todayData = $todayStmt->fetch();
} catch (PDOException $e) {
    $memos = []; $todayData = ['t'=>0,'c'=>0];
    $error_msg = "Error fetching cash memos.";
}

$totalAll = array_sum(array_column($memos, 'grand_total'));

// For edit modal - fetch memo + items if ?edit=ID
$editMemo = null; $editItems = [];
if (!empty($_GET['edit'])) {
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
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Finance</p>
            <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Cash Memos</h1>
            <p class="text-[#7c7c7c] text-sm mt-1">Create, manage & print customizable cash receipts</p>
        </div>
        <button onclick="openMemoModal()"
                class="inline-flex items-center gap-2.5 px-6 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg shadow-[#004591]/20 hover:shadow-[#ea741b]/20 transition-all duration-300 self-start sm:self-auto">
            <i class="fas fa-plus text-xs"></i> New Cash Memo
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)]">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-9 h-9 rounded-xl bg-[#ea741b]/10 flex items-center justify-center"><i class="fas fa-file-invoice text-[#ea741b] text-sm"></i></div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-[#7c7c7c]">All Memos</p>
            </div>
            <p class="font-serif text-xl font-bold text-[#004591]"><?= count($memos) ?></p>
        </div>
        <div class="bg-gradient-to-br from-[#ea741b] to-[#cf5e0e] rounded-2xl p-5 shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><i class="fas fa-calendar-day text-white text-sm"></i></div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-white/60">Today</p>
            </div>
            <p class="font-serif text-xl font-bold text-white"><span class="text-sm">৳</span><?= number_format($todayData['t'], 2) ?></p>
            <p class="text-[10px] text-white/50 font-semibold"><?= $todayData['c'] ?> memos today</p>
        </div>
        <div class="bg-[#004591] rounded-2xl p-5 shadow-lg">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center"><i class="fas fa-coins text-white text-sm"></i></div>
                <p class="text-[9px] font-bold uppercase tracking-widest text-white/60">Total Value</p>
            </div>
            <p class="font-serif text-xl font-bold text-white"><span class="text-sm">৳</span><?= number_format($totalAll, 2) ?></p>
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

    <!-- Table -->
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
                                <a href="cash_memos.php?edit=<?= $m['id'] ?>" class="w-8 h-8 rounded-lg bg-[#F4F7FC] hover:bg-[#004591] hover:text-white flex items-center justify-center text-[#7c7c7c] transition-all" title="Edit"><i class="fas fa-pen text-xs"></i></a>
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
                        <p class="text-[#7c7c7c] font-semibold">No cash memos created yet.</p>
                        <button onclick="openMemoModal()" class="mt-4 px-5 py-2 bg-[#004591] text-white text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-[#ea741b] transition-all">Create First Memo</button>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ═══ CREATE / EDIT CASH MEMO MODAL ═══ -->
<div id="memoModal" class="fixed inset-0 z-50 hidden flex items-start justify-center p-4 pt-8 backdrop-blur-sm bg-[#004591]/20 overflow-y-auto">
    <div class="relative w-full max-w-2xl mb-8">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <div>
                    <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5" id="memoModalLabel">New</p>
                    <h3 class="font-serif text-xl text-[#004591] font-bold" id="memoModalTitle">Create Cash Memo</h3>
                </div>
                <button onclick="closeMemoModal()" class="w-9 h-9 rounded-xl bg-[#F4F7FC] hover:bg-red-50 hover:text-red-500 flex items-center justify-center text-[#7c7c7c] transition-all"><i class="fas fa-times text-sm"></i></button>
            </div>
            <form action="api/save_cash_memo.php" method="POST" class="p-6 space-y-5" id="memoForm">
                <input type="hidden" name="memo_id" id="memo_id" value="0">

                <!-- Customer Info -->
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

                <!-- Date, Method, Discount -->
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

                <!-- Line Items -->
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

                <!-- Totals -->
                <div class="flex justify-end">
                    <div class="w-56 space-y-1 text-sm">
                        <div class="flex justify-between"><span class="text-[#7c7c7c]">Subtotal</span><span class="font-bold text-[#004591]" id="dispSubtotal">৳0.00</span></div>
                        <div class="flex justify-between"><span class="text-[#7c7c7c]">Discount</span><span class="font-bold text-green-600" id="dispDiscount">- ৳0.00</span></div>
                        <div class="flex justify-between pt-2 border-t border-gray-200"><span class="font-bold text-[#004591]">Grand Total</span><span class="font-serif text-lg font-bold text-[#ea741b]" id="dispGrand">৳0.00</span></div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-2">Notes</label>
                    <input type="text" name="notes" id="f_notes" placeholder="Optional notes" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-[#004591] outline-none transition">
                </div>

                <div class="pt-4 flex gap-3 border-t border-gray-100">
                    <button type="submit" class="flex-1 py-3 bg-[#004591] hover:bg-[#ea741b] text-white text-[11px] font-bold uppercase tracking-widest rounded-xl shadow-lg transition-all duration-300"><i class="fas fa-check mr-2"></i> <span id="submitBtnText">Save Cash Memo</span></button>
                    <button type="button" onclick="closeMemoModal()" class="px-5 py-3 bg-[#F4F7FC] text-[#7c7c7c] text-[11px] font-bold uppercase tracking-widest rounded-xl transition-all">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'components/footer.php'; ?>

<script>
// ─── Item Row Management ───
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

// ─── Modal Controls ───
function openMemoModal() {
    document.getElementById('memo_id').value = 0;
    document.getElementById('memoModalLabel').textContent = 'New';
    document.getElementById('memoModalTitle').textContent = 'Create Cash Memo';
    document.getElementById('submitBtnText').textContent = 'Save Cash Memo';
    document.getElementById('memoForm').reset();
    document.getElementById('f_date').querySelector('input[type="hidden"]').value = new Date().toISOString().split('T')[0];
    document.getElementById('f_discount').value = 0;
    document.getElementById('itemsContainer').innerHTML = '';
    addItemRow(); // Start with one empty row
    document.getElementById('memoModal').classList.remove('hidden');
}

function closeMemoModal() {
    document.getElementById('memoModal').classList.add('hidden');
}

// Close on backdrop click
document.getElementById('memoModal')?.addEventListener('click', e => { if(e.target.id === 'memoModal') closeMemoModal(); });

// ─── Edit Mode ───
<?php if ($editMemo): ?>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('memo_id').value = <?= $editMemo['id'] ?>;
    document.getElementById('memoModalLabel').textContent = 'Edit';
    document.getElementById('memoModalTitle').textContent = 'Edit Cash Memo — <?= htmlspecialchars($editMemo['memo_number']) ?>';
    document.getElementById('submitBtnText').textContent = 'Update Cash Memo';
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
});
<?php endif; ?>

// Auto-dismiss alerts
setTimeout(() => {
    ['successAlert','errorAlert'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.style.transition = 'opacity 0.5s'; el.style.opacity = 0; setTimeout(() => el.remove(), 500); }
    });
}, 5000);
</script>
