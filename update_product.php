<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'pharmatrack');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payload']);
    exit;
}

$stmt = $conn->prepare('UPDATE products SET quantity=?, price=?, status=? WHERE id=?');
foreach ($data as $item) {
    $stmt->bind_param('idsi', $item['qty'], $item['price'], $item['status'], $item['id']);
    $stmt->execute();
}

echo json_encode(['success' => true]);