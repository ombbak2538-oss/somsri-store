<?php
include __DIR__.'/_guard.php';
include __DIR__ . '/../../config/db.php';

$orderId = (int)($_GET['id'] ?? 0);
$order = $conn->query("SELECT * FROM orders WHERE id=$orderId")->fetch_assoc();
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$orderId");
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#ff4d6d">
  <title>รายละเอียดออเดอร์ #<?= $orderId ?></title>
</head>
<body class="container py-4">
  <h4>ออเดอร์ #<?= $orderId ?></h4>
  <p>ยอดรวม: <b><?= number_format($order['total'],2) ?> บาท</b></p>

  <table class="table table-sm">
    <tr><th>สินค้า</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr>
    <?php while($it = $items->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($it['name']) ?></td>
        <td><?= number_format($it['price'],2) ?></td>
        <td><?= (int)$it['qty'] ?></td>
        <td><?= number_format($it['price'] * $it['qty'],2) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>

  <a class="btn btn-secondary" href="orders.php">← กลับ</a>
</body>
</html>
