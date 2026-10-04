<?php
require_once __DIR__ . "/../../config/db.php";
header('Content-Type: application/json');

$barcode = trim($_GET['barcode'] ?? '');

if ($barcode === '') {
  echo json_encode(['ok' => false, 'msg' => 'ไม่พบบาร์โค้ด']);
  exit;
}

$conn->begin_transaction();

$stmt = $conn->prepare("SELECT id, name, stock FROM products WHERE barcode = ? FOR UPDATE");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$res = $stmt->get_result();

if (!$row = $res->fetch_assoc()) {
  $conn->rollback();
  echo json_encode(['ok' => false, 'msg' => 'ไม่พบสินค้าในระบบ']);
  exit;
}

if ($row['stock'] <= 0) {
  $conn->rollback();
  echo json_encode(['ok' => false, 'msg' => 'สต๊อกหมดแล้ว']);
  exit;
}

$newStock = $row['stock'] - 1;

$upd = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
$upd->bind_param("ii", $newStock, $row['id']);
$upd->execute();

$conn->commit();

echo json_encode([
  'ok' => true,
  'name' => $row['name'],
  'stock' => $newStock
]);
