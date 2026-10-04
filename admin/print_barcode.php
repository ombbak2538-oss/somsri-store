<?php
include __DIR__.'/_guard.php';
$sku = $_GET['sku'] ?? '';
$name = $_GET['name'] ?? 'สินค้าไม่มีชื่อ';
$price = $_GET['price'] ?? '0';

if(empty($sku)) die("ไม่พบรหัสสินค้า");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Print Barcode - <?= $sku ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: 'Kanit', sans-serif; display: flex; justify-content: center; padding-top: 50px; background: #eee; }
        .ticket { 
            background: white; 
            padding: 15px; 
            border: 1px solid #ccc; 
            text-align: center; 
            width: 250px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .name { font-size: 14px; font-weight: bold; margin-bottom: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .price { font-size: 18px; color: #d9534f; font-weight: bold; margin-top: 5px; }
        svg { width: 100%; height: auto; }
        @media print {
            body { background: white; padding: 0; }
            .ticket { border: none; box-shadow: none; }
            .btn-print { display: none; }
        }
        .btn-print {
            position: fixed; top: 20px; right: 20px;
            padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;
        }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">🖨️ สั่งพิมพ์บาร์โค้ด</button>

    <div class="ticket">
        <div class="name"><?= htmlspecialchars($name) ?></div>
        <svg id="barcode"></svg>
        <div class="price">฿<?= number_format($price, 2) ?></div>
    </div>

    <script>
        JsBarcode("#barcode", "<?= $sku ?>", {
            format: "CODE128",
            lineColor: "#000",
            width: 2,
            height: 80,
            displayValue: true,
            fontSize: 16
        });
    </script>
</body>
</html>