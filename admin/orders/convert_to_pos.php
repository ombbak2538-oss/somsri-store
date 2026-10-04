<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: /somsri-store/public/admin/login.php");
  exit;
}

require_once __DIR__ . "/../../../config/db.php";

$orderId = (int)($_GET['order_id'] ?? $_GET['id'] ?? $_POST['order_id'] ?? 0);
$userId  = (int)$_SESSION['admin_id'];

if ($orderId <= 0) {
  die("ไม่พบออเดอร์");
}

$conn->begin_transaction();

try {
  // 1) ดึงออเดอร์ + ล็อกแถว (กันกดซ้ำ/แข่งกัน)
  $stmtO = $conn->prepare("
    SELECT * FROM orders WHERE id = ? FOR UPDATE
  ");
  $stmtO->bind_param("i", $orderId);
  $stmtO->execute();
  $order = $stmtO->get_result()->fetch_assoc();

  if (!$order) {
    throw new Exception("ไม่พบออเดอร์");
  }
  if (($order['status'] ?? '') === 'DONE') {
    throw new Exception("ออเดอร์นี้ถูกแปลงเป็นบิลแล้ว");
  }

  // 2) ดึงรายการสินค้า + ล็อกสต๊อกสินค้า
  $stmtI = $conn->prepare("
    SELECT oi.product_id, oi.qty, oi.price, p.stock, p.name
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id = ?
    FOR UPDATE
  ");
  $stmtI->bind_param("i", $orderId);
  $stmtI->execute();
  $items = $stmtI->get_result();

  if ($items->num_rows === 0) {
    throw new Exception("ออเดอร์นี้ไม่มีสินค้า");
  }

  // 3) เช็กสต๊อกก่อน
  $total = 0;
  $rows = [];
  while ($i = $items->fetch_assoc()) {
    if ((int)$i['stock'] < (int)$i['qty']) {
      throw new Exception("❌ สต๊อกสินค้าไม่พอ: {$i['name']} (คงเหลือ {$i['stock']})");
    }
    $total += ($i['qty'] * $i['price']);
    $rows[] = $i; // เก็บไว้ใช้ต่อ ไม่ต้อง query ซ้ำ
  }

  // 4) สร้างบิล POS
  $stmtS = $conn->prepare("
    INSERT INTO sales (total, payment_method, paid_amount, change_amount, created_by, created_at, source_order_id)
    VALUES (?,?,?,?,?,NOW(),?)
  ");
  $paymentMethod = $order['payment_method'] ?: 'TRANSFER';
  $paid   = $total;
  $change = 0;
  $stmtS->bind_param("dsddii", $total, $paymentMethod, $paid, $change, $userId, $orderId);
  $stmtS->execute();
  $saleId = $conn->insert_id;

  // 5) เพิ่ม sale_items + ตัดสต๊อก (atomic)
  $stmtSI = $conn->prepare("
    INSERT INTO sale_items (sale_id, product_id, qty, price) 
    VALUES (?,?,?,?)
  ");
  $stmtStock = $conn->prepare("
    UPDATE products 
    SET stock = stock - ? 
    WHERE id = ? AND stock >= ?
  ");

  foreach ($rows as $i) {
    $stmtSI->bind_param("iiid", $saleId, $i['product_id'], $i['qty'], $i['price']);
    $stmtSI->execute();

    $stmtStock->bind_param("iii", $i['qty'], $i['product_id'], $i['qty']);
    $stmtStock->execute();

    if ($stmtStock->affected_rows === 0) {
      throw new Exception("สต๊อกไม่พอสำหรับสินค้า ID {$i['product_id']}");
    }
  }

  // 6) อัปเดตสถานะออเดอร์เว็บ + ผูก sale_id
  $upd = $conn->prepare("
    UPDATE orders SET status='DONE', total_amount=?, sale_id=? WHERE id=?
  ");
  $upd->bind_param("dii", $total, $saleId, $orderId);
  $upd->execute();

  $conn->commit();

  header("Location: /somsri-store/public/admin/orders/index.php?ok=1");
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  echo "❌ แปลงออเดอร์ไม่สำเร็จ: " . htmlspecialchars($e->getMessage());
}
