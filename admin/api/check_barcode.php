<?php
require_once __DIR__ . "/../../config/db.php";
header('Content-Type: application/json');

$barcode = trim($_GET['barcode'] ?? '');

if ($barcode === '') {
  echo json_encode(['found' => false]);
  exit;
}

$stmt = $conn->prepare("SELECT id, name, price, stock, image FROM products WHERE barcode = ? LIMIT 1");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  echo json_encode([
    'found' => true,
    'product' => $row
  ]);
} else {
  echo json_encode(['found' => false]);
}
