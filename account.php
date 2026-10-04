<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$userId = (int)$_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'orders';

$stmt = $conn->prepare("
  SELECT username, fullname, phone, email, birthday, avatar, address 
  FROM users 
  WHERE id=?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$orders = [];
$q = $conn->prepare("
  SELECT id, order_no, total_amount, status, created_at
  FROM orders
  WHERE user_id = ?
  ORDER BY id DESC
");
$q->bind_param("i", $userId);
$q->execute();
$orders = $q->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>บัญชีของฉัน | ร้านสมศรี</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  body{ font-family:'Prompt',sans-serif; background:#f7f7fb; }
  .account-wrap{ padding:0 10px 40px; }
  .account-card{
    background: linear-gradient(180deg, #fff1f6, #ffe0ec);
    border-radius:16px;
    box-shadow:0 12px 28px rgba(200,59,119,.2);
    border:1px solid rgba(255,255,255,.9);
  }
  .avatar{
    width:110px;height:110px;border-radius:50%;object-fit:cover;
    border:3px solid #fff; box-shadow:0 6px 16px rgba(0,0,0,.15);
  }
  .tab-btn{
    border-radius:999px; padding:6px 12px; border:1px solid #f0a8c9;
    background:#fff; color:#8b2a57; font-weight:600; text-decoration:none;
  }
  .tab-btn.active{ background:#c83b77; color:#fff; border-color:#c83b77; }
  .tab-btn:hover{ filter:brightness(1.03); }
</style>
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<div class="container-fluid account-wrap mt-3">
  <div class="container">
    <div class="account-card p-3 p-md-4">

      <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
        <a href="?tab=orders" class="tab-btn <?= $tab==='orders'?'active':'' ?>">📦 ออเดอร์ของฉัน</a>
        <a href="?tab=profile" class="tab-btn <?= $tab==='profile'?'active':'' ?>">🧾 โปรไฟล์</a>
        <a href="?tab=password" class="tab-btn <?= $tab==='password'?'active':'' ?>">🔒 เปลี่ยนรหัสผ่าน</a>
        <a href="?tab=coupons" class="tab-btn <?= $tab==='coupons'?'active':'' ?>">🧧 คูปองของฉัน</a>
        <a href="logout.php" class="ms-auto btn btn-outline-danger btn-sm">🚪 ออกจากระบบ</a>
      </div>

      <?php if($tab==='orders'): ?>
        <h5 class="fw-bold mb-2">📦 ออเดอร์ของฉัน</h5>
        <div class="table-responsive">
          <table class="table table-sm align-middle bg-white rounded">
            <thead>
              <tr>
                <th>#</th>
                <th>เลขที่ออเดอร์</th>
                <th>ยอดรวม</th>
                <th>สถานะ</th>
                <th>วันที่</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($orders)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีออเดอร์ของคุณ</td></tr>
              <?php else: foreach($orders as $i=>$o): ?>
                <tr>
                  <td><?= $i+1 ?></td>
                  <td>#<?= htmlspecialchars($o['order_no']) ?></td>
                  <td>฿<?= number_format($o['total_amount'],2) ?></td>
                  <td>
                    <span class="badge <?= $o['status']==='DONE'?'bg-success':'bg-warning text-dark' ?>">
                      <?= htmlspecialchars($o['status']) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($o['created_at']) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

      <?php elseif($tab==='profile'): ?>
        <h5 class="fw-bold mb-3">🧾 โปรไฟล์</h5>
        <form method="post" action="account-update.php" enctype="multipart/form-data" class="row g-3">
          <div class="col-12 col-md-3 text-center">
            <img 
              src="<?= $user['avatar'] ? 'uploads/avatars/'.htmlspecialchars($user['avatar']) : 'assets/no-avatar.png' ?>" 
              class="avatar mb-2"
              onerror="this.src='assets/no-avatar.png'">
            <input type="file" name="avatar" class="form-control form-control-sm">
          </div>

          <div class="col-12 col-md-9">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label">ชื่อผู้ใช้</label>
                <input class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">ชื่อ-นามสกุล</label>
                <input name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">เบอร์โทรศัพท์</label>
                <input name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">อีเมล</label>
                <input name="email" type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">วันเกิด</label>
                <input name="birthday" type="date" class="form-control" value="<?= htmlspecialchars($user['birthday']) ?>">
              </div>
              <div class="col-12">
                <label class="form-label">ที่อยู่จัดส่ง</label>
                <textarea name="address" class="form-control" rows="3"
                  placeholder="เลขที่บ้าน, ถนน, แขวง/ตำบล, อำเภอ, จังหวัด, รหัสไปรษณีย์"
                ><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
              </div>
            </div>
            <button class="btn btn-danger mt-3">บันทึกโปรไฟล์</button>
          </div>
        </form>

      <?php elseif($tab==='password'): ?>
        <h5 class="fw-bold mb-3">🔒 เปลี่ยนรหัสผ่าน</h5>
        <form method="post" action="change-password.php" class="row g-2" style="max-width:420px;">
          <input type="password" name="old_password" class="form-control mb-2" placeholder="รหัสผ่านเดิม" required>
          <input type="password" name="new_password" class="form-control mb-2" placeholder="รหัสผ่านใหม่" required>
          <input type="password" name="confirm_password" class="form-control mb-2" placeholder="ยืนยันรหัสผ่านใหม่" required>
          <button class="btn btn-danger">เปลี่ยนรหัสผ่าน</button>
        </form>

      <?php elseif($tab==='coupons'): ?>
        <h5 class="fw-bold mb-3">🧧 คูปองวันเกิด</h5>
        <div class="alert alert-info">
          🎉 ระบบจะสร้างคูปองอัตโนมัติในเดือนเกิดของคุณ
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>