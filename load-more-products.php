<?php
require_once __DIR__ . "/../config/db.php";

$keyword     = $_GET['keyword'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$min_price   = $_GET['min_price'] ?? '';
$max_price   = $_GET['max_price'] ?? '';
$in_stock    = isset($_GET['in_stock']) ? 1 : 0;

$page = intval($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page-1)*$limit;

$sql = "SELECT p.* FROM products p WHERE 1 ";

if ($keyword !== '') {
    $kw = $conn->real_escape_string($keyword);
    $sql .= " AND p.name LIKE '%$kw%'";
}

if ($category_id !== '') $sql .= " AND p.category_id = " . intval($category_id);
if ($min_price   !== '') $sql .= " AND p.price >= " . floatval($min_price);
if ($max_price   !== '') $sql .= " AND p.price <= " . floatval($max_price);
if ($in_stock)           $sql .= " AND p.stock > 0";

$sql .= " ORDER BY p.id DESC LIMIT $limit OFFSET $offset";

$res = $conn->query($sql);

while ($p = $res->fetch_assoc()):

$price = (float)$p['price'];
$stock = (int)$p['stock'];
$discount = (int)($p['discount_percent'] ?? 0);
$finalPrice = $discount > 0 ? $price * (100 - $discount) / 100 : $price;
?>

<div class="col-6 col-md-4">
<div class="ios-product-card shadow-sm h-100 d-flex flex-column <?= $stock <= 0 ? 'out-of-stock' : '' ?>">
<div class="img-container">

<a href="product-detail.php?id=<?= $p['id'] ?>">
<img src="uploads/products/<?= htmlspecialchars($p['image'] ?: 'no-image.png') ?>" class="img-fluid">
</a>

<?php if($discount > 0): ?>
<div class="ios-badge-discount">-<?= $discount ?>%</div>
<?php endif; ?>

</div>

<div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">

<div class="product-name text-truncate mb-2">
<?= htmlspecialchars($p['name']) ?>
</div>

<div class="d-flex justify-content-between align-items-center">

<div class="price-container">
<div class="price-final">฿<?= number_format($finalPrice,2) ?></div>

<?php if($discount > 0): ?>
<span class="price-original">฿<?= number_format($price,0) ?></span>
<?php endif; ?>
</div>

<?php if($stock > 0): ?>
<button class="btn btn-outline-dark btn-sm rounded-pill px-3 add-cart-btn" data-id="<?= $p['id'] ?>">
<i class="bi bi-cart-plus"></i>
</button>
<?php else: ?>
<span class="badge rounded-pill bg-secondary-subtle text-secondary border px-3 py-2" style="font-size:10px;">
หมดสต็อก
</span>
<?php endif; ?>

</div>
</div>
</div>
</div>

<?php endwhile; ?>