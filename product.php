<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  header("Location: index.php");
  exit;
}

$stmt = $conn->prepare("
  SELECT p.*, c.name AS cat_name 
  FROM products p 
  LEFT JOIN categories c ON p.category_id=c.id 
  WHERE p.id=?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();

if (!$p) {
  echo "ไม่พบสินค้า";
  exit;
}

$stock = (int)($p['stock'] ?? 0);
$sold  = (int)($p['sold'] ?? 0);
$isHot = $sold >= 10;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($p['name']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/somsri-store/public/assets/css/ios26.css">
<style>
.product-img{ width:100%; aspect-ratio:1/1; object-fit:cover; }
.price{
  font-size:28px; font-weight:900;
  background: linear-gradient(90deg, #ff9f0a, #ff375f);
  -webkit-background-clip:text; background-clip:text; color:transparent;
}
.stock-pill{
  display:inline-block; padding:6px 14px; border-radius:999px; font-weight:600;
  background: linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.7));
  border:1px solid rgba(0,0,0,.08);
  box-shadow: 0 6px 14px rgba(0,0,0,.15);
}
.stock-low{ color:#ff375f; }
.stock-ok{ color:#30d158; }

.badge-hot{
  background: linear-gradient(180deg, #ff9f0a, #ff375f);
  color:#fff; font-weight:800; padding:6px 12px; border-radius:999px;
  box-shadow: 0 10px 22px rgba(255,55,95,.35);
}

.qty-group .btn{ width:44px; height:44px; font-size:20px; }
</style>
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<div class="container py-4">
  <div class="row g-3 ios-glass p-3 p-md-4 position-relative">

    <?php if($isHot): ?>
      <div class="position-absolute" style="top:12px; right:12px; z-index:2;">
        <span class="badge-hot">🔥 ขายดี</span>
      </div>
    <?php endif; ?>

    <div class="col-md-5">
      <img src="uploads/products/<?= htmlspecialchars($p['image'] ?: 'no-image.png') ?>" 
           class="product-img rounded-4 shadow"
           onerror="this.src='uploads/products/no-image.png'">
    </div>

    <div class="col-md-7 d-flex flex-column">
      <h3 class="fw-bold mb-2"><?= htmlspecialchars($p['name']) ?></h3>

      <div class="price mb-2">฿<?= number_format($p['price'],2) ?></div>

      <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
        <span class="text-muted">หมวดหมู่: <?= htmlspecialchars($p['cat_name'] ?? '-') ?></span>
        <span class="stock-pill <?= $stock <= 5 ? 'stock-low' : 'stock-ok' ?>">
          คงเหลือ: <?= number_format($stock) ?> ชิ้น
        </span>
      </div>

      <div class="mb-4" style="white-space:pre-line;">
        <?= htmlspecialchars($p['description'] ?? '') ?>
      </div>

      <div class="mt-auto d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group qty-group" style="width:160px">
          <button class="btn ios-btn" type="button" onclick="dec()">−</button>
          <input id="qty" type="number" class="form-control text-center ios-pill" value="1" min="1" max="<?= max(1,$stock) ?>">
          <button class="btn ios-btn" type="button" onclick="inc(<?= $stock ?>)">+</button>
        </div>

        <button class="btn btn-danger px-4 py-2" 
                onclick="addToCart(<?= (int)$p['id'] ?>)" 
                <?= $stock <= 0 ? 'disabled' : '' ?>>
          🛒 ใส่ตะกร้า
        </button>

        <?php if($stock <= 0): ?>
          <span class="badge bg-secondary">สินค้าหมด</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function inc(max){
  const el = document.getElementById('qty');
  let v = parseInt(el.value||1,10);
  if(v < max) el.value = v + 1;
}
function dec(){
  const el = document.getElementById('qty');
  let v = parseInt(el.value||1,10);
  if(v > 1) el.value = v - 1;
}
function addToCart(id){
  const qty = document.getElementById('qty').value || 1;
  fetch('add-to-cart.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id+'&qty='+qty
  })
  .then(r => r.json())
  .then(res => {
    if(res.ok){
      alert('เพิ่มสินค้าเข้าตะกร้าแล้ว 🛒');
    } else {
      alert('ไม่สามารถเพิ่มสินค้าได้');
    }
  })
  .catch(() => alert('เชื่อมต่อเซิร์ฟเวอร์ไม่ได้'));
}
</script>

</body>
</html>