<?php
include __DIR__ . "/auth.php";
include __DIR__ . "/../config/db.php";
$id = $_GET['id'] ?? 0;

$order = $conn->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();
$items = $conn->query("
  SELECT oi.qty, oi.price, p.name
  FROM order_items oi
  JOIN products p ON oi.product_id = p.id
  WHERE oi.order_id=$id
");
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รายละเอียดออเดอร์ #<?= $id ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<h4>ออเดอร์ #<?= $id ?></h4>
<div class="mb-2">
  <b>ลูกค้า:</b> <?= htmlspecialchars($order['customer_name']) ?><br>
  <b>เบอร์:</b> <?= htmlspecialchars($order['phone']) ?><br>
  <b>ที่อยู่:</b> <?= nl2br(htmlspecialchars($order['address'])) ?><br>
</div>

<table class="table">
  <tr>
    <th>สินค้า</th>
    <th>ราคา</th>
    <th>จำนวน</th>
    <th>รวม</th>
  </tr>
  <?php $sum=0; while($i = $items->fetch_assoc()):
    $rowTotal = $i['price'] * $i['qty'];
    $sum += $rowTotal;
  ?>
  <tr>
    <td><?= htmlspecialchars($i['name']) ?></td>
    <td><?= number_format($i['price'],2) ?></td>
    <td><?= $i['qty'] ?></td>
    <td><?= number_format($rowTotal,2) ?></td>
  </tr>
  <?php endwhile; ?>
  <tr>
    <th colspan="3">รวมทั้งหมด</th>
    <th><?= number_format($sum,2) ?> บาท</th>
  </tr>
</table>

<a href="orders.php" class="btn btn-secondary">← กลับ</a>

</body>
</html>
