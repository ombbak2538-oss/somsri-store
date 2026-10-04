<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . "/../config/db.php";

if (empty($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    echo json_encode(['ok' => false, 'message' => 'Session expired or cart is empty']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'];
$payment_method = $_POST['payment_method'] ?? 'cod';
$shipping_fee = $_SESSION['shipping_fee'] ?? 0;
$discount_amount = $_SESSION['discount_amount'] ?? 0;

$conn->begin_transaction(); 

try {
    $total_price = 0;
    $order_items_data = [];

    foreach ($cart as $product_id => $qty) {
        $stmt = $conn->prepare("SELECT name, price, stock, discount_percent FROM products WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product || $product['stock'] < $qty) {
            throw new Exception("สินค้า {$product['name']} หมดหรือสต็อกไม่พอ");
        }

        $price = (float)$product['price'];
        $discount = (int)($product['discount_percent'] ?? 0);
        $final_unit_price = ($discount > 0) ? $price * (100 - $discount) / 100 : $price;
        
        $subtotal = $final_unit_price * $qty;
        $total_price += $subtotal;
        
        $order_items_data[] = [
            'id' => $product_id,
            'qty' => $qty,
            'unit_price' => $final_unit_price,
            'total' => $subtotal
        ];
    }

    $grand_total = max(0, $total_price - $discount_amount) + $shipping_fee;

    $slip_name = null;
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] == 0) {
        $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
        $slip_name = "SLIP_" . time() . "_" . uniqid() . "." . $ext;
        if (!is_dir("../uploads/slips/")) {
            mkdir("../uploads/slips/", 0777, true);
        }
        move_uploaded_file($_FILES['slip']['tmp_name'], "../uploads/slips/" . $slip_name);
    }

    $order_no = "ORD-" . strtoupper(bin2hex(random_bytes(4)));

    $stmt = $conn->prepare("INSERT INTO orders 
        (user_id, order_no, total_amount, total, shipping_fee, payment_method, slip_image, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING', NOW())");

    $stmt->bind_param("isdddss", 
        $user_id,
        $order_no,
        $total_price,
        $grand_total,
        $shipping_fee,
        $payment_method,
        $slip_name
    );
    $stmt->execute();
    $order_id = $conn->insert_id;

    $stmt_detail = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, total) VALUES (?, ?, ?, ?, ?)");
    $stmt_update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($order_items_data as $item) {
        $stmt_detail->bind_param("iiidd", $order_id, $item['id'], $item['qty'], $item['unit_price'], $item['total']);
        $stmt_detail->execute();

        $stmt_update_stock->bind_param("ii", $item['qty'], $item['id']);
        $stmt_update_stock->execute();
    }

    unset($_SESSION['cart']);
    $conn->commit();

    echo json_encode(['ok' => true, 'order_id' => $order_id, 'order_no' => $order_no]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}