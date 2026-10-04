<?php
if (session_status()===PHP_SESSION_NONE) session_start();
include "_guard.php";
include __DIR__ . "/../../config/db.php";

$id=(int)($_GET['id']??0);
if($_SERVER['REQUEST_METHOD']==='POST'){
  $p=password_hash($_POST['password'],PASSWORD_DEFAULT);
  $conn->query("UPDATE users SET password='$p' WHERE id=$id");
  header("Location: members.php"); exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>รีเซ็ตรหัสผ่าน</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include "_sidebar.php"; ?>
<div class="main-content">
<h4 class="fw-bold mb-3">รีเซ็ตรหัสผ่าน</h4>
<form method="post" class="card p-4 shadow-sm" style="max-width:420px">
  <input type="password" class="form-control mb-3" name="password" placeholder="รหัสผ่านใหม่" required>
  <button class="btn btn-warning w-100">รีเซ็ต</button>
</form>
</div>
</body>
</html>