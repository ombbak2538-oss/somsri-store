<?php
session_start();
require_once __DIR__ . "/../../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
  header("Location: /somsri-store/public/admin/login.php");
  exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
if ($orderId <= 0) die("ไม่พบ order_id");

$conn->begin_transaction();

try {

  // ล็อกออเดอร์
  $order = $conn->query("
    SELECT * FROM orders 
    WHERE id = $orderId 
    FOR UPDATE
  ")->fetch_assoc();

  if (!$order || $order['status'] !== 'CONVERTED') {
    throw new Exception("ออเดอร์นี้ยังไม่ได้แปลง หรือถูกยกเลิกไปแล้ว");
  }

  // หาบิลขายล่าสุดของออเดอร์นี้
  $sale = $conn->query("
    SELECT id FROM sales 
    WHERE order_id = $orderId 
    ORDER BY id DESC LIMIT 1
  ")->fetch_assoc();

  if (!$sale) {
    throw new Exception("ไม่พบบิล POS ที่ผูกกับออเดอร์นี้");
  }

  $saleId = (int)$sale['id'];

  // ดึง sale_items
  $items = $conn->query("
    SELECT * FROM sale_items WHERE sale_id = $saleId
  ");

  while ($i = $items->fetch_assoc()) {
    // คืนสต๊อก
    $stmt = $conn->prepare("
      UPDATE products 
      SET stock = stock + ? 
      WHERE id = ?
    ");
    $stmt->bind_param("ii", $i['qty'], $i['product_id']);
    $stmt->execute();
  }

  // ลบ sale_items + sales
  $conn->query("DELETE FROM sale_items WHERE sale_id = $saleId");
  $conn->query("DELETE FROM sales WHERE id = $saleId");

  // ย้อนสถานะออเดอร์
  $conn->query("
    UPDATE orders 
    SET status = 'PENDING' 
    WHERE id = $orderId
  ");

  $conn->commit();
  header("Location: index.php?cancel_ok=1");

} catch (Exception $e) {
  $conn->rollback();
  echo "❌ ยกเลิกไม่สำเร็จ: " . $e->getMessage();
}
