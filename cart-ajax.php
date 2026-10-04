<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$id  = (int)($_POST['id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

if ($id > 0) {
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stock = $res ? (int)$res['stock'] : 0;

    $final_qty = max(0, min($qty, $stock));

    if ($final_qty <= 0) {
        unset($_SESSION['cart'][$id]); 
    } else {
        $_SESSION['cart'][$id] = $final_qty;
    }

    $total_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
    
    echo json_encode([
        'status' => 'ok',
        'count' => $total_count,
        'actual_qty' => $final_qty
    ]);
} else {
    echo json_encode(['status' => 'error']);
}