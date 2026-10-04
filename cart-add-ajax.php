<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$id  = intval($_POST['id'] ?? 0);
$qty = max(1, intval($_POST['qty'] ?? 1));

if ($id <= 0) {
  echo json_encode(['ok' => false]);
  exit;
}

if (!isset($_SESSION['cart'])) {
  $_SESSION['cart'] = [];
}

$_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;

$count = array_sum($_SESSION['cart']);

echo json_encode([
  'ok' => true,
  'count' => $count
]);