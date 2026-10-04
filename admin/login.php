<?php
session_start();
require_once __DIR__ . "/../../config/db.php";

$error = "";

if (isset($_SESSION['admin_id'])) {
  header("Location: /somsri-store/public/admin/dashboard.php");
  exit;
}

if (isset($_POST['login'])) {
  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';

  if ($u === '' || $p === '') {
    $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
  } else {
    $stmt = $conn->prepare("SELECT id, username, password, branch_id FROM admins WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $u);
    $stmt->execute();
    $rs = $stmt->get_result();

    if ($row = $rs->fetch_assoc()) {
      if (password_verify($p, $row['password'])) {
        $_SESSION['admin_id']  = (int)$row['id'];
        $_SESSION['username']  = $row['username'];
        $_SESSION['role']      = 'ADMIN';
        $_SESSION['branch_id'] = (int)$row['branch_id']; 

        $upd = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id=?");
        $upd->bind_param("i", $row['id']);
        $upd->execute();

        header("Location: /somsri-store/public/admin/dashboard.php");
        exit;
      }
    }

    $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
  }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>Admin Login | ร้านสมศรี</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  background: radial-gradient(circle at 50% 20%, #ffb3d9, #c83b77 60%, #a12861 100%);
  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
}
.login-card{
  width:100%;
  max-width:420px;
  background: linear-gradient(180deg, #fde7f1, #f7cadd);
  border-radius:18px;
  padding:18px 22px 24px;
  box-shadow: 0 20px 40px rgba(0,0,0,.25);
  border:1px solid rgba(255,255,255,.7);
}
.logo-wrap{
  width:86px;height:86px;border-radius:50%;
  background:#fff;margin:-54px auto 6px;
  display:flex;align-items:center;justify-content:center;
  box-shadow: 0 10px 24px rgba(0,0,0,.25);
}
.logo-wrap img{ height:48px; }
.title{ text-align:center;font-weight:800;color:#a12861;margin:6px 0 12px; }
.input-group-text{
  background:#fff;border:1px solid #f0a8c9;border-right:none;border-radius:12px 0 0 12px;color:#c83b77;
}
.form-control{
  border:1px solid #f0a8c9;border-left:none;border-radius:0 12px 12px 0;padding:10px 12px;
}
.form-control:focus{ border-color:#c83b77; box-shadow: 0 0 0 .2rem rgba(200,59,119,.25); }
.btn-pink{
  background: linear-gradient(180deg, #ff8fc7, #c83b77);
  color:#fff;font-weight:800;border:none;border-radius:14px;padding:12px;
  box-shadow: 0 10px 20px rgba(200,59,119,.45);
}
.btn-pink:hover{ filter:brightness(1.05); }
.btn-back{ border-radius:12px;border:1px dashed #c83b77;color:#8b2a57;background:transparent; }
.btn-back:hover{ background:#f6c1d8; }
.small-link{ font-size:13px;color:#8b2a57;text-decoration:none; }
.small-link:hover{ text-decoration:underline; }
.footer{ text-align:center;font-size:12px;color:#8b2a57;margin-top:10px; }
</style>
</head>
<body>

<div class="login-card">
  <div class="logo-wrap">
    <img src="/somsri-store/public/assets/logo.png" alt="Somsri Logo">
  </div>

  <div class="title">Admin Panel</div>

  <?php if($error): ?>
    <div class="alert alert-danger py-2 text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" novalidate>
    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
      <input name="username" class="form-control" placeholder="Username" required>
    </div>

    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
      <input type="password" name="password" class="form-control" placeholder="Password" required>
    </div>

    <button name="login" class="btn btn-pink w-100 mb-2">เข้าสู่หลังบ้าน</button>

    <div class="d-flex justify-content-between mb-2">
      <span class="small-link text-muted">เฉพาะเจ้าของร้าน / แอดมิน</span>
      <a href="/somsri-store/public/login.php" class="small-link">เข้าสู่ระบบลูกค้า</a>
    </div>
  </form>

  <a href="/somsri-store/public/index.php" class="btn btn-back w-100 mb-2">← กลับหน้าร้าน</a>
  <div class="footer">© ร้านสมศรีซุปเปอร์สโตร์</div>
</div>

</body>
</html>