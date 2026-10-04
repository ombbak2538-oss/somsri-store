<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$zone_id = intval($_POST['zone_id'] ?? 0);
$total = floatval($_POST['total'] ?? 0);

$shippingFee = 0;
if ($zone_id) {
  $stmt = $conn->prepare("SELECT fee FROM shipping_zones WHERE id=?");
  $stmt->bind_param("i", $zone_id);
  $stmt->execute();
  $res = $stmt->get_result()->fetch_assoc();
  $shippingFee = $res['fee'] ?? 0;
}

$_SESSION['shipping_zone_id'] = $zone_id;

echo json_encode([
  'shipping' => $shippingFee,
  'grand' => $total + $shippingFee
]);
