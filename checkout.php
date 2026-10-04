<?php
session_start();
require_once __DIR__ . "/../config/db.php";

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'];
$total = 0;
foreach($cart as $id => $qty) {
    $res = $conn->query("SELECT price, discount_percent FROM products WHERE id = ".intval($id));
    if($row = $res->fetch_assoc()){
        $price = (float)$row['price'];
        $discount = (int)($row['discount_percent'] ?? 0);
        $finalPrice = $discount > 0 ? $price * (100 - $discount) / 100 : $price;
        $total += $finalPrice * $qty;
    }
}

$shipping = $_SESSION['shipping_fee'] ?? 0;
$discount_coupon = $_SESSION['discount_amount'] ?? 0;
$grandTotal = max(0, $total - $discount_coupon) + $shipping;

$promptpay_number = "083-244-3413";
$bank_info = [
    "name" => "ธนาคารกสิกรไทย",
    "account" => "123-4-56789-0",
    "owner" => "บจก. สมศรี ซุปเปอร์สโตร์"
];

$qr_data = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=00020101021129370016A00000067701011101130066" . str_replace('-', '', $promptpay_number) . "5802TH54" . sprintf("%.2f", $grandTotal) . "6304";
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Payment | สมศรี ซุปเปอร์สโตร์</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --ios-bg: #F2F2F7;
            --ios-blue: #007AFF;
            --ios-green: #34C759;
            --ios-gray: #8E8E93;
            --glass: rgba(255, 255, 255, 0.7);
        }

        body { 
            background: var(--ios-bg); 
            font-family: 'Kanit', sans-serif; 
            color: #1C1C1E;
            -webkit-font-smoothing: antialiased;
        }

        .ios-header {
            padding: 40px 0 20px;
            text-align: center;
        }

        .pay-card {
            background: var(--glass);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 35px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 50px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 50px;
        }

        .method-selector {
            display: flex;
            background: #E3E3E8;
            padding: 4px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .method-card {
            flex: 1;
            padding: 10px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 500;
            color: #48484A;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .method-card.active {
            background: white;
            color: var(--ios-blue);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            transform: scale(1.02);
        }

        .payment-detail-box {
            background: white;
            border-radius: 28px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.01);
        }

        .qr-frame {
            background: #fff;
            padding: 15px;
            border-radius: 24px;
            display: inline-block;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        .upload-zone {
            border: 2px dashed #D1D1D6;
            border-radius: 22px;
            padding: 25px;
            text-align: center;
            background: #F8F8F9;
            transition: 0.3s;
        }
        .upload-zone:hover {
            border-color: var(--ios-blue);
            background: #F0F7FF;
        }

        #slipPreview {
            max-width: 100%;
            border-radius: 18px;
            margin-top: 15px;
            display: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .summary-panel {
            background: rgba(255,255,255,0.5);
            border-radius: 28px;
            padding: 25px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }
        .grand-total-row {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #E5E5EA;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .btn-confirm {
            background: var(--ios-blue);
            color: white;
            border: none;
            width: 100%;
            padding: 18px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(0,122,255,0.3);
            transition: 0.3s;
        }
        .btn-confirm:active {
            transform: scale(0.97);
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .pay-card { border-radius: 0; margin-top: 0; border: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="ios-header">
        <h2 class="fw-bold">ชำระเงิน</h2>
        <p class="text-secondary small">Order ID: #SS<?= time() ?></p>
    </div>

    <div class="pay-card p-4 p-md-5">
        <div class="row g-5">
            <div class="col-lg-7">
                <h5 class="fw-bold mb-4">เลือกวิธีชำระเงิน</h5>
                
                <div class="method-selector">
                    <div class="method-card active" data-method="promptpay">
                        <i class="bi bi-qr-code-scan d-block fs-4 mb-1"></i> พร้อมเพย์
                    </div>
                    <div class="method-card" data-method="bank">
                        <i class="bi bi-bank d-block fs-4 mb-1"></i> ธนาคาร
                    </div>
                    <div class="method-card" data-method="cod">
                        <i class="bi bi-truck d-block fs-4 mb-1"></i> ปลายทาง
                    </div>
                </div>

                <div class="payment-detail-box">
                    <div id="view-promptpay" class="payment-view text-center">
                        <div class="qr-frame">
                            <img src="assets/img/promptpay-logo.png" style="height: 35px; margin-bottom: 15px;"><br>
                            <img src="<?= $qr_data ?>" alt="PromptPay QR" class="img-fluid" style="border-radius: 10px;">
                        </div>
                        <div class="mt-2">
                            <p class="mb-1 fw-medium"><?= $bank_info['owner'] ?></p>
                            <h4 class="fw-bold text-primary"><?= $promptpay_number ?></h4>
                            <p class="text-muted small">สแกน QR เพื่อชำระเงินจำนวน ฿<?= number_format($grandTotal, 2) ?></p>
                        </div>
                    </div>

                    <div id="view-bank" class="payment-view d-none">
                        <div class="p-4 rounded-4 text-center" style="background: #F9F9FB;">
                            <img src="https://logospng.org/download/kbank/logo-kbank-icon-256.png" width="60" class="mb-3">
                            <h6 class="text-secondary"><?= $bank_info['name'] ?></h6>
                            <h3 class="fw-bold tracking-wider my-2"><?= $bank_info['account'] ?></h3>
                            <p class="fw-medium mb-0"><?= $bank_info['owner'] ?></p>
                        </div>
                        <div class="mt-4 small text-center text-secondary">
                            <i class="bi bi-info-circle"></i> โอนแล้วอย่าลืมแนบสลิปที่ด้านล่างนะครับ
                        </div>
                    </div>

                    <div id="view-cod" class="payment-view d-none text-center">
                        <div class="py-5">
                            <div class="display-1 text-primary opacity-25 mb-3"><i class="bi bi-mailbox"></i></div>
                            <h5 class="fw-bold">บริการเก็บเงินปลายทาง</h5>
                            <p class="text-secondary">ท่านสามารถชำระเงินสด ฿<?= number_format($grandTotal, 2) ?> <br>ให้พนักงานส่งของได้ทันทีเมื่อได้รับสินค้า</p>
                        </div>
                    </div>

                    <div id="slip-section" class="mt-4">
                        <div class="upload-zone" onclick="document.getElementById('slipInput').click()">
                            <i class="bi bi-cloud-arrow-up-fill fs-2 text-primary"></i>
                            <p class="mb-0 mt-2 fw-medium">แตะเพื่อแนบสลิปโอนเงิน</p>
                            <span class="text-muted small">รองรับ JPG, PNG</span>
                            <input type="file" id="slipInput" hidden accept="image/*">
                        </div>
                        <center><img id="slipPreview" src=""></center>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="summary-panel">
                    <h5 class="fw-bold mb-4">สรุปยอดชำระ</h5>
                    
                    <div class="price-row">
                        <span class="text-secondary">ราคาสินค้า</span>
                        <span>฿<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="price-row">
                        <span class="text-secondary">ค่าจัดส่ง</span>
                        <span>฿<?= number_format($shipping, 2) ?></span>
                    </div>
                    <?php if($discount_coupon > 0): ?>
                    <div class="price-row">
                        <span class="text-secondary">ส่วนลดคูปอง</span>
                        <span class="text-danger">- ฿<?= number_format($discount_coupon, 2) ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="price-row grand-total-row">
                        <span>ยอดสุทธิ</span>
                        <span class="text-primary">฿<?= number_format($grandTotal, 2) ?></span>
                    </div>

                    <div class="mt-4 p-3 rounded-4 bg-white border border-light small text-secondary">
                        <i class="bi bi-shield-check text-success me-1"></i> ข้อมูลการชำระเงินของคุณถูกเข้ารหัสและปลอดภัยตามมาตรฐานสากล
                    </div>

                    <button id="confirmBtn" class="btn-confirm mt-4">
                        ยืนยันการสั่งซื้อ
                    </button>
                    
                    <a href="cart.php" class="btn btn-link w-100 text-muted mt-3 text-decoration-none">
                        <i class="bi bi-chevron-left"></i> ย้อนกลับไปตะกร้า
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const methodCards = document.querySelectorAll('.method-card');
const views = {
    promptpay: document.getElementById('view-promptpay'),
    bank: document.getElementById('view-bank'),
    cod: document.getElementById('view-cod')
};
const slipSection = document.getElementById('slip-section');

methodCards.forEach(card => {
    card.onclick = () => {
        methodCards.forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        
        const method = card.dataset.method;
        Object.keys(views).forEach(k => views[k].classList.add('d-none'));
        views[method].classList.remove('d-none');
        
        if(method === 'cod') {
            slipSection.style.opacity = '0';
            setTimeout(() => slipSection.style.display = 'none', 200);
        } else {
            slipSection.style.display = 'block';
            setTimeout(() => slipSection.style.opacity = '1', 10);
        }
    };
});

document.getElementById('slipInput').onchange = function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('slipPreview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
};

document.getElementById('confirmBtn').onclick = async function() {
    const activeMethod = document.querySelector('.method-card.active').dataset.method;
    const slipFile = document.getElementById('slipInput').files[0];

    if (activeMethod !== 'cod' && !slipFile) {
        return alert('กรุณาแนบรูปสลิปเพื่อยืนยันการโอนเงินครับ');
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-grow spinner-grow-sm"></span> กำลังส่งคำสั่งซื้อ...';

    const formData = new FormData();
    formData.append('payment_method', activeMethod);
    if(slipFile) formData.append('slip', slipFile);

    try {
        const res = await fetch('place-order-all-in-one.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.ok) {
            window.location.href = 'order-success.php?id=' + data.order_id;
        } else {
            alert('ขออภัย: ' + data.message);
            this.disabled = false;
            this.innerText = 'ยืนยันการสั่งซื้อ';
        }
    } catch (e) {
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่ครับ');
        this.disabled = false;
        this.innerText = 'ยืนยันการสั่งซื้อ';
    }
};
</script>

</body>
</html>