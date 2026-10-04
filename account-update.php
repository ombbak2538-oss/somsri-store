<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$userId  = (int)$_SESSION['user_id'];
$fullname = trim($_POST['fullname'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$email    = trim($_POST['email'] ?? '');
$birthday = $_POST['birthday'] ?? null;
$address  = trim($_POST['address'] ?? '');

$avatarName = null;

if (!empty($_FILES['avatar']['name'])) {
  $uploadDir = __DIR__ . "/uploads/avatars/";
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }

  $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
  $allowed = ['jpg','jpeg','png','webp'];

  if (!in_array($ext, $allowed)) {
    header("Location: account.php?tab=profile&err=type");
    exit;
  }

  if ($_FILES['avatar']['size'] > 3 * 1024 * 1024) {
    header("Location: account.php?tab=profile&err=size");
    exit;
  }

  $avatarName = uniqid('ava_') . '.' . $ext;
  move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarName);
}

if ($avatarName) {
  $stmt = $conn->prepare("
    UPDATE users 
    SET fullname=?, phone=?, email=?, birthday=?, address=?, avatar=? 
    WHERE id=?
  ");
  $stmt->bind_param("ssssssi", $fullname, $phone, $email, $birthday, $address, $avatarName, $userId);
} else {
  $stmt = $conn->prepare("
    UPDATE users 
    SET fullname=?, phone=?, email=?, birthday=?, address=? 
    WHERE id=?
  ");
  $stmt->bind_param("sssssi", $fullname, $phone, $email, $birthday, $address, $userId);
}

$stmt->execute();

header("Location: account.php?tab=profile&saved=1");
exit;