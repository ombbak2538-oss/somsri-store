<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$userId   = $_SESSION['user_id'];
$old      = $_POST['old_password'] ?? '';
$new      = $_POST['new_password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';

if ($new !== $confirm) {
  header("Location: account.php?tab=password&err=notmatch");
  exit;
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$hash = $stmt->get_result()->fetch_assoc()['password'] ?? '';

if (!password_verify($old, $hash)) {
  header("Location: account.php?tab=password&err=oldwrong");
  exit;
}

$newHash = password_hash($new, PASSWORD_DEFAULT);
$up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$up->bind_param("si", $newHash, $userId);
$up->execute();

header("Location: account.php?tab=password&saved=1");
exit;
