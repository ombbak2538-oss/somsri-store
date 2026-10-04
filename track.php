<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$order_no = trim($_GET['order_no'] ?? '');
$phone = trim($_GET['phone'] ?? '');

$order = null;
$items = [];

if ($order_no || $phone) {
    $sql = "SELECT * FROM orders WHERE order_no = ? OR phone = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $order_no, $phone);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $oid = (int)$order['id'];
        $sqlItems = "SELECT oi.qty, oi.price, p.name, p.image FROM order_items oi 
                     JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?";
        $stmtItems = $conn->prepare($sqlItems);
        $stmtItems->bind_param("i", $oid);
        $stmtItems->execute();
        $resItems = $stmtItems->get_result();
        while ($r = $resItems->fetch_assoc()) { $items[] = $r; }
    }
}

function getStatusInfo($status) {
    switch ($status) {
        case 'PENDING': return ['text' => 'รอตรวจสอบ', 'color' => '#FF9500', 'step' => 1];
        case 'PROCESSING': return ['text' => 'เตรียมสินค้า', 'color' => '#5856D6', 'step' => 2];
        case 'SHIPPED': return ['text' => 'จัดส่งแล้ว', 'color' => '#007AFF', 'step' => 3];
        case 'DELIVERED': return ['text' => 'สำเร็จ', 'color' => '#34C759', 'step' => 4];
        default: return ['text' => 'ยกเลิก', 'color' => '#8E8E93', 'step' => 0];
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Track Order | Somsri Super Store</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ios-pink: #FF2D55;
            --glass: rgba(255, 255, 255, 0.7);
        }
        body {
            font-family: 'Kanit', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at top, #ffb3d9 0%, #ff7fb8 35%, #c23b78 100%);
            display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .track-card {
            background: var(--glass);
            backdrop-filter: blur(25px) saturate(180%);
            -webkit-backdrop-filter: blur(25px) saturate(180%);
            border-radius: 40px;
            border: 1px solid rgba(255,255,255,0.5);
            width: 100%; max-width: 500px;
            padding: 35px; box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        .input-group-ios {
            background: rgba(255,255,255,0.5);
            border-radius: 20px; padding: 5px 15px;
            border: 1px solid rgba(0,0,0,0.05);
            margin-bottom: 12px;
        }
        .input-group-ios input {
            border: none; background: transparent; padding: 12px 5px; width: 100%; outline: none;
        }
        .btn-ios {
            background: var(--ios-pink); color: #fff;
            border: none; border-radius: 20px; padding: 15px;
            font-weight: 600; width: 100%; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(255, 45, 85, 0.2);
        }
        .btn-ios:active { transform: scale(0.96); }

        .stepper { display: flex; justify-content: space-between; position: relative; margin: 30px 0; }
        .stepper::before {
            content: ''; position: absolute; top: 15px; left: 10%; right: 10%;
            height: 3px; background: rgba(0,0,0,0.05); z-index: 1;
        }
        .step-progress {
            position: absolute; top: 15px; left: 10%;
            height: 3px; background: var(--ios-pink); z-index: 2;
            transition: width 1s ease-in-out;
        }
        .step { position: relative; z-index: 3; text-align: center; width: 25%; }
        .step-circle {
            width: 32px; height: 32px; background: #fff; border-radius: 50%;
            margin: 0 auto 8px; display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: bold; color: #ccc;
            border: 2px solid rgba(0,0,0,0.05); transition: 0.3s;
        }
        .step.active .step-circle {
            background: var(--ios-pink); color: #fff; border-color: var(--ios-pink);
            box-shadow: 0 0 15px rgba(255, 45, 85, 0.4);
        }
        .step.active .step-label { color: #1d1d1f; font-weight: 600; }
        .step-label { font-size: 11px; color: #8e8e93; }

        .order-info-box {
            background: rgba(255,255,255,0.4);
            border-radius: 25px; padding: 20px; border: 1px solid rgba(255,255,255,0.3);
        }
        .product-img { width: 55px; height: 55px; border-radius: 15px; object-fit: cover; }
    </style>
</head>
<body>

<div class="track-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold" style="letter-spacing: -1px;">ติดตามพัสดุ</h3>
        <p class="text-muted small">Somsri Super Store Delivery</p>
    </div>

    <form method="get">
        <div class="input-group-ios">
            <input type="text" name="order_no" placeholder="เลขคำสั่งซื้อ" value="<?= htmlspecialchars($order_no) ?>">
        </div>
        <div class="input-group-ios mb-3">
            <input type="text" name="phone" placeholder="เบอร์โทรศัพท์" value="<?= htmlspecialchars($phone) ?>">
        </div>
        <button class="btn-ios">ค้นหาออเดอร์</button>
    </form>

    <?php if ($order): 
        $statusInfo = getStatusInfo($order['status']);
        $progressWidth = [0, 0, 33, 66, 100][$statusInfo['step']];
    ?>
        <div class="stepper">
            <div class="step-progress" style="width: <?= $progressWidth ?>%;"></div>
            <?php 
            $steps = [['icon'=>'bi-receipt','n'=>'รอตรวจ'], ['icon'=>'bi-box-seam','n'=>'แพ็คของ'], ['icon'=>'bi-truck','n'=>'ส่งแล้ว'], ['icon'=>'bi-check2-circle','n'=>'สำเร็จ']];
            foreach($steps as $idx => $s): 
                $isActive = ($statusInfo['step'] >= ($idx+1)) ? 'active' : '';
            ?>
                <div class="step <?= $isActive ?>">
                    <div class="step-circle"><i class="bi <?= $s['icon'] ?>"></i></div>
                    <div class="step-label"><?= $s['n'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="order-info-box shadow-sm mb-3">
            <div class="d-flex justify-content-between mb-3">
                <span class="badge rounded-pill px-3 py-2" style="background: <?= $statusInfo['color'] ?>20; color: <?= $statusInfo['color'] ?>;">
                    ● <?= $statusInfo['text'] ?>
                </span>
                <span class="small text-muted">ID: <?= $order['order_no'] ?></span>
            </div>
            <div class="small mb-1"><b>เลขพัสดุ:</b> <span class="text-primary fw-bold"><?= $order['tracking_no'] ?: 'รอดำเนินการ' ?></span></div>
            <div class="small text-muted"><b>สั่งเมื่อ:</b> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
        </div>

        <div class="px-2">
            <?php foreach ($items as $it): ?>
            <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom border-white">
                <img src="uploads/products/<?= htmlspecialchars($it['image']) ?>" class="product-img shadow-sm" onerror="this.src='assets/no-image.png'">
                <div class="flex-grow-1">
                    <div class="small fw-bold"><?= htmlspecialchars($it['name']) ?></div>
                    <div class="text-muted" style="font-size: 11px;"><?= number_format($it['price'],2) ?> x <?= $it['qty'] ?></div>
                </div>
                <div class="small fw-bold text-end">฿<?= number_format($it['price'] * $it['qty'], 2) ?></div>
            </div>
            <?php endforeach; ?>

            <div class="d-flex justify-content-between mt-3 px-1">
                <span class="text-muted small">รวมทั้งสิ้น</span>
                <h5 class="fw-bold text-danger">฿<?= number_format($order['total_amount'], 2) ?></h5>
            </div>
        </div>

    <?php elseif ($order_no || $phone): ?>
        <div class="alert alert-light border-0 text-center mt-4 rounded-4 small">
            <i class="bi bi-search text-muted mb-2 d-block fs-4"></i>
            ไม่พบข้อมูลออเดอร์นี้ในระบบ
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-link w-100 text-muted text-decoration-none small mt-3">← กลับหน้าหลัก</a>
</div>

</body>
</html>