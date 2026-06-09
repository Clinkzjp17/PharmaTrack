<?php
require_once 'config.php'; 
header('Content-Type: application/json');

$data       = json_decode(file_get_contents('php://input'), true);
$type       = $data['type']       ?? '';
$product_id = (int)($data['product_id'] ?? 0);
$qty        = (int)($data['qty']        ?? 0);
$date       = $data['date']   ?? date('Y-m-d');
$notes      = $data['notes']  ?? '';
$expiry     = $data['expiry'] ?? null;

if (!$product_id || !$qty || !in_array($type, ['in','out'])) {
    echo json_encode(['success'=>false,'message'=>'Invalid input']);
    exit;
}

if ($type === 'in') {
    $stmt = $conn->prepare('UPDATE products SET quantity = quantity + ? WHERE id = ?');
} else {
    $stmt = $conn->prepare('UPDATE products SET quantity = GREATEST(0, quantity - ?) WHERE id = ?');
}
$stmt->bind_param('ii', $qty, $product_id);
$stmt->execute();
$stmt->close();

$logStmt = $conn->prepare(
    'INSERT INTO stock_logs (product_id, type, quantity, date, expiry_date, notes) VALUES (?,?,?,?,?,?)'
);
$logStmt->bind_param('isisss', $product_id, $type, $qty, $date, $expiry, $notes);
$logStmt->execute();
$logStmt->close();

$conn->query("UPDATE products SET status = CASE
  WHEN quantity = 0        THEN 'Out of Stock'
  WHEN quantity <= 40      THEN 'Low Stock'
  ELSE 'In Stock'
END");

echo json_encode(['success' => true]);