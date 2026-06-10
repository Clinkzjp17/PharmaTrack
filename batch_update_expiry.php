<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$errors = 0;

foreach ($input as $item) {
    $product_id = (int)($item['id']        ?? 0);
    $qty        = (int)($item['newQty']    ?? 0);
    $expiry     = trim($item['newExpiry']  ?? '');

    if (!$product_id || !$expiry) { $errors++; continue; }

    $dt = DateTime::createFromFormat('Y-m-d', $expiry);
    if (!$dt) { $errors++; continue; }
    $expiryFormatted = $dt->format('Y-m-d');

    // Update quantity on products
    $stmt = $conn->prepare('UPDATE products SET quantity = ?, status = CASE WHEN ? = 0 THEN "Out of Stock" WHEN ? <= 40 THEN "Low Stock" ELSE "In Stock" END WHERE id = ?');
    $stmt->bind_param('iiii', $qty, $qty, $qty, $product_id);
    if (!$stmt->execute()) $errors++;
    $stmt->close();

    // Update expiry on stock_logs
    $find = $conn->prepare('SELECT id FROM stock_logs WHERE product_id = ? AND expiry_date IS NOT NULL ORDER BY expiry_date ASC LIMIT 1');
    $find->bind_param('i', $product_id);
    $find->execute();
    $logRow = $find->get_result()->fetch_assoc();
    $find->close();

    if ($logRow) {
        $upd = $conn->prepare('UPDATE stock_logs SET expiry_date = ? WHERE id = ?');
        $upd->bind_param('si', $expiryFormatted, $logRow['id']);
        if (!$upd->execute()) $errors++;
        $upd->close();
    } else {
        $ins = $conn->prepare('INSERT INTO stock_logs (product_id, type, quantity, date, expiry_date) VALUES (?, "in", ?, CURDATE(), ?)');
        $ins->bind_param('iis', $product_id, $qty, $expiryFormatted);
        if (!$ins->execute()) $errors++;
        $ins->close();
    }
}

echo json_encode($errors > 0
    ? ['success' => false, 'message' => "$errors item(s) failed to update"]
    : ['success' => true]
);
