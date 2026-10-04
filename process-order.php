<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/config/db.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    echo json_encode(['status' => 'error', 'message' => 'เซสชั่นหมดอายุหรือไม่มีสินค้าในตะกร้า']);
    exit;
}

$userId = $_SESSION['user_id'];
$cart   = $_SESSION['cart'];

$receiver_name  = $_POST['receiver_name'] ?? '';
$receiver_phone = $_POST['receiver_phone'] ?? '';
$address        = $_POST['address'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'transfer';

if (empty($receiver_name) || empty($receiver_phone) || empty($address)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลผู้รับให้ครบถ้วน']);
    exit;
}

$conn->begin_transaction();

try {
    $totalAmount = 0;
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $sql = "SELECT id, price, discount_percent, stock FROM products WHERE id IN ($ids)";
    $res = $conn->query($sql);
    
    $products_data = [];
    while ($p = $res->fetch_assoc()) {
        $qty = $cart[$p['id']];
        
        if ($p['stock'] < $qty) {
            throw new Exception("สินค้าบางรายการมีสต็อกไม่เพียงพอ");
        }
        
        $finalPrice = $p['discount_percent'] > 0 ? $p['price'] * (100 - $p['discount_percent']) / 100 : $p['price'];
        $totalAmount += ($finalPrice * $qty);
        $products_data[] = [
            'id' => $p['id'],
            'price' => $finalPrice,
            'qty' => $qty
        ];
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, receiver_name, receiver_phone, address, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("idssss", $userId, $totalAmount, $receiver_name, $receiver_phone, $address, $payment_method);
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmtDetail = $conn->prepare("INSERT INTO order_details (order_id, product_id, price, qty) VALUES (?, ?, ?, ?)");
    $stmtUpdateStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($products_data as $item) {
        $stmtDetail->bind_param("iidi", $orderId, $item['id'], $item['price'], $item['qty']);
        $stmtDetail->execute();
        $stmtUpdateStock->bind_param("ii", $item['qty'], $item['id']);
        $stmtUpdateStock->execute();
    }

    $conn->commit();

    unset($_SESSION['cart']);

    echo json_encode([
        'status' => 'success',
        'message' => 'บันทึกคำสั่งซื้อเรียบร้อยแล้ว',
        'order_id' => $orderId
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}