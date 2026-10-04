<?php
session_start();
include __DIR__ . "/../config/db.php";

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$diff = (int)($_GET['diff'] ?? 0);
$_SESSION['cart'] = $_SESSION['cart'] ?? [];

function summary(){
  $items = array_values($_SESSION['cart']);
  $total = 0; $count = 0;
  foreach($items as $it){ $total += $it['price'] * $it['qty']; $count += $it['qty']; }
  return ['items'=>$items, 'total'=>$total, 'count'=>$count];
}

if($action === 'add' && $id){
  global $conn;
  $stmt = $conn->prepare("SELECT id, name, price, image, discount_percent FROM products WHERE id=? LIMIT 1");
  $stmt->bind_param("i",$id); $stmt->execute();
  $p = $stmt->get_result()->fetch_assoc();
  if(!$p){ echo json_encode(['ok'=>false]); exit; }

  $price = (float)$p['price'];
  if((int)$p['discount_percent'] > 0){
    $price = $price * (100 - (int)$p['discount_percent']) / 100;
  }

  if(isset($_SESSION['cart'][$id])){
    $_SESSION['cart'][$id]['qty'] += 1;
  }else{
    $_SESSION['cart'][$id] = [
      'id'=>$id,'name'=>$p['name'],'price'=>$price,
      'image'=>$p['image'],'qty'=>1
    ];
  }
  echo json_encode(['ok'=>true] + summary()); exit;
}

if($action === 'update' && $id){
  if(isset($_SESSION['cart'][$id])){
    $_SESSION['cart'][$id]['qty'] += $diff;
    if($_SESSION['cart'][$id]['qty'] <= 0) unset($_SESSION['cart'][$id]);
  }
  echo json_encode(['ok'=>true] + summary()); exit;
}

if($action === 'remove' && $id){
  unset($_SESSION['cart'][$id]);
  echo json_encode(['ok'=>true] + summary()); exit;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok'=>false]);
