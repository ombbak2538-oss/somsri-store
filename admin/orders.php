<?php
session_start();
require_once __DIR__ . "/../../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
  http_response_code(403);
  exit("Unauthorized");
}

$orderId = (int)($_POST['order_id'] ?? 0);
if ($orderId <= 0) {
  exit("Invalid order_id");
}

$conn->begin_transaction();

try {
  $orderRes = $conn->prepare("
    SELECT * 
    FROM orders 
    WHERE id = ? 
    FOR UPDATE
  ");
  $orderRes->bind_param("i", $orderId);
  $orderRes->execute();
  $order = $orderRes->get_result()->fetch_assoc();

  if (!$order) {
    throw new Exception("ไม่พบออเดอร์");
  }

  if (($order['status'] ?? '') === 'แปลงเป็นบิล POS แล้ว') {
    throw new Exception("ออเดอร์นี้ถูกแปลงเป็นบิล POS ไปแล้ว");
  }

  $itemRes = $conn->prepare("
    SELECT oi.product_id, oi.qty, oi.price, p.name 
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
  ");
  $itemRes->bind_param("i", $orderId);
  $itemRes->execute();
  $items = $itemRes->get_result();

  if ($items->num_rows === 0) {
    throw new Exception("ออเดอร์นี้ไม่มีรายการสินค้า (order_items ว่าง)");
  }

  $stmt = $conn->prepare("
    INSERT INTO pos_orders (customer_name, customer_phone, source_order_id, status, created_at)
    VALUES (?, ?, ?, 'paid', NOW())
  ");
  $stmt->bind_param("ssi", $order['customer_name'], $order['customer_phone'], $orderId);
  $stmt->execute();

  $posOrderId = $conn->insert_id;

  $insItem = $conn->prepare("
    INSERT INTO pos_items (pos_order_id, product_id, product_name, qty, price)
    VALUES (?, ?, ?, ?, ?)
  ");

  while ($it = $items->fetch_assoc()) {
    $insItem->bind_param(
      "iisid",
      $posOrderId,
      $it['product_id'],
      $it['name'],
      $it['qty'],
      $it['price']
    );
    $insItem->execute();
  }

  $upd = $conn->prepare("UPDATE orders SET status = 'แปลงเป็นบิล POS แล้ว' WHERE id = ?");
  $upd->bind_param("i", $orderId);
  $upd->execute();

  $conn->commit();

  header("Location: ../orders.php?converted=1");
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  http_response_code(400);
  echo "เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage());
  exit;
}
