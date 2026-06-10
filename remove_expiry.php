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

$product_id   = (int)($input['id']           ?? 0);
$disposal     = trim($input['disposal']      ?? 'other');
$date_removed = trim($input['date_removed']  ?? date('Y-m-d'));
$notes        = trim($input['notes']         ?? '');

$allowed_disposals = ['returned', 'destroyed', 'donated', 'other'];
if (!$product_id || !in_array($disposal, $allowed_disposals)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// Find the earliest expiry stock_log for this product
$find = $conn->prepare('SELECT id FROM stock_logs WHERE product_id = ? AND expiry_date IS NOT NULL ORDER BY expiry_date ASC LIMIT 1');
$find->bind_param('i', $product_id);
$find->execute();
$logRow = $find->get_result()->fetch_assoc();
$find->close();

if (!$logRow) {
    echo json_encode(['success' => false, 'message' => 'Stock log record not found']);
    exit;
}

// Remove that specific stock_log entry
$del = $conn->prepare('DELETE FROM stock_logs WHERE id = ?');
$del->bind_param('i', $logRow['id']);

if ($del->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
$del->close();
