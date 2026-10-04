<?php
require_once __DIR__ . "/../config/db.php";

$username = 'admin';
$newPass  = '123456';

$hash = password_hash($newPass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password=?, role='OWNER' WHERE username=?");
$stmt->bind_param("ss", $hash, $username);
$stmt->execute();

echo "รีเซ็ตรหัสผ่านแอดมินเรียบร้อยแล้ว";
