<?php
date_default_timezone_set('Asia/Bangkok');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
  die('Not logged in');
}

$orderId = (int)($_POST['order_id'] ?? 0);
$userId  = (int)$_SESSION['admin_id'];

if (!$orderId) die('Invalid order');

$conn->begin_transaction();

try {
  // ดึงออเดอร์
  $o = $conn->query("SELECT * FROM orders WHERE id={$orderId} FOR UPDATE")->fetch_assoc();
  if (!$o) throw new Exception('ไม่พบออเดอร์');
  if ($o['status'] === 'CONVERTED') throw new Exception('ออเดอร์นี้ถูกแปลงแล้ว');

  // ดึงรายการสินค้า
  $items = $conn->query("
    SELECT oi.product_id, oi.qty, p.price 
    FROM order_items oi 
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = {$orderId}
  ");

  if ($items->num_rows === 0) throw new Exception('ไม่มีรายการสินค้า');

  // คำนวณยอดรวม
  $total = 0;
  $rows = [];
  while ($i = $items->fetch_assoc()) {
    $sub = $i['price'] * $i['qty'];
    $total += $sub;
    $rows[] = $i;
  }

  // สร้างบิล POS
  $stmt = $conn->prepare("
    INSERT INTO sales (total, payment_method, paid_amount, change_amount, created_by, created_at)
    VALUES (?,?,?,?,?,NOW())
  ");
  $payment = 'TRANSFER'; // เว็บ = โอน / พร้อมเพย์
  $paid = $total;
  $change = 0;
  $stmt->bind_param("dsddi", $total, $payment, $paid, $change, $userId);
  $stmt->execute();
  $saleId = $stmt->insert_id;

  // เพิ่ม sale_items + ตัดสต็อก
  foreach ($rows as $r) {
    $stmt2 = $conn->prepare("
      INSERT INTO sale_items (sale_id, product_id, qty, price)
      VALUES (?,?,?,?)
    ");
    $stmt2->bind_param("iiid", $saleId, $r['product_id'], $r['qty'], $r['price']);
    $stmt2->execute();

    $stmt3 = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
    $stmt3->bind_param("ii", $r['qty'], $r['product_id']);
    $stmt3->execute();
  }

  // อัปเดตสถานะออเดอร์
  $conn->query("UPDATE orders SET status='CONVERTED' WHERE id={$orderId}");

  $conn->commit();

  header("Location: /somsri-store/public/admin/reports/sales_list.php?success=1");
  exit;

} catch (Exception $e) {
  $conn->rollback();
  echo "❌ แปลงออเดอร์ไม่สำเร็จ: " . $e->getMessage();
}
