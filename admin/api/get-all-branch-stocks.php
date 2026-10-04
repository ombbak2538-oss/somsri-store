<?php
require_once __DIR__ . "/../../../config/db.php";
session_start();

if (!isset($_SESSION['admin_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'unauthorized']);
  exit;
}

$productId = (int)($_GET['product_id'] ?? 0);
if ($productId <= 0) {
  echo json_encode([]);
  exit;
}

$sql = "
  SELECT 
    b.id AS branch_id,
    b.name AS branch_name,
    COALESCE(bs.qty, 0) AS qty
  FROM branches b
  LEFT JOIN branch_stocks bs 
    ON bs.branch_id = b.id AND bs.product_id = ?
  ORDER BY b.id
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[] = [
    'branch_id' => (int)$row['branch_id'],
    'branch_name' => $row['branch_name'],
    'qty' => (int)$row['qty']
  ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);