<?php
session_start();
$items = array_values($_SESSION['cart'] ?? []);
$total = 0; $count = 0;
foreach($items as $it){ $total += $it['price'] * $it['qty']; $count += $it['qty']; }
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['items'=>$items, 'total'=>$total, 'count'=>$count]);
