<?php
$pageTitle = "สต็อกแยกตามสาขา";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['branch_id'], $_POST['product_id'], $_POST['qty'])) {
  $branchId  = (int)$_POST['branch_id'];
  $productId = (int)$_POST['product_id'];
  $qty       = (int)$_POST['qty'];

  $stmt = $conn->prepare("
    INSERT INTO branch_stocks (branch_id, product_id, qty)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE qty = VALUES(qty)
  ");
  $stmt->bind_param("iii", $branchId, $productId, $qty);
  $stmt->execute();

  header("Location: branch_stocks.php");
  exit;
}

$branches = $conn->query("SELECT id, name FROM branches ORDER BY id ASC");
$products = $conn->query("SELECT id, name FROM products ORDER BY name ASC");

$stocks = $conn->query("
  SELECT 
    b.id AS branch_id,
    b.name AS branch_name,
    p.id AS product_id,
    p.name AS product_name,
    COALESCE(bs.qty, 0) AS qty
  FROM branches b
  CROSS JOIN products p
  LEFT JOIN branch_stocks bs 
    ON bs.branch_id = b.id AND bs.product_id = p.id
  ORDER BY b.id, p.name
");
?>

<main class="content">
  <div class="topbar mb-3 d-flex align-items-center gap-2">
    <strong>📊 สต็อกแต่ละสาขา</strong>
  </div>

  <div class="card-soft p-3 mb-3">
    <form method="post" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label">สาขา</label>
        <select name="branch_id" class="form-select" required>
          <?php while($b = $branches->fetch_assoc()): ?>
            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">สินค้า</label>
        <select name="product_id" class="form-select" required>
          <?php while($p = $products->fetch_assoc()): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">จำนวน</label>
        <input type="number" name="qty" class="form-control" min="0" required>
      </div>
      <div class="col-md-2">
        <button class="btn btn-danger btn-soft w-100">บันทึก</button>
      </div>
    </form>
  </div>

  <div class="card-soft p-3 table-responsive">
    <table class="table table-bordered align-middle text-center mb-0">
      <thead class="table-light">
        <tr>
          <th>สาขา</th>
          <th>สินค้า</th>
          <th width="120">คงเหลือ</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($stocks && $stocks->num_rows): ?>
          <?php while($s = $stocks->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($s['branch_name']) ?></td>
              <td class="text-start"><?= htmlspecialchars($s['product_name']) ?></td>
              <td class="fw-semibold"><?= (int)$s['qty'] ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="text-muted py-4">ยังไม่มีข้อมูลสต็อก</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>