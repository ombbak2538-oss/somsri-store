<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);

$_SESSION['checkout_shipping'] = (float)($data['shipping'] ?? 0);
$_SESSION['checkout_discount'] = (float)($data['discount'] ?? 0);

echo json_encode(['ok'=>true]);
