<?php
include __DIR__ . "/_guard.php";
include __DIR__ . "/../../config/db.php";

// รับ ID ออเดอร์จาก URL
$id = (int)($_GET['id'] ?? 0);

// ดึงข้อมูลหลักของออเดอร์ (รวมชื่อลูกค้า ที่อยู่ และสถานะ)
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$order_query->bind_param("i", $id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

if (!$order) {
    die("<div style='text-align:center; padding:50px;'><h3>ไม่พบข้อมูลออเดอร์ #$id</h3><a href='orders.php'>กลับหน้าหลัก</a></div>");
}

// ดึงรายการสินค้าที่อยู่ในออเดอร์นี้
// หมายเหตุ: ชื่อตารางรายการสินค้าของคุณอาจเป็น order_items หรือ order_details ให้เช็คในฐานข้อมูลอีกทีครับ
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $id");
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>ใบเสร็จรับเงิน #<?= $id ?> - สมศรี Store</title>
    <style>
        body { font-family: 'Tahoma', 'Kanit', sans-serif; color: #333; line-height: 1.6; padding: 0; margin: 0; background: #f9f9f9; }
        .invoice-box { max-width: 800px; margin: 30px auto; padding: 30px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0, 0, 0, 0.05); }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #ff4d6d; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info h2 { margin: 0; color: #ff4d6d; }
        .invoice-details { text-align: right; }
        .customer-section { margin-bottom: 30px; display: flex; justify-content: space-between; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 12px; text-align: left; }
        .table td { border-bottom: 1px solid #eee; padding: 12px; }
        .total-section { margin-top: 30px; text-align: right; border-top: 1px solid #eee; padding-top: 20px; }
        .total-amount { font-size: 24px; font-weight: bold; color: #ff4d6d; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #888; border-top: 1px dashed #ccc; padding-top: 20px; }
        
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-box { border: none; box-shadow: none; margin: 0; width: 100%; max-width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background:#1a1c23; padding:15px; text-align:center; color:white;">
    <button onclick="window.print()" style="padding:10px 20px; background:#ff4d6d; border:none; color:white; border-radius:5px; cursor:pointer; font-weight:bold;">
        <i class="bi bi-printer"></i> พิมพ์เอกสารนี้
    </button>
    <button onclick="window.close()" style="padding:10px 20px; background:#444; border:none; color:white; border-radius:5px; cursor:pointer; margin-left:10px;">ปิดหน้าต่าง</button>
</div>

<div class="invoice-box">
    <div class="header">
        <div class="company-info">
            <h2>สมศรี Store</h2>
            <p>123 ถนนสมมติ ต.ในเมือง อ.เมือง<br>จ.อุบลราชธานี 34000<br>โทร: 08x-xxx-xxxx</p>
        </div>
        <div class="invoice-details">
            <h3 style="margin:0;">ใบเสร็จรับเงิน</h3>
            <p><strong>เลขที่:</strong> #<?= $id ?><br>
               <strong>วันที่:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
        </div>
    </div>

    <div class="customer-section">
        <div>
            <strong>ลูกค้า:</strong><br>
            คุณ <?= htmlspecialchars($order['cus_name'] ?? 'ลูกค้าทั่วไป') ?><br>
            <strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($order['cus_address'] ?? '-')) ?><br>
            <strong>โทร:</strong> <?= htmlspecialchars($order['cus_tel'] ?? ($order['phone'] ?? '-')) ?>
        </div>
        <div style="text-align: right;">
            <strong>ช่องทางการชำระ:</strong> <?= htmlspecialchars($order['payment_method'] ?? 'โอนเงิน') ?><br>
            <strong>สถานะ:</strong> <?= $order['status'] ?>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>รายการสินค้า</th>
                <th style="text-align:center;">จำนวน</th>
                <th style="text-align:right;">ราคา/หน่วย</th>
                <th style="text-align:right;">ยอดรวม</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal = 0;
            if ($items && $items->num_rows > 0): 
                while($item = $items->fetch_assoc()): 
                    $line_total = $item['qty'] * $item['price'];
                    $subtotal += $line_total;
            ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td style="text-align:center;"><?= $item['qty'] ?></td>
                <td style="text-align:right;"><?= number_format($item['price'], 2) ?></td>
                <td style="text-align:right;"><?= number_format($line_total, 2) ?></td>
            </tr>
            <?php 
                endwhile; 
            else:
            ?>
            <tr>
                <td colspan="4" style="text-align:center;">ไม่พบรายการสินค้า</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="total-section">
        <p style="margin:0;">ยอดรวมสุทธิ</p>
        <div class="total-amount">฿<?= number_format($order['total'], 2) ?></div>
    </div>

    <div class="footer">
        <p>ขอบคุณที่อุดหนุนสินค้าจากสมศรี Store หวังว่าจะได้ให้บริการท่านอีกครั้ง</p>
        <p>ใบเสร็จนี้ออกโดยระบบอัตโนมัติ</p>
    </div>
</div>

</body>
</html>