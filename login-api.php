<?php
session_start();
require_once __DIR__."/../config/db.php";

$u = $_POST['username'] ?? '';
$p = $_POST['password'] ?? '';

$stmt = $conn->prepare("SELECT id,password FROM users WHERE username=? LIMIT 1");
$stmt->bind_param("s",$u);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

if($me && password_verify($p,$me['password'])){
  $_SESSION['user_id'] = $me['id'];
  echo json_encode(['ok'=>true]);
}else{
  echo json_encode(['ok'=>false,'msg'=>'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
}
