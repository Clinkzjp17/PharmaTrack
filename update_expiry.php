<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$product_id = (int)($input['id']     ?? 0);
$qty        = (int)($input['qty']    ?? -1);
$expiry     = trim($input['expiry']  ?? '');
$notes      = trim($input['notes']   ?? '');

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Missing product ID']);
    exit;
}

// Update quantity on the products table
if ($qty >= 0) {
    $stmt = $conn->prepare('UPDATE products SET quantity = ?, status = CASE WHEN ? = 0 THEN "Out of Stock" WHEN ? <= 40 THEN "Low Stock" ELSE "In Stock" END WHERE id = ?');
    $stmt->bind_param('iiii', $qty, $qty, $qty, $product_id);
    $stmt->execute();
    $stmt->close();
}

if ($expiry !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $expiry);
    if (!$dt) {
        echo json_encode(['success' => false, 'message' => 'Invalid date format']);
        exit;
    }
    $expiryFormatted = $dt->format('Y-m-d');

    $find = $conn->prepare('SELECT id FROM stock_logs WHERE product_id = ? AND expiry_date IS NOT NULL ORDER BY expiry_date ASC LIMIT 1');
    $find->bind_param('i', $product_id);
    $find->execute();
    $logRow = $find->get_result()->fetch_assoc();
    $find->close();

    if ($logRow) {
        $upd = $conn->prepare('UPDATE stock_logs SET expiry_date = ?, notes = ? WHERE id = ?');
        $upd->bind_param('ssi', $expiryFormatted, $notes, $logRow['id']);
        $upd->execute();
        $upd->close();
    } else {
       
        $ins = $conn->prepare('INSERT INTO stock_logs (product_id, type, quantity, date, expiry_date, notes) VALUES (?, "in", ?, CURDATE(), ?, ?)');
        $ins->bind_param('iiss', $product_id, $qty, $expiryFormatted, $notes);
        $ins->execute();
        $ins->close();
    }
}

echo json_encode(['success' => true]);
