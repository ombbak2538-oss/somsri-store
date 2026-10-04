<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . "/../config/db.php";

$code = trim($_GET['code'] ?? '');

if ($code === '') {
    echo json_encode(['success' => false, 'items' => []]);
    exit;
}

$sql = "SELECT order_code, tracking_no, shipping_status 
        FROM orders 
        WHERE order_code = ? OR tracking_no = ? OR customer_phone = ? 
        ORDER BY id DESC LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $code, $code, $code);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

if (count($orders) > 0) {
    echo json_encode(['success' => true, 'items' => $orders]);
} else {
    echo json_encode(['success' => false, 'items' => []]);
}