<?php
$pageTitle = "จัดการสาขา";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
  $name = trim($_POST['name']);
  if ($name !== '') {
    $stmt = $conn->prepare("INSERT INTO branches (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
  }
  header("Location: branches.php");
  exit;
}

if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $conn->query("DELETE FROM branches WHERE id = $id");
  header("Location: branches.php");
  exit;
}

$branches = $conn->query("SELECT * FROM branches ORDER BY id ASC");
?>

<main class="content">
  <div class="topbar mb-3 d-flex align-items-center gap-2">
    <strong>🏬 จัดการสาขา</strong>
  </div>

  <div class="card-soft p-3 mb-3">
    <form method="post" class="d-flex gap-2">
      <input type="text" name="name" class="form-control" placeholder="ชื่อสาขา เช่น คลังหลัก" required>
      <button class="btn btn-danger btn-soft">➕ เพิ่มสาขา</button>
    </form>
  </div>

  <div class="card-soft p-3">
    <table class="table table-bordered align-middle text-center mb-0">
      <thead class="table-light">
        <tr>
          <th width="80">#</th>
          <th>ชื่อสาขา</th>
          <th width="120">จัดการ</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($branches && $branches->num_rows): ?>
          <?php while($b = $branches->fetch_assoc()): ?>
            <tr>
              <td><?= (int)$b['id'] ?></td>
              <td><?= htmlspecialchars($b['name']) ?></td>
              <td>
                <a href="?delete=<?= (int)$b['id'] ?>" 
                   onclick="return confirm('ยืนยันลบสาขานี้?')" 
                   class="btn btn-sm btn-danger">🗑 ลบ</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" class="text-muted py-4">ยังไม่มีสาขา</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>