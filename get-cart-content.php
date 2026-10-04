<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/config/db.php";

$cart = $_SESSION['cart'] ?? [];
$totalPrice = 0;

if (empty($cart)) {
    echo '<div class="text-center py-5">
            <h1 class="display-1 opacity-25">🛒</h1>
            <p class="text-muted">ตะกร้าของคุณยังว่างเปล่า</p>
            <a href="index.php" class="btn btn-outline-danger btn-sm rounded-pill">ไปช้อปเลย</a>
          </div>';
    exit;
}

$ids = implode(',', array_map('intval', array_keys($cart)));
$sql = "SELECT id, name, price, image, discount_percent FROM products WHERE id IN ($ids)";
$result = $conn->query($sql);
?>

<div class="cart-items">
    <?php while ($p = $result->fetch_assoc()): 
        $id = $p['id'];
        $qty = $cart[$id];
        $price = (float)$p['price'];
        $discount = (int)$p['discount_percent'];
        $finalPrice = $discount > 0 ? $price * (100 - $discount) / 100 : $price;
        $subtotal = $finalPrice * $qty;
        $totalPrice += $subtotal;
    ?>
    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
        <img src="uploads/products/<?= htmlspecialchars($p['image'] ?: 'no-image.png') ?>" 
             class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
        
        <div class="ms-3 flex-grow-1">
            <div class="fw-bold small text-truncate" style="max-width: 180px;"><?= htmlspecialchars($p['name']) ?></div>
            <div class="text-hp-orange fw-bold">฿<?= number_format($finalPrice, 2) ?></div>
            <div class="d-flex align-items-center mt-1">
                <small class="text-muted">จำนวน: <?= $qty ?></small>
            </div>
        </div>
        
        <div class="text-end">
            <div class="fw-bold">฿<?= number_format($subtotal, 2) ?></div>
            <button class="btn btn-sm text-danger p-0" onclick="removeFromCart(<?= $id ?>)">ลบ</button>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<div class="cart-footer mt-4">
    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
        <span>ยอดรวมทั้งหมด:</span>
        <span class="text-hp-orange">฿<?= number_format($totalPrice, 2) ?></span>
    </div>
    <a href="checkout.php" class="btn btn-danger w-100 py-2 rounded-pill fw-bold shadow-sm">
        สั่งซื้อสินค้าเลย
    </a>
</div>