<?php
session_start();
require_once 'database/connection.php';
if (!isset($_SESSION['user_id'])) { header('Location: login_page.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo "Memo not found."; exit; }

try {
    $stmt = $pdo->prepare("SELECT cm.*, u.name as staff_name FROM cash_memos cm LEFT JOIN users u ON cm.created_by = u.id WHERE cm.id = ?");
    $stmt->execute([$id]);
    $memo = $stmt->fetch();
    if (!$memo) { echo "Cash memo not found."; exit; }

    $iStmt = $pdo->prepare("SELECT * FROM cash_memo_items WHERE memo_id = ? ORDER BY id");
    $iStmt->execute([$id]);
    $items = $iStmt->fetchAll();
} catch (PDOException $e) { echo "Database error."; exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cash Memo <?= htmlspecialchars($memo['memo_number']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
<script>tailwind.config={theme:{extend:{fontFamily:{serif:['"Playfair Display"','serif'],sans:['"Outfit"','sans-serif']},colors:{brand:{blue:'#004591',orange:'#ea741b',pale:'#f4f7fc'}}}}}</script>
<style>
body{font-family:'Outfit',sans-serif;background:#e2e8f0;-webkit-print-color-adjust:exact;print-color-adjust:exact}
.memo-page{width:210mm;min-height:auto;background:#fff;margin:10mm auto;box-shadow:0 20px 50px rgba(0,0,0,.15);overflow:hidden;border-radius:4px}
@media print{body{background:#fff;margin:0;padding:0}.memo-page{width:100%;margin:0;box-shadow:none;border-radius:0}.no-print{display:none!important}@page{size:A4 portrait;margin:8mm}}
@media(max-width:800px){.memo-page{width:100%;margin:0;border-radius:0}}
</style>
</head>
<body>

<div class="fixed top-4 right-4 no-print flex gap-3 z-50">
    <button onclick="window.close()" class="px-5 py-2.5 bg-white text-gray-700 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition text-sm">Close</button>
    <button onclick="window.print()" class="px-5 py-2.5 bg-brand-blue text-white font-bold rounded-xl shadow-lg hover:bg-brand-orange transition text-sm"><i class="fas fa-print mr-2"></i>Print</button>
</div>

<div class="memo-page">
    <!-- Header -->
    <div class="bg-gradient-to-r from-brand-blue to-[#002d5e] text-white px-10 py-6 relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-60 h-60 bg-white/5 rounded-full"></div>
        <div class="relative z-10 flex justify-between items-start">
            <div class="flex items-center gap-4">
                <img src="Logo.png" alt="Logo" class="w-12 h-12 object-contain rounded-xl bg-white/10 p-1 shadow-lg">
                <div>
                    <h1 class="font-serif text-2xl font-bold tracking-tight leading-none">Mamun's <span class="text-brand-orange">Ortho</span> Dental</h1>
                    <p class="text-[9px] font-bold text-white/40 tracking-[.25em] uppercase mt-1">5/2 BlockA, Lalmatia, Dhaka &middot; +880 1712-718527</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[9px] font-bold uppercase tracking-[.2em] text-brand-orange mb-1">Cash Memo</p>
                <p class="font-serif text-lg font-bold"><?= htmlspecialchars($memo['memo_number']) ?></p>
                <p class="text-[11px] text-white/50 mt-0.5"><?= date('d M Y', strtotime($memo['memo_date'])) ?></p>
            </div>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="px-10 py-4 bg-brand-pale border-b border-blue-100">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <p class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Customer</p>
                <p class="text-sm font-bold text-brand-blue"><?= htmlspecialchars($memo['customer_name']) ?></p>
            </div>
            <div>
                <p class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Phone</p>
                <p class="text-sm font-bold text-gray-700"><?= htmlspecialchars($memo['customer_phone'] ?: 'N/A') ?></p>
            </div>
            <div>
                <p class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Address</p>
                <p class="text-sm font-bold text-gray-700"><?= htmlspecialchars($memo['customer_address'] ?: 'N/A') ?></p>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="px-10 pt-6 pb-3">
        <table class="w-full text-sm">
            <thead><tr class="border-b-2 border-brand-blue/10">
                <th class="text-left py-2 text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 w-8">#</th>
                <th class="text-left py-2 text-[8px] font-bold uppercase tracking-[.15em] text-gray-400">Description</th>
                <th class="text-center py-2 text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 w-16">Qty</th>
                <th class="text-right py-2 text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 w-24">Unit Price</th>
                <th class="text-right py-2 text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 w-24">Total</th>
            </tr></thead>
            <tbody>
            <?php foreach($items as $idx => $it): ?>
                <tr class="border-b border-gray-50">
                    <td class="py-2.5 text-gray-400 font-bold"><?= $idx+1 ?></td>
                    <td class="py-2.5 font-semibold text-gray-800"><?= htmlspecialchars($it['description']) ?></td>
                    <td class="py-2.5 text-center text-gray-600"><?= rtrim(rtrim(number_format($it['quantity'],2),'0'),'.') ?></td>
                    <td class="py-2.5 text-right text-gray-600">৳<?= number_format($it['unit_price'],2) ?></td>
                    <td class="py-2.5 text-right font-bold text-gray-700">৳<?= number_format($it['total'],2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals -->
    <div class="px-10 pb-6">
        <div class="flex justify-end">
            <div class="w-56">
                <div class="flex justify-between py-1.5 border-b border-gray-100"><span class="text-gray-400 text-[12px] font-semibold">Subtotal</span><span class="font-bold text-gray-700">৳<?= number_format($memo['subtotal'],2) ?></span></div>
                <?php if($memo['discount'] > 0): ?>
                <div class="flex justify-between py-1.5 border-b border-gray-100"><span class="text-gray-400 text-[12px] font-semibold">Discount</span><span class="font-bold text-green-600">- ৳<?= number_format($memo['discount'],2) ?></span></div>
                <?php endif; ?>
                <div class="flex justify-between py-2.5 bg-brand-blue rounded-xl px-4 mt-2">
                    <span class="text-white/70 text-[11px] font-bold uppercase tracking-wider">Grand Total</span>
                    <span class="font-serif text-lg font-bold text-white">৳<?= number_format($memo['grand_total'],2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Meta -->
    <div class="px-10 pb-6">
        <div class="flex items-center justify-between bg-brand-pale rounded-2xl p-4 border border-blue-100 text-sm">
            <div class="flex items-center gap-5">
                <div>
                    <p class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Payment</p>
                    <p class="font-bold text-brand-blue"><?= htmlspecialchars($memo['payment_method']) ?></p>
                </div>
                <?php if($memo['notes']): ?>
                <div class="h-8 w-px bg-blue-200"></div>
                <div>
                    <p class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Notes</p>
                    <p class="text-gray-600"><?= htmlspecialchars($memo['notes']) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <p class="text-[8px] font-bold uppercase tracking-[.15em] text-gray-400">Issued By</p>
                <p class="font-bold text-brand-blue text-sm"><?= htmlspecialchars($memo['staff_name'] ?? 'Admin') ?></p>
            </div>
        </div>
    </div>

    <!-- Signature -->
    <div class="px-10 pb-6 flex justify-between items-end">
        <div class="text-center w-44">
            <div class="h-12 border-b-2 border-gray-300 mb-1"></div>
            <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Customer Signature</p>
        </div>
        <div class="text-center w-44">
            <div class="h-12 border-b-2 border-brand-blue/30 mb-1"></div>
            <p class="text-[9px] text-brand-orange font-bold uppercase tracking-widest">Authorized Signature</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="bg-brand-blue text-white px-10 py-3 flex justify-between items-center text-[10px]">
        <span><i class="fas fa-envelope text-brand-orange mr-1"></i>mamunddcbdc@gmail.com</span>
        <p class="text-white/30 text-[8px] font-bold uppercase tracking-widest">Thank you for choosing us</p>
        <span><i class="fas fa-globe text-brand-orange mr-1"></i>mamunorthodental.com</span>
    </div>
</div>

<script>window.addEventListener('load',()=>{setTimeout(()=>{window.print()},800)});</script>
</body>
</html>
