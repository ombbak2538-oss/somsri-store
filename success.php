<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$order_id = $_GET['id'] ?? 0; 
$user_id = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("SELECT id, grand_total, payment_method, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Success | Somsri Superstore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --ios-bg: #F2F2F7;
            --ios-blue: #007AFF;
            --ios-green: #34C759;
            --glass: rgba(255, 255, 255, 0.8);
        }

        body { 
            background: var(--ios-bg); 
            font-family: 'Kanit', sans-serif; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 40px;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 40px 30px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.05);
        }

        .checkmark-wrapper {
            width: 100px;
            height: 100px;
            background: var(--ios-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: white;
            font-size: 50px;
            box-shadow: 0 15px 30px rgba(52, 199, 89, 0.3);
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes scaleIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .order-info-box {
            background: white;
            border-radius: 25px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid rgba(0,0,0,0.03);
        }

        .btn-ios-main {
            background: var(--ios-blue);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 20px;
            font-weight: 600;
            width: 100%;
            transition: 0.3s;
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
        }
        .btn-ios-main:active { transform: scale(0.96); opacity: 0.9; }

        .btn-ios-secondary {
            color: var(--ios-blue);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="success-card">
    <div class="checkmark-wrapper">
        <i class="bi bi-check-lg"></i>
    </div>

    <h3 class="fw-bold mb-1">สั่งซื้อเรียบร้อย!</h3>
    <p class="text-secondary small px-3">คำสั่งซื้อของคุณถูกส่งเข้าระบบแล้ว สมศรีจะรีบจัดส่งให้ไวที่สุดครับ</p>

    <div class="order-info-box">
        <div class="text-muted small mb-1">เลขที่คำสั่งซื้อ</div>
        <div class="fw-bold fs-4 mb-2">#SS-<?= str_pad($order['id'], 6, "0", STR_PAD_LEFT) ?></div>
        
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <span class="text-secondary small">ยอดชำระสุทธิ</span>
            <span class="fw-bold text-primary fs-5">฿<?= number_format($order['grand_total'], 2) ?></span>
        </div>

        <?php if($order['payment_method'] == 'cod'): ?>
            <div class="status-badge bg-info-subtle text-info-emphasis">
                <i class="bi bi-truck me-1"></i> เตรียมชำระเงินปลายทาง
            </div>
        <?php else: ?>
            <div class="status-badge bg-warning-subtle text-warning-emphasis">
                <i class="bi bi-clock-history me-1"></i> รอตรวจสอบยอดโอน
            </div>
        <?php endif; ?>
    </div>

    <div class="px-2">
        <a href="track-order.php?id=<?= $order['id'] ?>" class="btn-ios-main">
            <i class="bi bi-search me-1"></i> ติดตามสถานะสินค้า
        </a>
        <a href="index.php" class="btn-ios-secondary">
            กลับหน้าหลัก
        </a>
    </div>

    <p class="text-muted mt-4 mb-0" style="font-size: 0.75rem;">
        ระบบจะส่งข้อมูลยืนยันให้คุณทาง SMS/Email <br> ขอบคุณที่ใช้บริการ <b>Somsri Superstore</b>
    </p>
</div>

</body>
</html>