<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$error = "";
$success = "";

if (isset($_POST['register'])) {
  $username = trim($_POST['username'] ?? '');
  $fullname = trim($_POST['fullname'] ?? '');
  $phone    = trim($_POST['phone'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $birthday = $_POST['birthday'] ?? '';
  $password = $_POST['password'] ?? '';

  if ($username==='' || $fullname==='' || $phone==='' || $email==='' || $birthday==='' || $password==='') {
    $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "รูปแบบอีเมลไม่ถูกต้อง";
  } else {
    $chk = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $chk->bind_param("ss", $username, $email);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
      $error = "Username หรือ Email นี้ถูกใช้งานแล้ว";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("INSERT INTO users (username, fullname, phone, email, birthday, password, role) VALUES (?,?,?,?,?,?, 'USER')");
      $stmt->bind_param("ssssss", $username, $fullname, $phone, $email, $birthday, $hash);
      $stmt->execute();
      header("Location: login.php?registered=1");
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>สมัครสมาชิก | ร้านสมศรี</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{
  min-height:100vh;display:flex;align-items:center;justify-content:center;
  background: radial-gradient(circle at 50% 20%, #ffb3d9, #c83b77 60%, #a12861 100%);
  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
}
.login-card{
  width:100%;max-width:460px;background: linear-gradient(180deg, #fde7f1, #f7cadd);
  border-radius:18px;padding:18px 22px 24px;box-shadow: 0 20px 40px rgba(0,0,0,.25);
  border:1px solid rgba(255,255,255,.7);
}
.logo-wrap{width:86px;height:86px;border-radius:50%;background:#fff;margin:-54px auto 6px;display:flex;align-items:center;justify-content:center;box-shadow: 0 10px 24px rgba(0,0,0,.25);}
.logo-wrap img{height:48px;}
.title{text-align:center;font-weight:800;color:#a12861;margin:6px 0 12px;}
.input-group-text{background:#fff;border:1px solid #f0a8c9;border-right:none;border-radius:12px 0 0 12px;color:#c83b77;}
.form-control{border:1px solid #f0a8c9;border-left:none;border-radius:0 12px 12px 0;padding:10px 12px;}
.form-control:focus{border-color:#c83b77;box-shadow: 0 0 0 .2rem rgba(200,59,119,.25);}
.btn-pink{background: linear-gradient(180deg, #ff8fc7, #c83b77);color:#fff;font-weight:800;border:none;border-radius:14px;padding:12px;box-shadow: 0 10px 20px rgba(200,59,119,.45);}
.btn-back{border-radius:12px;border:1px dashed #c83b77;color:#8b2a57;background:transparent;}
.footer{text-align:center;font-size:12px;color:#8b2a57;margin-top:10px;}
</style>
</head>
<body>

<div class="login-card">
  <div class="logo-wrap">
    <img src="/somsri-store/public/assets/logo.png" alt="Somsri Logo">
  </div>

  <div class="title">สมัครสมาชิก</div>

  <?php if($error): ?>
    <div class="alert alert-danger py-2 text-center"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="input-group mb-2">
      <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
      <input name="fullname" class="form-control" placeholder="ชื่อ - นามสกุล" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
      <input name="phone" class="form-control" placeholder="เบอร์โทรศัพท์" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
      <input type="email" name="email" class="form-control" placeholder="อีเมล" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="bi bi-calendar-event-fill"></i></span>
      <input type="date" name="birthday" class="form-control" required>
    </div>

    <div class="input-group mb-2">
      <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
      <input name="username" class="form-control" placeholder="Username (ใช้เข้าสู่ระบบ)" required>
    </div>

    <div class="input-group mb-3">
      <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
      <input type="password" name="password" class="form-control" placeholder="Password" required>
    </div>

    <button name="register" class="btn btn-pink w-100 mb-2">สมัครสมาชิก</button>
  </form>

  <a href="login.php" class="btn btn-back w-100 mb-2">← กลับไปหน้าเข้าสู่ระบบ</a>
  <div class="footer">© ร้านสมศรีซุปเปอร์สโตร์</div>
</div>

</body>
</html>
