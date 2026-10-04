<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. ตรวจสอบการเข้าสู่ระบบ
if (file_exists(__DIR__ . "/_guard.php")) {
    include_once __DIR__ . "/_guard.php";
} elseif (file_exists(__DIR__ . "/auth.php")) {
    include_once __DIR__ . "/auth.php";
}

// 2. เชื่อมต่อฐานข้อมูลแบบยืดหยุ่น
if (file_exists(__DIR__ . "/../config/db.php")) {
    require_once __DIR__ . "/../config/db.php";
} elseif (file_exists(__DIR__ . "/../../config/db.php")) {
    require_once __DIR__ . "/../../config/db.php";
} else {
    die("<div style='color:red; padding:20px; font-family:sans-serif;'><b>ข้อผิดพลาด:</b> ไม่พบไฟล์ config/db.php</div>");
}

// 3. ดึงข้อมูลผู้ใช้งานปัจจุบัน ป้องกัน SQL Injection
$my_id = (int)($_SESSION['user_id'] ?? 1);
$stmt_me = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_me->bind_param("i", $my_id);
$stmt_me->execute();
$me = $stmt_me->get_result()->fetch_assoc();

// 4. ดึงรายชื่อสมาชิกทั้งหมด เรียงตาม ID จากใหม่ไปเก่า
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");

// กำหนด Base URL สำหรับดึงภาพรูปโปรไฟล์
$base_url = (strpos($_SERVER['SCRIPT_NAME'], '/somsri-store/') !== false) ? '/somsri-store' : '/somsri_store_web';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จัดการสมาชิก - สมศรี Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { 
            --sidebar-bg: #1a1c23; 
            --primary-red: #ff4d6d; 
        }
        body { 
            font-family: 'Kanit', sans-serif; 
            background: #f8f9fa; 
            margin: 0; 
        }
        
        /* Layout Sidebar */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            position: fixed; 
            background: var(--sidebar-bg); 
            color: #fff; 
            padding: 20px 0; 
            z-index: 1000; 
        }
        .sidebar .nav-link {
            color: #9e9e9e;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 77, 109, 0.1);
            border-left: 4px solid var(--primary-red);
        }
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            min-height: 100vh; 
        }
        
        /* Table Design */
        .card-table { 
            border: none; 
            border-radius: 15px; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.04); 
            overflow: hidden; 
            background: #fff; 
        }
        .table thead th { 
            background: #fdfdfd; 
            border-bottom: 1px solid #f0f0f0; 
            color: #666; 
            padding: 15px; 
            font-size: 0.88rem; 
            font-weight: 500; 
        }
        .table tbody td { 
            padding: 15px; 
            vertical-align: middle; 
        }
        
        /* Avatar Image */
        .avatar-list { 
            width: 45px; 
            height: 45px; 
            border-radius: 10px; 
            object-fit: cover; 
            background: #f0f0f0; 
            border: 1px solid #e9ecef;
        }
        
        .badge-role { 
            font-weight: 400; 
            padding: 6px 12px; 
            border-radius: 50px; 
            font-size: 0.75rem; 
        }

        @media(max-width: 992px) { 
            .sidebar { display: none; } 
            .main-content { margin-left: 0; padding: 20px; } 
        }
    </style>
</head>
<body>

<?php 
if (file_exists(__DIR__ . "/_sidebar.php")) {
    include_once __DIR__ . "/_sidebar.php"; 
}
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">👥 จัดการสมาชิก</h3>
            <p class="text-muted small mb-0">หน้าแรก / รายชื่อสมาชิกพนักงาน</p>
        </div>
        <a href="members_add.php" class="btn btn-danger rounded-pill px-4 shadow-sm">
            <i class="bi bi-person-plus-fill me-2"></i>เพิ่มสมาชิก
        </a>
    </div>

    <!-- ส่วนแสดงข้อความแจ้งเตือน Flash Message -->
    <?php if(!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4 alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['flash_success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if(!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4 alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="card card-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" width="70">รูป</th>
                        <th>รหัสพนักงาน</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>Username</th>
                        <th>สิทธิ์</th>
                        <th class="text-end pe-4">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while($u = $users->fetch_assoc()): ?>
                            <?php 
                                // ตรวจสอบตำแหน่งรูปภาพ
                                $avatar_file = !empty($u['avatar']) ? $u['avatar'] : 'default.png';
                                $avatar_src = "../../public/avatars/" . $avatar_file;
                                if (!file_exists(__DIR__ . "/../../public/avatars/" . $avatar_file)) {
                                    $avatar_src = $base_url . "/uploads/avatars/" . $avatar_file;
                                }
                            ?>
                        <tr>
                            <td class="ps-4">
                                <img src="<?= $avatar_src ?>" 
                                     class="avatar-list" 
                                     alt="Avatar"
                                     onerror="this.src='https://placehold.co/100x100?text=User'">
                            </td>
                            <td>
                                <span class="fw-medium text-secondary">
                                    <?= htmlspecialchars($u['emp_code'] ?? '-') ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    <?= htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?>
                                </div>
                                <?php if(!empty($u['nickname'])): ?>
                                    <small class="text-muted">ชื่อเล่น: <?= htmlspecialchars($u['nickname']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code class="text-danger" style="font-size: 0.88rem;">
                                    <?= htmlspecialchars($u['username'] ?? '-') ?>
                                </code>
                            </td>
                            <td>
                                <?php if(strtoupper($u['role'] ?? '') === 'ADMIN'): ?>
                                    <span class="badge bg-dark badge-role text-white">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border badge-role">User</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded">
                                    <a href="members_edit.php?id=<?= $u['id'] ?>" class="btn btn-white btn-sm border" title="แก้ไข">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </a>
                                    <a href="members_delete.php?id=<?= $u['id'] ?>" 
                                       class="btn btn-white btn-sm text-danger border border-start-0" 
                                       onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบสมาชิกนี้?')" title="ลบ">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-person-exclamation fs-1"></i>
                                    <p class="mt-2 mb-0">ยังไม่มีข้อมูลสมาชิกในระบบ</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>