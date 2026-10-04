<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$order = $_POST['order_code'] ?? '';
$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($order==='' || $productId<=0 || $rating<1 || $rating>5) {
  die('ข้อมูลไม่ครบ');
}

$stmt = $conn->prepare("
  INSERT INTO reviews (order_code, product_id, rating, comment, created_at)
  VALUES (?,?,?,?,NOW())
");
$stmt->bind_param("siis", $order, $productId, $rating, $comment);
$stmt->execute();

header("Location: /somsri-store/public/reviews.php?order=" . urlencode($order));
exit;
