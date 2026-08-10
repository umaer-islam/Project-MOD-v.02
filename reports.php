<?php
require_once 'components/header.php';
require_once 'components/sidebar.php';
require_once 'components/topbar.php';
require_once 'database/connection.php';
restrict_access(['admin', 'doctor', 'receptionist']);


$error_msg = '';
$totalPatients = 0;
$totalPayments = 0;
$todayAppts = 0;
$paymentsByMonth = [];

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM patients");
    $totalPatients = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT SUM(amount) FROM payments");
    $totalPayments = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()");
    $todayAppts = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total FROM payments WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month ASC");
    $paymentsByMonth = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error fetching report data. Please check your database connection.";
}
?>

<main class="flex-1 bg-[#F4F7FC] p-4 sm:p-6 lg:p-8 overflow-y-auto">

    <div class="mb-8">
        <p class="text-[10px] uppercase tracking-[0.25em] text-[#ea741b] font-bold mb-1">Insights</p>
        <h1 class="font-serif text-2xl md:text-3xl text-[#004591] font-bold">Analytics & Reports</h1>
        <p class="text-[#7c7c7c] text-sm mt-1">Financial and clinical data summaries for the clinic</p>
    </div>

    <?php if ($error_msg): ?>
    <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium" id="errorAlert">
        <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
        <button onclick="document.getElementById('errorAlert').remove()" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times text-xs"></i></button>
    </div>
    <?php endif; ?>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <div class="admin-card bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="fas fa-users text-[#004591] text-9xl"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#e8f0fa] flex items-center justify-center mb-4">
                <i class="fas fa-users text-[#004591]"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-1">Total Patients</p>
            <p class="font-serif text-4xl font-bold text-[#004591]"><?= number_format($totalPatients) ?></p>
        </div>

        <div class="admin-card bg-white rounded-2xl p-6 border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] relative overflow-hidden group">
            <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <i class="fas fa-money-bill-wave text-green-600 text-9xl"></i>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center mb-4">
                <i class="fas fa-coins text-green-600"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] mb-1">Lifetime Revenue</p>
            <p class="font-serif text-4xl font-bold text-[#004591]"><span class="text-lg">৳</span><?= number_format($totalPayments, 2) ?></p>
        </div>

        <div class="bg-[#004591] rounded-2xl p-6 border border-transparent shadow-[0_4px_20px_rgba(0,69,145,0.15)] cursor-pointer hover:bg-[#ea741b] transition-colors duration-300 group flex flex-col justify-between">
            <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center mb-4">
                <i class="fas fa-file-export text-white text-xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-white font-bold text-base">Export Full Report</p>
                <p class="text-white/60 text-xs mt-1 font-semibold uppercase tracking-wider">CSV / PDF</p>
            </div>
        </div>
    </div>

    <!-- Detailed Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Revenue by Month -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_4px_20px_rgba(0,69,145,0.06)] overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100">
                <p class="text-[10px] uppercase tracking-widest text-[#ea741b] font-bold mb-0.5">Financials</p>
                <h3 class="font-serif text-lg text-[#004591] font-bold">Revenue (Last 6 Months)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c]">Month</th>
                            <th class="whitespace-nowrap px-6 py-3 text-[10px] font-bold uppercase tracking-widest text-[#7c7c7c] text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (count($paymentsByMonth) > 0): ?>
                            <?php foreach($paymentsByMonth as $row): ?>
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-[#004591]">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-[#ea741b]"></div>
                                        <?= date('F Y', strtotime($row['month'] . '-01')) ?>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 font-bold text-[#004591] text-right">৳<?= number_format($row['total'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="2" class="px-6 py-10 text-center text-[#7c7c7c]">No revenue data available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<?php require_once 'components/footer.php'; ?>
