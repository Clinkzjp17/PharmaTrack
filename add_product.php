<?php
require_once 'config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$name     = trim($data['name']     ?? '');
$category = trim($data['category'] ?? '');
$quantity = (int)($data['quantity'] ?? 0);
$price    = (float)($data['price'] ?? 0);
$status   = trim($data['status']   ?? '');

$allowed_statuses = ['In Stock', 'Low Stock', 'Out of Stock'];

if (!$name || !$category || !in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid fields.']);
    exit;
}

$check = $conn->prepare('SELECT id FROM products WHERE product_name = ?');
$check->bind_param('s', $name);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'A product with that name already exists.']);
    exit;
}
$check->close();

$stmt = $conn->prepare(
    'INSERT INTO products (product_name, category, quantity, price, status) VALUES (?, ?, ?, ?, ?)'
);
$stmt->bind_param('ssiis', $name, $category, $quantity, $price, $status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
$stmt->close();
