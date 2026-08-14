<?php
session_start();
require_once '../components/auth_guard.php';
require_once '../database/connection.php';
require_once '../components/activity_logger.php';
require_once '../components/cache.php';
restrict_access(['admin', 'doctor', 'receptionist']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../cash_memos.php");
    exit;
}

$redirectBase = '../cash_memos.php';
$saveAndPrint = isset($_POST['save_and_print']);

$memo_id        = (int)($_POST['memo_id'] ?? 0);
$customer_name  = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$customer_address = trim($_POST['customer_address'] ?? '');
$memo_date      = $_POST['memo_date'] ?? date('Y-m-d');
$discount       = (float)($_POST['discount'] ?? 0);
$discount_type  = in_array($_POST['discount_type'] ?? '', ['amount', 'percentage']) ? $_POST['discount_type'] : 'amount';
$payment_method = $_POST['payment_method'] ?? 'Cash';
$notes          = trim($_POST['notes'] ?? '');

$descriptions = $_POST['item_desc'] ?? [];
$quantities   = $_POST['item_qty'] ?? [];
$unit_prices  = $_POST['item_price'] ?? [];

if (!$customer_name || empty($descriptions)) {
    header("Location: {$redirectBase}?error=" . urlencode("Customer name and at least one item are required."));
    exit;
}

try {
    $pdo->beginTransaction();

    $subtotal = 0;
    $items = [];
    for ($i = 0; $i < count($descriptions); $i++) {
        $desc = trim($descriptions[$i] ?? '');
        $qty  = max(0.01, (float)($quantities[$i] ?? 1));
        $price = max(0, (float)($unit_prices[$i] ?? 0));
        if ($desc === '') continue;
        $lineTotal = round($qty * $price, 2);
        $subtotal += $lineTotal;
        $items[] = [$desc, $qty, $price, $lineTotal];
    }

    if (empty($items)) {
        $pdo->rollBack();
        header("Location: {$redirectBase}?error=" . urlencode("At least one valid item is required."));
        exit;
    }

    $grand_total = max(0, $subtotal - ($discount_type === 'percentage' ? $subtotal * $discount / 100 : $discount));

    if ($memo_id > 0) {
        $numStmt = $pdo->prepare("SELECT memo_number FROM cash_memos WHERE id = ?");
        $numStmt->execute([$memo_id]);
        $memo_number = $numStmt->fetchColumn() ?: "CM-{$memo_id}";

        $stmt = $pdo->prepare("UPDATE cash_memos SET customer_name=?, customer_phone=?, customer_address=?, memo_date=?, subtotal=?, discount=?, discount_type=?, grand_total=?, payment_method=?, notes=? WHERE id=?");
        $stmt->execute([$customer_name, $customer_phone, $customer_address, $memo_date, $subtotal, $discount, $discount_type, $grand_total, $payment_method, $notes, $memo_id]);

        $pdo->prepare("DELETE FROM cash_memo_items WHERE memo_id = ?")->execute([$memo_id]);
        $itemStmt = $pdo->prepare("INSERT INTO cash_memo_items (memo_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $itemStmt->execute([$memo_id, $item[0], $item[1], $item[2], $item[3]]);
        }

        $pdo->commit();
        log_activity($pdo, 'UPDATE_CASH_MEMO', "Updated cash memo {$memo_number} for {$customer_name} — Total: ৳{$grand_total}");
        cache_flush('dash:');

        if ($saveAndPrint) {
            header("Location: ../print_cash_memo.php?id={$memo_id}");
        } else {
            header("Location: {$redirectBase}?success=" . urlencode("Cash memo updated successfully."));
        }
        exit;

    } else {
        $datePrefix = date('ymd');
        $countStmt = $pdo->query("SELECT COUNT(*) as c FROM cash_memos WHERE memo_number LIKE 'CM-{$datePrefix}%'");
        $dayCount = ($countStmt->fetch()['c'] ?? 0) + 1;
        $memo_number = 'CM-' . $datePrefix . '-' . str_pad($dayCount, 4, '0', STR_PAD_LEFT);

        $stmt = $pdo->prepare("INSERT INTO cash_memos (memo_number, customer_name, customer_phone, customer_address, memo_date, subtotal, discount, discount_type, grand_total, payment_method, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$memo_number, $customer_name, $customer_phone, $customer_address, $memo_date, $subtotal, $discount, $discount_type, $grand_total, $payment_method, $notes, $_SESSION['user_id'] ?? null]);

        $newMemoId = $pdo->lastInsertId();
        $itemStmt = $pdo->prepare("INSERT INTO cash_memo_items (memo_id, description, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $itemStmt->execute([$newMemoId, $item[0], $item[1], $item[2], $item[3]]);
        }

        $pdo->commit();
        log_activity($pdo, 'CREATE_CASH_MEMO', "Created cash memo {$memo_number} for {$customer_name} — Total: ৳{$grand_total}");
        cache_flush('dash:');

        if ($saveAndPrint) {
            header("Location: ../print_cash_memo.php?id={$newMemoId}");
        } else {
            header("Location: {$redirectBase}?success=" . urlencode("Cash memo {$memo_number} created successfully."));
        }
        exit;
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    header("Location: {$redirectBase}?error=" . urlencode("Database error. Please try again."));
    exit;
}
