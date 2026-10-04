<?php
$pageTitle = "รายละเอียดออเดอร์";
require __DIR__ . "/../partials/header.php";
require __DIR__ . "/../partials/sidebar.php";
require_once __DIR__ . "/../../../config/db.php";

$orderId = (int)($_GET['id'] ?? 0);

$order = $conn->query("SELECT * FROM orders WHERE id=$orderId")->fetch_assoc();
if(!$order){
  echo "ไม่พบออเดอร์"; exit;
}

$items = $conn->query("
  SELECT oi.*, p.name, p.image 
  FROM order_items oi
  JOIN products p ON p.id = oi.product_id
  WHERE oi.order_id = $orderId
");

$total = 0;
?>

<main class="content p-3">
  <h4>📦 ออเดอร์ #<?= htmlspecialchars($order['order_no']) ?></h4>
  <p>
    ลูกค้า: <b><?= htmlspecialchars($order['customer_name']) ?></b><br>
    เบอร์: <?= htmlspecialchars($order['customer_phone']) ?><br>
    สถานะ: <span class="badge bg-info"><?= htmlspecialchars($order['status']) ?></span>
  </p>

  <div class="card-soft p-3">
    <table class="table align-middle">
      <thead>
        <tr>
          <th>รูป</th>
          <th>สินค้า</th>
          <th class="text-end">ราคา</th>
          <th class="text-center">จำนวน</th>
          <th class="text-end">รวม</th>
        </tr>
      </thead>
      <tbody>
        <?php while($i = $items->fetch_assoc()):
          $sum = $i['price'] * $i['qty'];
          $total += $sum;
        ?>
        <tr>
          <td width="60">
            <img src="/somsri-store/public/uploads/products/<?= htmlspecialchars($i['image']) ?>" 
                 style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
          </td>
          <td><?= htmlspecialchars($i['name']) ?></td>
          <td class="text-end"><?= number_format($i['price'],2) ?></td>
          <td class="text-center"><?= (int)$i['qty'] ?></td>
          <td class="text-end fw-semibold"><?= number_format($sum,2) ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="4" class="text-end">รวมทั้งหมด</th>
          <th class="text-end text-danger fs-5"><?= number_format($total,2) ?> บาท</th>
        </tr>
      </tfoot>
    </table>
  </div>

  <a href="index.php" class="btn btn-secondary mt-3">← กลับ</a>
</main>

<?php require __DIR__ . "/../partials/footer.php"; ?>
