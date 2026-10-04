<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$error = "";
$next = $_GET['next'] ?? 'index.php';

if (isset($_POST['login'])) {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if ($u === '' || $p === '') {
        $error = "กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username=? AND role='USER' LIMIT 1");
        $stmt->bind_param("s", $u);
        $stmt->execute();
        $rs = $stmt->get_result();

        if ($row = $rs->fetch_assoc()) {
            if (password_verify($p, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = 'USER';

                header("Location: " . $next);
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
    <title>เข้าสู่ระบบ | Somsri Super Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --ios-pink: #FF2D55;
            --glass-bg: rgba(255, 255, 255, 0.75);
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: 
                radial-gradient(at 0% 0%, rgba(255, 179, 193, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(200, 59, 119, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(255, 45, 85, 0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(161, 40, 97, 0.3) 0px, transparent 50%),
                #fdf2f7;
            font-family: 'Kanit', -apple-system, sans-serif;
            overflow: hidden;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--glass-bg);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border-radius: 45px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .logo-wrap {
            width: 90px; height: 90px;
            background: #fff;
            border-radius: 22px;
            margin: 0 auto 20px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 10px 30px rgba(200, 59, 119, 0.15);
        }
        .logo-wrap img { height: 50px; }

        .title {
            text-align: center;
            font-weight: 700;
            color: #1d1d1f;
            font-size: 1.5rem;
            margin-bottom: 25px;
            letter-spacing: -0.5px;
        }

        .input-group {
            background: rgba(255, 255, 255, 0.5);
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
            margin-bottom: 15px;
        }
        .input-group:focus-within {
            background: #fff;
            border-color: var(--ios-pink);
            box-shadow: 0 0 0 4px rgba(255, 45, 85, 0.1);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: var(--ios-pink);
            padding-left: 20px;
        }

        .form-control {
            background: transparent;
            border: none;
            padding: 14px 15px;
            font-size: 16px;
        }
        .form-control:focus {
            box-shadow: none;
            background: transparent;
        }

        .btn-pink {
            background: var(--ios-pink);
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 18px;
            padding: 16px;
            font-size: 17px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
            box-shadow: 0 10px 25px rgba(255, 45, 85, 0.25);
        }
        .btn-pink:hover {
            filter: brightness(1.1);
            transform: translateY(-2px);
            color: #fff;
        }
        .btn-pink:active {
            transform: scale(0.97);
        }

        .btn-back {
            border-radius: 18px;
            border: 1px solid rgba(0,0,0,0.1);
            color: #86868b;
            background: rgba(0,0,0,0.03);
            padding: 12px;
            font-weight: 500;
            text-decoration: none;
            display: block;
            text-align: center;
            transition: 0.2s;
        }
        .btn-back:hover {
            background: rgba(0,0,0,0.08);
            color: #1d1d1f;
        }

        .small-link {
            font-size: 14px;
            color: var(--ios-pink);
            text-decoration: none;
            font-weight: 500;
        }
        .small-link:hover { text-decoration: underline; }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #86868b;
            margin-top: 25px;
        }

        .alert-error {
            background: rgba(255, 59, 48, 0.1);
            color: #ff3b30;
            border: none;
            border-radius: 15px;
            font-size: 14px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-wrap">
        <img src="/somsri-store/public/assets/logo.png" alt="Somsri Logo">
    </div>

    <div class="title">Somsri Super Store</div>

    <?php if($error): ?>
        <div class="alert-error fw-medium">
            <i class="bi bi-exclamation-circle-fill me-1"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
            <input name="username" class="form-control" placeholder="ชื่อผู้ใช้" required autofocus>
        </div>

        <div class="input-group mb-4">
            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
            <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
        </div>

        <button name="login" class="btn btn-pink w-100 mb-3">เข้าสู่ระบบ</button>

        <div class="d-flex justify-content-between px-2 mb-4">
            <a href="register.php" class="small-link">สร้างบัญชีใหม่</a>
            <a href="forgot-password.php" class="small-link">ลืมรหัสผ่าน?</a>
        </div>
    </form>

    <a href="index.php" class="btn btn-back w-100">← กลับหน้าหลัก</a>
    <div class="footer">© 2026 สมศรี ซุปเปอร์สโตร์</div>
</div>

</body>
</html>