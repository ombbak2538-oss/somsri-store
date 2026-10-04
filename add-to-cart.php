<?php
session_start();
header('Content-Type: application/json');
include __DIR__ . "/../config/db.php";

$id  = intval($_POST['id'] ?? 0);
$qty = intval($_POST['qty'] ?? 1);
if ($qty < 1) $qty = 1;

if ($id <= 0) {
    echo json_encode(['ok' => false, 'message' => 'รหัสสินค้าไม่ถูกต้อง']);
    exit;
}

$stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();

if (!$product) {
    echo json_encode(['ok' => false, 'message' => 'ไม่พบสินค้าชิ้นนี้']);
    exit;
}

if ($product['stock'] < $qty) {
    echo json_encode(['ok' => false, 'message' => 'สินค้าในสต็อกไม่พอ']);
    exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$id])) {
    if ($_SESSION['cart'][$id] + $qty > $product['stock']) {
        $_SESSION['cart'][$id] = $product['stock']; 
    } else {
        $_SESSION['cart'][$id] += $qty;
    }
} else {
    $_SESSION['cart'][$id] = $qty;
}

$count = array_sum($_SESSION['cart']);

echo json_encode([
  'ok'    => true,
  'count' => $count
]);