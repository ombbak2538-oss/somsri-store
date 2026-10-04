<?php
require_once __DIR__ . "/../../config/db.php";
header('Content-Type: application/json');

$barcode = trim($_POST['barcode'] ?? '');
$qty = intval($_POST['qty'] ?? 0);

if ($barcode === '' || $qty <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'ข้อมูลไม่ครบ']);
  exit;
}

$conn->begin_transaction();

$stmt = $conn->prepare("SELECT id, stock FROM products WHERE barcode = ? FOR UPDATE");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$res = $stmt->get_result();

if (!$p = $res->fetch_assoc()) {
  $conn->rollback();
  echo json_encode(['ok'=>false,'msg'=>'ไม่พบสินค้า']);
  exit;
}

$newStock = $p['stock'] + $qty;

$upd = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
$upd->bind_param("ii", $newStock, $p['id']);
$upd->execute();

$log = $conn->prepare("INSERT INTO stock_logs (product_id, qty, type, note) VALUES (?, ?, 'IN', 'รับเข้าโกดัง')");
$log->bind_param("ii", $p['id'], $qty);
$log->execute();

$conn->commit();

echo json_encode(['ok'=>true,'stock'=>$newStock]);
