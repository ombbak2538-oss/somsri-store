<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (empty($_SESSION['cart'])) {
  header("Location: /somsri-store/public/index.php");
  exit;
}

if (empty($_SESSION['user_id'])) {
  header("Location: /somsri-store/public/login.php?redirect=/somsri-store/public/set-checkout.php");
  exit;
}

$uid = (int)$_SESSION['user_id'];
$me = $conn->query("SELECT fullname, phone, address FROM users WHERE id=$uid")->fetch_assoc();

$profileIncomplete = empty($me['fullname']) || empty($me['phone']) || empty($me['address']);
if ($profileIncomplete) {
  header("Location: /somsri-store/public/account.php?tab=profile&redirect=/somsri-store/public/set-checkout.php");
  exit;
}

header("Location: /somsri-store/public/checkout.php");
exit;
