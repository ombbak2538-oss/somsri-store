<?php
session_start();
require_once __DIR__ . "/../config/db.php";

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'msg' => 'กรุณาเข้าสู่ระบบก่อนสั่งซื้อ']);
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo json_encode(['ok' => false, 'msg' => 'ไม่มีสินค้าในตะกร้า']);
    exit;
}

$uid      = (int)$_SESSION['user_id'];
$fullname = $_POST['fullname'] ?? '';
$phone    = $_POST['phone'] ?? '';
$address  = $_POST['address'] ?? '';
$pay      = $_POST['payment_method'] ?? 'cod';

$slip = null;
if ($pay === 'bank' && !empty($_FILES['slip']['name'])) {
    $targetDir = __DIR__ . "/uploads/slips/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true); 

    $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
    $slip = 'slip_' . time() . '_' . rand(100, 999) . '.' . $ext;
    move_uploaded_file($_FILES['slip']['tmp_name'], $targetDir . $slip);
}

$conn->begin_transaction();

try {
    $ids = implode(",", array_map('intval', array_keys($cart)));
    $productsRes = $conn->query("SELECT id, price, stock FROM products WHERE id IN ($ids)");
    
    $subtotal = 0;
    $orderItems = [];
    
    while ($p = $productsRes->fetch_assoc()) {
        $qty = (int)$cart[$p['id']];
        
        if ($p['stock'] < $qty) {
            throw new Exception("สินค้าบางรายการมีสต็อกไม่พอ");
        }
        
        $itemTotal = $p['price'] * $qty;
        $subtotal += $itemTotal;
        $orderItems[] = [
            'id'    => $p['id'],
            'price' => $p['price'],
            'qty'   => $qty
        ];
    }

    $shippingFee = 0;
    $FREE_SHIP_MIN = 500;
    if ($subtotal < $FREE_SHIP_MIN) {
        $zoneId = $_SESSION['shipping_zone_id'] ?? 0;
        if ($zoneId) {
            $z = $conn->prepare("SELECT fee FROM shipping_zones WHERE id=?");
            $z->bind_param("i", $zoneId);
            $z->execute();
            $shippingFee = (float)($z->get_result()->fetch_assoc()['fee'] ?? 0);
        }
    }
    
    $grandTotal = $subtotal + $shippingFee;
    $orderNo = 'SS' . date('Ymd') . '-' . rand(1000, 9999);

    $stmt = $conn->prepare("INSERT INTO orders (order_no, user_id, fullname, phone, address, total_amount, shipping_fee, payment_method, slip, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')");
    $stmt->bind_param("sisssddss", $orderNo, $uid, $fullname, $phone, $address, $grandTotal, $shippingFee, $pay, $slip);
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmtItem  = $conn->prepare("INSERT INTO order_items (order_id, product_id, price, qty) VALUES (?, ?, ?, ?)");
    $stmtStock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($orderItems as $item) {
        $stmtItem->bind_param("iidi", $orderId, $item['id'], $item['price'], $item['qty']);
        $stmtItem->execute();

        $stmtStock->bind_param("ii", $item['qty'], $item['id']);
        $stmtStock->execute();
    }

    $conn->commit();

    unset($_SESSION['cart']);

    echo json_encode(['ok' => true, 'order_no' => $orderNo]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}