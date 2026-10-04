<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  header("Location: cart.php");
  exit;
}

$userId = $_SESSION['user_id'] ?? null;

$name   = trim($_POST['fullname'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$addr   = trim($_POST['address'] ?? '');
$pm     = $_POST['payment_method'] ?? 'cod';

if ($name === '' || $phone === '' || $addr === '') {
  header("Location: cart.php?error=missing_info");
  exit;
}

$ids = implode(",", array_map('intval', array_keys($cart)));
$rs = $conn->query("SELECT id, price FROM products WHERE id IN ($ids)");

$total = 0;
$priceMap = [];
while($r = $rs->fetch_assoc()){
  $pid = (int)$r['id'];
  $priceMap[$pid] = (float)$r['price'];
  $total += $priceMap[$pid] * (int)$cart[$pid];
}

$shippingFee = 0;
$zoneId = $_SESSION['shipping_zone_id'] ?? 0;
if ($zoneId) {
  $z = $conn->prepare("SELECT fee FROM shipping_zones WHERE id=?");
  $z->bind_param("i", $zoneId);
  $z->execute();
  $shippingFee = (float)($z->get_result()->fetch_assoc()['fee'] ?? 0);
}

$grandTotal = $total + $shippingFee;
$orderNo = 'SS' . date('Ymd') . '-' . rand(1000, 9999);
$slipName = null;
if (!empty($_FILES['slip']['name'])) {
  $ext = strtolower(pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION));
  $allow = ['jpg','jpeg','png','webp'];
  if (!in_array($ext, $allow)) {
    die('ไฟล์สลิปไม่ถูกต้อง');
  }
  $slipName = 'slip_' . time() . '.' . $ext;
  $dir = __DIR__ . "/uploads/slips/";
  if (!is_dir($dir)) mkdir($dir, 0755, true);
  move_uploaded_file($_FILES['slip']['tmp_name'], $dir . $slipName);
}

$conn->begin_transaction();

try {

  $stmt = $conn->prepare("
    INSERT INTO orders 
      (order_no, user_id, customer_name, customer_phone, payment_method, total_amount, address, status, created_at, status_updated_at, shipping_fee, slip_image)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'PENDING', NOW(), NOW(), ?, ?)
  ");

$stmt->bind_param(
  "sisssdssd",
  $orderNo,     // s
  $userId,      // i
  $name,        // s
  $phone,       // s
  $pm,          // s
  $grandTotal,  // d
  $addr,        // s
  $shippingFee, // d
  $slipName     // s
);



  $stmt->execute();
  $orderId = $stmt->insert_id;

  $itemStmt = $conn->prepare("
    INSERT INTO order_items (order_id, product_id, qty, price)
    VALUES (?, ?, ?, ?)
  ");

  foreach ($cart as $pid => $qty) {
    $pid = (int)$pid;
    $qty = (int)$qty;
    $price = (float)$priceMap[$pid];

    $itemStmt->bind_param("iiid", $orderId, $pid, $qty, $price);
    $itemStmt->execute();
  }

  unset($_SESSION['cart'], $_SESSION['shipping_zone_id']);
  $conn->commit();

  header("Location: success.php?order_no=" . urlencode($orderNo));
  exit;

} catch (Throwable $e) {
  $conn->rollback();
  echo "ERROR: " . $e->getMessage();
}
