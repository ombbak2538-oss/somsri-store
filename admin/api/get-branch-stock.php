<?php
require_once __DIR__ . "/../../../config/db.php";
session_start();

if (!isset($_SESSION['admin_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'unauthorized']);
  exit;
}

$branchId  = (int)($_SESSION['branch_id'] ?? 1);
$productId = (int)($_GET['product_id'] ?? 0);

if ($productId <= 0) {
  echo json_encode(['qty' => 0]);
  exit;
}

$stmt = $conn->prepare("
  SELECT COALESCE(qty, 0) AS qty
  FROM branch_stocks
  WHERE branch_id = ? AND product_id = ?
  LIMIT 1
");
$stmt->bind_param("ii", $branchId, $productId);
$stmt->execute();
$stmt->bind_result($qty);
$stmt->fetch();
$stmt->close();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['qty' => (int)$qty]);