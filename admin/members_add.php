<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "_guard.php";
include __DIR__ . "/../../config/db.php";

$my_id = $_SESSION['user_id'] ?? 1; 
$me = $conn->query("SELECT * FROM users WHERE id = $my_id")->fetch_assoc();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เพิ่มสมาชิก - สมศรี Store</title>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
:root{--sidebar-bg:#1a1c23;--primary-red:#ff4d6d}
body{font-family:'Kanit',sans-serif;background:#f8f9fa;margin:0}
.sidebar{width:260px;height:100vh;position:fixed;background:var(--sidebar-bg);color:#fff;padding:20px 0;z-index:1000}
.sidebar-brand{padding:0 25px 30px;font-size:1.5rem;color:var(--primary-red);font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05)}
.sidebar .nav-link{color:#9e9e9e;padding:15px 25px;display:flex;align-items:center;gap:12px;text-decoration:none;transition:.3s}
.sidebar .nav-link:hover,.sidebar .nav-link.active{color:#fff;background:rgba(255,77,109,.1);border-left:4px solid var(--primary-red)}
.main-content{margin-left:260px;padding:40px;min-height:100vh}

.user-pill{background:#fff;padding:6px 15px;border-radius:50px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.avatar-box{width:40px;height:40px;border-radius:50%;background:#eee;overflow:hidden;display:flex;align-items:center;justify-content:center}

/* ฟอร์ม */
.form-card{background:#fff;border-radius:20px;border:none;box-shadow:0 10px 30px rgba(0,0,0,0.05)}
.image-upload-zone{background:#f8f9fa;border-radius:15px;padding:20px;text-align:center;border:2px dashed #dee2e6}
.preview-circle{width:160px;height:160px;margin:0 auto 15px;border-radius:15px;overflow:hidden;background:#eee}
.preview-circle img{width:100%;height:100%;object-fit:cover}

.btn-save{background:var(--primary-red);border:none;padding:10px 30px;border-radius:10px}
.btn-save:hover{background:#e63958}

@media(max-width:992px){.sidebar{display:none}.main-content{margin-left:0}}
</style>
</head>
<body>

<?php include "_sidebar.php"; ?>

<div class="main-content">

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1">เพิ่มสมาชิก</h4>
      <p class="text-muted small mb-0">กรอกข้อมูลพนักงาน</p>
    </div>
    <div class="user-pill d-flex align-items-center gap-3">
      <div class="text-end d-none d-sm-block">
        <div class="fw-bold small"><?= htmlspecialchars($me['first_name'] ?? 'Admin') ?> (<?= htmlspecialchars($me['nickname'] ?? '-') ?>)</div>
        <div class="text-success small" style="font-size:10px;">ออนไลน์ขณะนี้</div>
      </div>
      <div class="avatar-box">
        <?php if(!empty($me['avatar'])): ?>
          <img src="/somsri_store_web/public/avatars/<?= htmlspecialchars($me['avatar']) ?>" style="width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <span><?= mb_substr($me['first_name'] ?? 'A',0,1) ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Form -->
  <div class="form-card p-4 p-md-5" style="max-width:1100px">
    <form action="members_save.php" method="POST" enctype="multipart/form-data">
      <div class="row g-4">

        <div class="col-lg-4">
          <div class="image-upload-zone">
            <label class="fw-bold mb-2 d-block">รูปโปรไฟล์</label>
            <div class="preview-circle">
              <img id="preview" src="https://placehold.co/400x400/f8f9fa/adb5bd?text=No+Image">
            </div>
            <input type="file" name="avatar" class="form-control form-control-sm" accept="image/*" onchange="previewImg(this)">
          </div>
        </div>

        <div class="col-lg-8">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">รหัสพนักงาน</label><input name="emp_code" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Username</label><input name="username" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>

            <div class="col-md-4"><label class="form-label">ชื่อจริง</label><input name="first_name" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">นามสกุล</label><input name="last_name" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">ชื่อเล่น</label><input name="nickname" class="form-control"></div>

            <div class="col-md-4"><label class="form-label">เบอร์โทร</label><input name="phone" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">เข้าใช้งานที่ไหน</label><input name="login_place" class="form-control"></div>
            <div class="col-md-4">
              <label class="form-label">สาขา</label>
              <select name="branch" class="form-select">
                <option value="สาขา 1">สาขา 1</option>
                <option value="สาขา 2">สาขา 2</option>
                <option value="สาขา 3">สาขา 3</option>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">สิทธิ์</label>
              <select name="role" class="form-select">
                <option value="USER">USER</option>
                <option value="ADMIN">ADMIN</option>
              </select>
            </div>
          </div>
        </div>

      </div>

      <div class="mt-4 pt-3 border-top">
        <button class="btn btn-save text-white fw-bold me-2">
          <i class="bi bi-check2-circle me-1"></i> บันทึก
        </button>
        <a href="members.php" class="btn btn-light border px-4">ยกเลิก</a>
      </div>
    </form>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewImg(input){
  if(input.files && input.files[0]){
    const reader = new FileReader();
    reader.onload = e => document.getElementById('preview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

</body>
</html>