<?php
require_once __DIR__ . "/../../config/db.php";

$user = "admin";
$pass = password_hash("123456", PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $user, $pass);
$stmt->execute();

echo "สร้างแอดมินเรียบร้อย: admin / 123456";
