<?php
require_once 'config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input) || empty($input)) {
    echo json_encode(['success'=>false,'message'=>'No data received']);
    exit;
}

$stmt   = $conn->prepare('UPDATE products SET quantity=?, price=?, status=? WHERE id=?');
$errors = 0;
$allowed = ['In Stock','Low Stock','Out of Stock'];

foreach ($input as $item) {
    $id     = (int)($item['id']     ?? 0);
    $qty    = (int)($item['qty']    ?? 0);
    $price  = (float)($item['price'] ?? 0);
    $status = trim($item['status']  ?? '');
    if (!$id || !in_array($status, $allowed)) { $errors++; continue; }
    $stmt->bind_param('idsi', $qty, $price, $status, $id);
    if (!$stmt->execute()) $errors++;
}
$stmt->close();

echo json_encode($errors > 0
    ? ['success'=>false,'message'=>"$errors item(s) failed"]
    : ['success'=>true]
);