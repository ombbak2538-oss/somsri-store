<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit;
}

$success = "";
$error = "";

if (isset($_POST['change'])) {
  $current = $_POST['current_password'] ?? '';
  $new     = $_POST['new_password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if ($current === '' || $new === '' || $confirm === '') {
    $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
  } elseif (strlen($new) < 8) {
    $error = "รหัสผ่านใหม่ต้องยาวอย่างน้อย 8 ตัวอักษร";
  } elseif (!preg_match('/[A-Za-z]/', $new) || !preg_match('/[0-9]/', $new)) {
    $error = "รหัสผ่านใหม่ควรมีทั้งตัวอักษรและตัวเลข";
  } elseif ($new !== $confirm) {
    $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
  } else {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $rs = $stmt->get_result();

    if ($row = $rs->fetch_assoc()) {
      if (!password_verify($current, $row['password'])) {
        $error = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
      } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);

        $up = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $up->bind_param("si", $hash, $_SESSION['admin_id']);
        $up->execute();

        $success = "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว 🎉";
      }
    } else {
      $error = "ไม่พบผู้ใช้";
    }
  }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เปลี่ยนรหัสผ่าน | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  body{
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    background: radial-gradient(circle at 50% 20%, #ffe0f0, #f1a1c6 60%, #c83b77 100%);
    font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  }
  .card-wrap{
    width:100%; max-width:440px; background:#fff; border-radius:18px;
    padding:20px 22px 24px; box-shadow: 0 20px 40px rgba(0,0,0,.25);
  }
  .title{ font-weight:800; color:#c83b77; text-align:center; margin-bottom:12px; }
  .input-group-text{
    background:#fff; border:1px solid #f0a8c9; border-right:none;
    border-radius:12px 0 0 12px; color:#c83b77;
  }
  .form-control{
    border:1px solid #f0a8c9; border-left:none;
    border-radius:0 12px 12px 0; padding:10px 12px;
  }
  .form-control:focus{
    border-color:#c83b77;
    box-shadow: 0 0 0 .2rem rgba(200,59,119,.25);
  }
  .btn-pink{
    background: linear-gradient(180deg, #ff8fc7, #c83b77);
    color:#fff; font-weight:800; border:none; border-radius:14px; padding:12px;
    box-shadow: 0 10px 20px rgba(200,59,119,.45);
  }
  .btn-back{ border-radius:12px; border:1px dashed #c83b77; color:#8b2a57; }
  .hint{ font-size:12px; color:#8b2a57; }
</style>
</head>
<body>

<div class="card-wrap">
  <div class="title">🔐 เปลี่ยนรหัสผ่าน Admin</div>

  <?php if($error): ?>
    <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if($success): ?>
    <div class="alert alert-success py-2"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
      <input type="password" name="current_password" class="form-control" placeholder="รหัสผ่านปัจจุบัน" required>
    </div>

    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-shield-lock-fill"></i></span>
      <input type="password" name="new_password" class="form-control" placeholder="รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)" required>
    </div>

    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-check-circle-fill"></i></span>
      <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่านใหม่" required>
    </div>

    <div class="hint mb-2">
      • อย่างน้อย 8 ตัวอักษร<br>
      • ควรมีทั้งตัวอักษรและตัวเลข
    </div>

    <button name="change" class="btn btn-pink w-100 mb-2">บันทึกรหัสผ่านใหม่</button>
  </form>

  <a href="dashboard.php" class="btn btn-back w-100">← กลับหน้า Dashboard</a>
</div>

</body>
</html>
