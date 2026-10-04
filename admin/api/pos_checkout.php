<?php
date_default_timezone_set('Asia/Bangkok');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../../../config/db.php";

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['items'])) {
  echo json_encode(['ok'=>false,'msg'=>'ไม่มีรายการสินค้า']); exit;
}

$userId = $_SESSION['admin_id'] ?? null;
if (!$userId) {
  echo json_encode(['ok'=>false,'msg'=>'ยังไม่ได้ล็อกอิน']); exit;
}

$items = $data['items'];
$payment_method = $data['payment_method'] ?? 'CASH';
$paid = (float)($data['paid_amount'] ?? 0);

$total = 0;
$products = [];

foreach ($items as $i) {
  $stmt = $conn->prepare("SELECT id, price, stock FROM products WHERE barcode=? LIMIT 1");
  $stmt->bind_param("s", $i['barcode']);
  $stmt->execute();
  $p = $stmt->get_result()->fetch_assoc();

  if (!$p) {
    echo json_encode(['ok'=>false,'msg'=>'ไม่พบสินค้า barcode: '.$i['barcode']]);
    exit;
  }

  if ($p['stock'] < $i['qty']) {
    echo json_encode(['ok'=>false,'msg'=>'สต็อกไม่พอสำหรับสินค้า ID '.$p['id']]);
    exit;
  }

  $sub = $p['price'] * (int)$i['qty'];
  $total += $sub;

  $products[] = [
    'id' => $p['id'],
    'price' => $p['price'],
    'qty' => (int)$i['qty']
  ];
}

$change = max(0, $paid - $total);

// บันทึก sales
$stmt = $conn->prepare("
  INSERT INTO sales (total, payment_method, paid_amount, change_amount, created_by, created_at)
  VALUES (?,?,?,?,?,NOW())
");
$stmt->bind_param("dsddi", $total, $payment_method, $paid, $change, $userId);
$stmt->execute();

$saleId = $stmt->insert_id;

// บันทึก sale_items + ตัดสต็อก
foreach ($products as $p) {
  $stmt2 = $conn->prepare("
    INSERT INTO sale_items (sale_id, product_id, qty, price) 
    VALUES (?,?,?,?)
  ");
  $stmt2->bind_param("iiid", $saleId, $p['id'], $p['qty'], $p['price']);
  $stmt2->execute();

  $stmt3 = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
  $stmt3->bind_param("ii", $p['qty'], $p['id']);
  $stmt3->execute();
}

echo json_encode([
  'ok'=>true,
  'sale_id'=>$saleId,
  'total'=>$total,
  'paid'=>$paid,
  'change'=>$change
]);
