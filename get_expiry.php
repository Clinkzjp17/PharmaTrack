<?php
require_once 'config.php';
require_once 'auth_guard.php';
require_role('admin');

header('Content-Type: application/json');

// Read from products + their latest expiry_date from stock_logs
$sql = "
    SELECT
        p.id            AS id,
        p.product_name  AS name,
        p.category      AS category,
        p.quantity      AS qty,
        sl.id           AS log_id,
        sl.expiry_date  AS expiry_raw,
        DATEDIFF(sl.expiry_date, CURDATE()) AS days_left
    FROM products p
    JOIN stock_logs sl ON sl.id = (
        SELECT id FROM stock_logs
        WHERE product_id = p.id
          AND expiry_date IS NOT NULL
        ORDER BY expiry_date ASC
        LIMIT 1
    )
    ORDER BY sl.expiry_date ASC
";

$result = $conn->query($sql);
if (!$result) {
    echo json_encode(['success' => false, 'message' => $conn->error]);
    exit;
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $days = (int)$row['days_left'];

    if ($days < 0) {
        $status    = 'expired';
        $daysLabel = 'Expired';
    } elseif ($days <= 7) {
        $status    = 'soon';
        $daysLabel = $days === 0 ? 'Expires today' : "$days day" . ($days === 1 ? '' : 's') . ' left';
    } elseif ($days <= 90) {
        $status    = 'soon';
        $daysLabel = $days < 30
            ? ceil($days / 7) . ' week' . (ceil($days / 7) === 1 ? '' : 's')
            : ceil($days / 30) . ' month' . (ceil($days / 30) === 1 ? '' : 's');
    } else {
        $status    = 'ok';
        $mo        = ceil($days / 30);
        $daysLabel = $mo . ' month' . ($mo === 1 ? '' : 's');
    }

    $rows[] = [
        'id'       => (int)$row['id'],
        'log_id'   => (int)$row['log_id'],
        'name'     => $row['name'],
        'category' => $row['category'],
        'qty'      => (int)$row['qty'],
        'batch'    => '',
        'expiry'   => date('M j, Y', strtotime($row['expiry_raw'])),
        'daysLeft' => $daysLabel,
        'status'   => $status,
    ];
}

echo json_encode(['success' => true, 'data' => $rows]);
