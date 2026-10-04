<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ไม่พบสินค้า");
}

/* ---------- ดึงข้อมูลสินค้า ---------- */
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    die("ไม่พบสินค้า");
}

/* ---------- คำนวณราคา ---------- */
$price = (float)$product['price'];
$discount = (int)($product['discount_percent'] ?? 0);
$finalPrice = $discount > 0 ? $price * (100 - $discount) / 100 : $price;

/* ---------- สินค้าที่เกี่ยวข้อง ---------- */
$relatedStmt = $conn->prepare("
SELECT id,name,price,image
FROM products
WHERE category_id = ? AND id <> ?
LIMIT 6
");
$relatedStmt->bind_param("ii", $product['category_id'], $product['id']);
$relatedStmt->execute();
$related = $relatedStmt->get_result();
$relatedStmt->close();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($product['name']) ?></title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
    font-family:'Kanit',sans-serif;
    background:#f2f2f7;
}

/* iOS glass card */
.ios-card{
    background:rgba(255,255,255,.85);
    backdrop-filter:blur(20px);
    border-radius:28px;
    box-shadow:0 20px 40px rgba(0,0,0,.05);
}

.product-img{
    width:100%;
    border-radius:22px;
    object-fit:cover;
}

.price{
    font-size:38px;
    font-weight:700;
}

.old-price{
    text-decoration:line-through;
    color:#999;
}

.qty-box{
    display:flex;
    align-items:center;
    gap:10px;
}

.qty-btn{
    width:36px;
    height:36px;
    border-radius:12px;
    border:none;
    background:#eee;
}

.add-cart{
    border-radius:20px;
    padding:14px;
    font-weight:600;
}

.buy-now{
    border-radius:20px;
    padding:14px;
    font-weight:600;
    background:#007aff;
    color:#fff;
}

.related-card{
    border-radius:18px;
    overflow:hidden;
    background:#fff;
    transition:.3s;
}

.related-card:hover{
    transform:translateY(-4px);
}

</style>
</head>

<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<div class="container py-5">
<div class="ios-card p-4">

<div class="row g-5">

<!-- รูปสินค้า -->
<div class="col-md-6">
<img
class="product-img"
src="uploads/products/<?= htmlspecialchars($product['image'] ?: 'no-image.png') ?>"
alt="<?= htmlspecialchars($product['name']) ?>">
</div>

<!-- รายละเอียด -->
<div class="col-md-6">

<div class="small text-primary mb-2">
<a href="index.php">หน้าหลัก</a> / รายละเอียดสินค้า
</div>

<h2 class="fw-bold"><?= htmlspecialchars($product['name']) ?></h2>

<div class="text-warning mb-2">
<i class="bi bi-star-fill"></i>
<i class="bi bi-star-fill"></i>
<i class="bi bi-star-fill"></i>
<i class="bi bi-star-fill"></i>
<i class="bi bi-star"></i>
<span class="text-muted">(0 รีวิว)</span>
</div>

<div class="price mb-2">
฿<?= number_format($finalPrice,2) ?>

<?php if ($discount > 0): ?>
<span class="old-price fs-6 ms-2">
฿<?= number_format($price,2) ?>
</span>
<?php endif; ?>

</div>

<div class="text-muted mb-3">
เหลือในคลัง: <?= (int)$product['stock'] ?> ชิ้น
</div>

<!-- เลือกจำนวน -->
<div class="qty-box mb-4">
<button class="qty-btn" onclick="decQty()">−</button>

<input
id="qty"
type="number"
value="1"
min="1"
max="<?= (int)$product['stock'] ?>"
class="form-control w-25 text-center">

<button class="qty-btn" onclick="incQty()">+</button>
</div>

<div class="d-flex gap-3 mb-4">

<button
class="btn btn-dark add-cart w-100"
onclick="addCart()">
<i class="bi bi-cart"></i>
ใส่รถเข็น
</button>

<button
class="btn buy-now w-100"
onclick="buyNow()">
ซื้อเลย
</button>

</div>

<hr>

<h6 class="fw-bold mb-3">
<i class="bi bi-info-circle"></i>
รายละเอียดสินค้า
</h6>

<div class="text-muted">
<?= nl2br(htmlspecialchars($product['description'] ?: 'ไม่มีรายละเอียดเพิ่มเติม')) ?>
</div>

</div>

</div>
</div>
</div>

<!-- สินค้าที่เกี่ยวข้อง -->
<div class="container pb-5">

<h4 class="fw-bold mb-4">สินค้าที่เกี่ยวข้อง</h4>

<div class="row g-4">

<?php while($r = $related->fetch_assoc()): ?>
<div class="col-6 col-md-2">
<div class="related-card p-2">

<a href="product-detail.php?id=<?= $r['id'] ?>">
<img
class="img-fluid"
src="uploads/products/<?= htmlspecialchars($r['image'] ?: 'no-image.png') ?>">
</a>

<div class="small mt-2 text-truncate">
<?= htmlspecialchars($r['name']) ?>
</div>

<div class="fw-bold">
฿<?= number_format($r['price'],2) ?>
</div>

</div>
</div>
<?php endwhile; ?>

</div>
</div>

<script>

function incQty(){
const q=document.getElementById("qty");
q.value=parseInt(q.value)+1;
}

function decQty(){
const q=document.getElementById("qty");
if(q.value>1) q.value--;
}

async function addCart(){
const qty=document.getElementById("qty").value;
await fetch(`update-cart.php?id=<?= $product['id'] ?>&qty=${qty}&act=add_ajax`);
alert("เพิ่มสินค้าแล้ว");
}

function buyNow(){
const qty=document.getElementById("qty").value;
location.href=`checkout.php?id=<?= $product['id'] ?>&qty=${qty}`;
}

</script>

</body>
</html>