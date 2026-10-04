<?php
session_start();
include __DIR__ . "/../config/db.php";

$message = "";
$error = "";

if (isset($_POST['change_password'])) {
    $username = trim($_POST['username']);
    $new_password = $_POST['new_password'];

    if (!empty($username) && !empty($new_password)) {
        // เข้ารหัสด้วย password_hash (Bcrypt)
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // อัปเดตรหัสผ่านในตาราง admins
        $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $message = "เปลี่ยนรหัสผ่านสำหรับ $username เรียบร้อยแล้ว!";
            } else {
                $error = "ไม่พบชื่อผู้ใช้ $username ในระบบ";
            }
        } else {
            $error = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
        }
    } else {
        $error = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
    }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>เปลี่ยนรหัสผ่าน Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5" style="max-width:450px">

<h4 class="mb-3 text-center">🔑 เปลี่ยนรหัสผ่าน Admin</h4>

<?php if($message): ?>
<div class="alert alert-success"><?= $message ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="post" class="card p-4 shadow-sm">
  <div class="mb-3">
    <label class="form-label">Username ที่ต้องการเปลี่ยน</label>
    <input name="username" class="form-control" value="admin" required>
  </div>
  <div class="mb-3">
    <label class="form-label">รหัสผ่านใหม่</label>
    <input type="password" name="new_password" class="form-control" placeholder="กรอกรหัสผ่านใหม่" required>
  </div>
  <button name="change_password" class="btn btn-primary w-100">บันทึกรหัสผ่านใหม่</button>
  <a href="login.php" class="btn btn-link w-100 mt-2 text-center">กลับไปหน้าเข้าสู่ระบบ</a>
</form>

</body>
</html>