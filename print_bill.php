<?php
session_start();
require_once 'database/connection.php';

// Auth check
if (!isset($_SESSION['user_id'])) { header('Location: login_page.php'); exit; }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo "Bill not found."; exit; }

try {
    // Get payment + patient info
    $stmt = $pdo->prepare("SELECT py.*, p.name as patient_name, p.patient_id as p_id, p.phone, p.age, p.gender, p.address FROM payments py JOIN patients p ON py.patient_id = p.id WHERE py.id = ?");
    $stmt->execute([$id]);
    $bill = $stmt->fetch();
    if (!$bill) { echo "Payment record not found."; exit; }

    // Get all payments for this patient to calculate totals
    $histStmt = $pdo->prepare("SELECT SUM(amount) as total_paid, COUNT(*) as payment_count FROM payments WHERE patient_id = ?");
    $histStmt->execute([$bill['patient_id']]);
    $history = $histStmt->fetch();

    // Generate bill number
    $billNo = 'MOD-' . str_pad($bill['id'], 5, '0', STR_PAD_LEFT);

} catch (PDOException $e) {
    echo "Database error."; exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= $billNo ?> — <?= htmlspecialchars($bill['patient_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: {
            fontFamily: { serif: ['"Playfair Display"','serif'], sans: ['"Outfit"','sans-serif'] },
            colors: { brand: { blue:'#004591', orange:'#ea741b', pale:'#f4f7fc' }}
        }}}
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; background: #e2e8f0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bill-page {
            width: 210mm; min-height: auto; background: white;
            margin: 10mm auto; box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            overflow: hidden; border-radius: 4px;
        }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .bill-page { width: 100%; margin: 0; box-shadow: none; border-radius: 0; }
            .no-print { display: none !important; }
            @page { size: A4 portrait; margin: 8mm; }
        }
        @media (max-width: 800px) {
            .bill-page { width: 100%; margin: 0; border-radius: 0; }
        }
    </style>
</head>
<body>

    <!-- Print/Close Controls -->
    <div class="fixed top-4 right-4 no-print flex gap-3 z-50">
        <button onclick="window.close()" class="px-5 py-2.5 bg-white text-gray-700 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition-colors text-sm">Close</button>
        <button onclick="window.print()" class="px-5 py-2.5 bg-brand-blue text-white font-bold rounded-xl shadow-lg hover:bg-brand-orange transition-colors text-sm"><i class="fas fa-print mr-2"></i> Print Bill</button>
    </div>

    <div class="bill-page">

        <!-- ═══ HEADER ═══ -->
        <div class="bg-gradient-to-r from-brand-blue to-[#002d5e] text-white px-10 py-7 relative overflow-hidden">
            <div class="absolute -right-16 -top-16 w-60 h-60 bg-white/5 rounded-full"></div>
            <div class="absolute right-20 bottom-0 w-32 h-32 bg-brand-orange/10 rounded-full"></div>
            <div class="relative z-10 flex justify-between items-start">
                <div class="flex items-center gap-4">
                    <img src="Logo.png" alt="Logo" class="w-14 h-14 object-contain rounded-xl bg-white/10 p-1.5 shadow-lg">
                    <div>
                        <h1 class="font-serif text-2xl font-bold tracking-tight leading-none">Mamun's <span class="text-brand-orange">Ortho</span> Dental</h1>
                        <p class="text-[10px] font-bold text-white/50 tracking-[.25em] uppercase mt-1">Premier Dental Care &middot; Lalmatia, Dhaka</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-bold uppercase tracking-[.2em] text-brand-orange mb-1">Payment Invoice</p>
                    <p class="font-serif text-xl font-bold"><?= $billNo ?></p>
                    <p class="text-[11px] text-white/50 mt-1"><?= date('d M Y, h:i A', strtotime($bill['created_at'])) ?></p>
                </div>
            </div>
        </div>

        <!-- ═══ PATIENT INFO BAR ═══ -->
        <div class="px-10 py-5 bg-brand-pale border-b border-blue-100">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Patient Name</p>
                    <p class="text-sm font-bold text-brand-blue"><?= htmlspecialchars($bill['patient_name']) ?></p>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Patient ID</p>
                    <p class="text-sm font-bold text-gray-700"><?= htmlspecialchars($bill['p_id']) ?></p>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Phone</p>
                    <p class="text-sm font-bold text-gray-700"><?= htmlspecialchars($bill['phone'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <p class="text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 mb-0.5">Age / Gender</p>
                    <p class="text-sm font-bold text-gray-700"><?= htmlspecialchars($bill['age'] ?? '--') ?>Y / <?= htmlspecialchars($bill['gender'] ?? '--') ?></p>
                </div>
            </div>
        </div>

        <!-- ═══ BILLING TABLE ═══ -->
        <div class="px-10 pt-8 pb-4">
            <h3 class="text-[10px] font-bold uppercase tracking-[.2em] text-brand-blue mb-4 flex items-center gap-2">
                <i class="fas fa-file-invoice text-brand-orange text-xs"></i> Service Details
            </h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-brand-blue/10">
                        <th class="text-left py-2.5 text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 w-8">#</th>
                        <th class="text-left py-2.5 text-[9px] font-bold uppercase tracking-[.15em] text-gray-400">Description</th>
                        <th class="text-right py-2.5 text-[9px] font-bold uppercase tracking-[.15em] text-gray-400">Amount (BDT)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-50">
                        <td class="py-3 text-gray-400 font-bold">1</td>
                        <td class="py-3">
                            <p class="font-semibold text-gray-800">Dental Consultation & Treatment</p>
                                <?php if ($bill['notes']): ?>
                                <p class="text-[11px] text-gray-400 mt-0.5"><?= htmlspecialchars($bill['notes']) ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-right font-bold text-gray-700">৳<?= number_format($bill['amount'], 2) ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ═══ TOTALS ═══ -->
        <div class="px-10 pb-6">
            <div class="flex justify-end">
                <div class="w-64">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-400 text-[12px] font-semibold">Subtotal</span>
                        <span class="font-bold text-gray-700">৳<?= number_format($bill['amount'], 2) ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-400 text-[12px] font-semibold">Discount</span>
                        <span class="font-bold text-green-600">- ৳0.00</span>
                    </div>
                    <div class="flex justify-between py-3 bg-brand-blue rounded-xl px-4 mt-2">
                        <span class="text-white/70 text-[12px] font-bold uppercase tracking-wider">Total Paid</span>
                        <span class="font-serif text-xl font-bold text-white">৳<?= number_format($bill['amount'], 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ PAYMENT METHOD + QR ═══ -->
        <div class="px-10 pb-8">
            <div class="flex items-center justify-between bg-brand-pale rounded-2xl p-5 border border-blue-100">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 mb-1">Payment Method</p>
                        <?php
                        $methodIcon = 'fa-money-bill-wave'; $methodColor = 'text-green-600 bg-green-50';
                        if ($bill['payment_method'] === 'Bkash') { $methodIcon = 'fa-mobile-screen'; $methodColor = 'text-pink-600 bg-pink-50'; }
                        if ($bill['payment_method'] === 'Nagad') { $methodIcon = 'fa-mobile-screen'; $methodColor = 'text-orange-600 bg-orange-50'; }
                        if ($bill['payment_method'] === 'Card') { $methodIcon = 'fa-credit-card'; $methodColor = 'text-blue-600 bg-blue-50'; }
                        ?>
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-lg <?= $methodColor ?> flex items-center justify-center"><i class="fas <?= $methodIcon ?> text-sm"></i></span>
                            <span class="font-bold text-brand-blue text-base"><?= htmlspecialchars($bill['payment_method']) ?></span>
                        </div>
                    </div>
                    <div class="h-10 w-px bg-blue-200"></div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-[.15em] text-gray-400 mb-1">Patient Lifetime Total</p>
                        <p class="font-bold text-brand-blue text-base">৳<?= number_format($history['total_paid'] ?? 0, 2) ?> <span class="text-[10px] text-gray-400 font-semibold">(<?= $history['payment_count'] ?? 0 ?> payments)</span></p>
                    </div>
                </div>
                <div class="text-center">
                    <?php
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                    $verifyUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/patient_record.php?pid=' . urlencode($bill['p_id']);
                    ?>
                    <div class="bg-white p-1.5 rounded-xl shadow-sm inline-block">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&color=004591&data=<?= urlencode($verifyUrl) ?>" alt="QR" class="w-16 h-16 opacity-90">
                    </div>
                    <p class="text-[8px] text-gray-400 tracking-widest uppercase font-bold mt-1">Verify</p>
                </div>
            </div>
        </div>

        <!-- ═══ FOOTER ═══ -->
        <div class="bg-brand-blue text-white px-10 py-4 flex justify-between items-center text-[11px]">
            <div class="flex items-center gap-5">
                <span><i class="fas fa-location-dot text-brand-orange mr-1.5"></i>5/2 BlockA, Lalmatia, Dhaka</span>
                <span><i class="fas fa-phone text-brand-orange mr-1.5"></i>+880 1712-718527</span>
                <span><i class="fas fa-envelope text-brand-orange mr-1.5"></i>mamunddcbdc@gmail.com</span>
            </div>
            <p class="text-white/30 text-[9px] font-bold uppercase tracking-widest">Thank you for choosing us</p>
        </div>

    </div>

    <script>
        window.addEventListener('load', () => { setTimeout(() => { window.print(); }, 800); });
    </script>
</body>
</html>
