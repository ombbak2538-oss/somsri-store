<?php
session_start();
$zone_id = intval($_POST['shipping_zone_id'] ?? 0);
if($zone_id > 0) $_SESSION['shipping_zone_id'] = $zone_id;
else unset($_SESSION['shipping_zone_id']);
header("Location: cart.php");
exit;