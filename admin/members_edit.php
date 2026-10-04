<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include "_guard.php";
include __DIR__ . "/../../config/db.php";

$id = (int)($_GET['id'] ?? 0);
$user = $conn->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
if(!$user){ die("ไม่พบผู้ใช้"); }

$my_id = $_SESSION['user_id'] ?? 1; 
$me = $conn->query("SELECT * FROM users WHERE id = $my_id")->fetch_assoc();

if($_SERVER['REQUEST_METHOD']==='POST'){
  $emp_code=$_POST['emp_code'];
  $username=$_POST['username'];
  $first_name=$_POST['first_name'];
  $last_name=$_POST['last_name'];
  $nickname=$_POST['nickname'];
  $phone=$_POST['phone'];
  $login_place=$_POST['login_place'];
  $branch=$_POST['branch'];
  $role=$_POST['role'];

  $avatar = $user['avatar'];
  if(!empty($_FILES['avatar']['name'])){
    $dir = __DIR__."/../avatars/";
    if(!is_dir($dir)) mkdir($dir,0755,true);
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $avatar = "user_".time().".".$ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $dir.$avatar);
    if($user['avatar'] && file_exists($dir.$user['avatar'])) unlink($dir.$user['avatar']);
  }

  $sql="UPDATE users SET emp_code=?,username=?,first_name=?,last_name=?,nickname=?,phone=?,login_place=?,branch=?,role=?,avatar=? WHERE id=?";
  $stmt=$conn->prepare($sql);
  $stmt->bind_param("ssssssssssi",$emp_code,$username,$first_name,$last_name,$nickname,$phone,$login_place,$branch,$role,$avatar,$id);
  $stmt->execute();

  header("Location: members.php"); exit;
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>แก้ไขสมาชิก</title>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--sidebar-bg:#1a1c23;--primary-red:#ff4d6d}
body{font-family:'Kanit',sans-serif;background:#f8f9fa;margin:0}
.sidebar{width:260px;height:100vh;position:fixed;background:var(--sidebar-bg)}
.main-content{margin-left:260px;padding:40px}
.preview{width:160px;height:160px;border-radius:15px;overflow:hidden;background:#eee}
.preview img{width:100%;height:100%;object-fit:cover}
@media(max-width:992px){.sidebar{display:none}.main-content{margin-left:0}}

        :root{--sidebar-bg:#1a1c23;--primary-red:#ff4d6d}
body{font-family:'Kanit',sans-serif;background:#f8f9fa;margin:0}
.sidebar{width:260px;height:100vh;position:fixed;background:var(--sidebar-bg);color:#fff;padding:20px 0;z-index:1000}
.sidebar-brand{padding:0 25px 30px;font-size:1.5rem;color:var(--primary-red);font-weight:600;border-bottom:1px solid rgba(255,255,255,0.05)}
.sidebar .nav-link{color:#9e9e9e;padding:15px 25px;display:flex;align-items:center;gap:12px;text-decoration:none;transition:.3s}
.sidebar .nav-link:hover,.sidebar .nav-link.active{color:#fff;background:rgba(255,77,109,.1);border-left:4px solid var(--primary-red)}
.main-content{margin-left:260px;padding:40px;min-height:100vh}
.user-pill{background:#fff;padding:6px 15px;border-radius:50px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
.avatar-box{width:40px;height:40px;border-radius:50%;background:#eee;overflow:hidden;display:flex;align-items:center;justify-content:center}
.avatar{width:42px;height:42px;border-radius:8px;object-fit:cover}
@media(max-width:992px){.sidebar{display:none}.main-content{margin-left:0}}

</style>
</head>
<body>

<?php include "_sidebar.php"; ?>

<div class="main-content">
  <h4 class="fw-bold mb-4">แก้ไขสมาชิก</h4>

  <form method="post" enctype="multipart/form-data" class="bg-white p-4 rounded-4 shadow-sm" style="max-width:1100px">
    <div class="row g-3">
      <div class="col-md-4">
        <div class="preview mb-2">
          <img id="preview" src="/somsri_store_web/public/avatars/<?= htmlspecialchars($user['avatar']) ?>">
        </div>
        <input type="file" name="avatar" class="form-control" onchange="previewImg(this)">
      </div>

      <div class="col-md-8">
        <div class="row g-3">
          <div class="col-md-4"><label>รหัสพนักงาน</label><input name="emp_code" class="form-control" value="<?= $user['emp_code'] ?>"></div>
          <div class="col-md-4"><label>Username</label><input name="username" class="form-control" value="<?= $user['username'] ?>"></div>
          <div class="col-md-4"><label>ชื่อจริง</label><input name="first_name" class="form-control" value="<?= $user['first_name'] ?>"></div>
          <div class="col-md-4"><label>นามสกุล</label><input name="last_name" class="form-control" value="<?= $user['last_name'] ?>"></div>
          <div class="col-md-4"><label>ชื่อเล่น</label><input name="nickname" class="form-control" value="<?= $user['nickname'] ?>"></div>
          <div class="col-md-4"><label>เบอร์</label><input name="phone" class="form-control" value="<?= $user['phone'] ?>"></div>
          <div class="col-md-4"><label>เข้าใช้งานที่ไหน</label><input name="login_place" class="form-control" value="<?= $user['login_place'] ?>"></div>
          <div class="col-md-4"><label>สาขา</label><input name="branch" class="form-control" value="<?= $user['branch'] ?>"></div>
          <div class="col-md-4">
            <label>สิทธิ์</label>
            <select name="role" class="form-select">
              <option <?= $user['role']=='USER'?'selected':'' ?>>USER</option>
              <option <?= $user['role']=='ADMIN'?'selected':'' ?>>ADMIN</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <button class="btn btn-danger">บันทึก</button>
      <a href="members.php" class="btn btn-light border">ย้อนกลับ</a>
    </div>
  </form>
</div>

<script>
function previewImg(i){ if(i.files[0]){ const r=new FileReader(); r.onload=e=>preview.src=e.target.result; r.readAsDataURL(i.files[0]); } }
</script>
</body>
</html>