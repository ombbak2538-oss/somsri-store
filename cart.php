<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;

if (!empty($cart)) {
  $ids = implode(",", array_map('intval', array_keys($cart)));
  $rs = $conn->query("SELECT id, name, price, image, stock FROM products WHERE id IN ($ids)");
  while($row = $rs->fetch_assoc()){
    $qty = max(1, (int)$cart[$row['id']]);
    $row['qty'] = $qty;
    $row['sum'] = $row['price'] * $qty;
    $total += $row['sum'];
    $items[] = $row;
  }
}

$FREE_SHIP_MIN = 500;

$zones = $conn->query("SELECT * FROM shipping_zones ORDER BY id");
$selectedZone = $_SESSION['shipping_zone_id'] ?? 0;
$shippingFee = 0;
if ($selectedZone) {
  $z = $conn->prepare("SELECT fee FROM shipping_zones WHERE id=?");
  $z->bind_param("i", $selectedZone);
  $z->execute();
  $shippingFee = (float)($z->get_result()->fetch_assoc()['fee'] ?? 0);
}

if($total >= $FREE_SHIP_MIN){
  $shippingFee = 0;
}
$grand = $total + $shippingFee;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>ตะกร้าสินค้า | ร้านสมศรี</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
body{
  min-height:100vh;
  background: radial-gradient(circle at 50% 20%, #ffb3d9, #c83b77 60%, #a12861 100%);
  font-family:'Kanit',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
}
.page-wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }

.cart-card{
  width:100%; max-width:1100px;
  background: linear-gradient(180deg, #fde7f1, #f7cadd);
  border-radius:20px; 
  padding:22px 22px 26px;
  box-shadow:0 24px 50px rgba(0,0,0,.28);
  border:1px solid rgba(255,255,255,.8);
}

.cart-title{ 
  text-align:center; 
  font-weight:800; 
  color:#a12861; 
  margin-bottom:16px; 
  letter-spacing:.3px;
}

.cart-item{
  display:grid; 
  grid-template-columns:26px 72px 1fr auto auto;
  gap:12px; 
  align-items:center; 
  padding:14px 12px;
  border-radius:14px; 
  background:#fff;
  box-shadow:0 8px 18px rgba(0,0,0,.12); 
  margin-bottom:10px;
}
.cart-thumb{ 
  width:72px;height:72px;
  object-fit:cover;border-radius:12px;
  border:1px solid #eee; 
  background:#fafafa;
}

.qty-box{ 
  display:flex; 
  align-items:center; 
  gap:8px; 
}
.qty-btn{ 
  width:34px;height:34px;
  border:none;border-radius:10px;
  background:#f3c1da;
  font-weight:800; 
  cursor:pointer; 
  line-height:1;
}
.qty-btn:hover{ filter:brightness(.95); }

.qty-input{ 
  width:60px; 
  height:34px;
  text-align:center; 
  border-radius:10px; 
  border:1px solid #f0a8c9;
  font-weight:600;
}

.price{ 
  color:#c83b77; 
  font-weight:800; 
  min-width:88px; 
  text-align:right;
}

.badge-low{ 
  background:#fff3cd; 
  color:#664d03; 
  border:1px dashed #ffecb5; 
  font-size:11px;
}

.sum-box{
  background:#fff; 
  border-radius:18px; 
  padding:16px 16px 18px;
  box-shadow:0 14px 28px rgba(0,0,0,.18);
}

.sum-box h6{
  font-weight:800;
  margin-bottom:10px;
}

.btn-pink{
  background: linear-gradient(180deg, #ff8fc7, #c83b77);
  color:#fff;
  font-weight:800;
  border:none;
  border-radius:14px;
  padding:12px 14px;
  font-size:16px;
  box-shadow:0 12px 22px rgba(200,59,119,.45);
}
.btn-pink:hover{ filter:brightness(1.05); }

.progress{ 
  background:#f3c1da; 
  border-radius:999px; 
  overflow:hidden;
}
.progress-bar{ border-radius:999px; }

@media (min-width: 992px){
  .sum-box{ 
    position: sticky; 
    top: 24px; 
  }
}

@media (max-width: 576px){
  .cart-item{
    grid-template-columns:22px 56px 1fr;
    gap:10px;
  }
  .price{ text-align:left; min-width:auto; }
  .qty-box{ justify-content:flex-start; }
}

.cart-item{
  align-items: center; 
}

.cart-item .btn-outline-danger{
  min-width: 44px;
  min-height: 36px;
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 13px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.cart-item .qty-box{
  margin-right: 8px;
}

.cart-item .price{
  min-width: 90px;   
  text-align: right; 
  font-size: 16px;
}

.qty-btn{
  width: 32px;
  height: 32px;
  border-radius: 10px;
  font-size: 16px;
}

.qty-input{
  width: 64px;
  height: 32px;
  font-size: 14px;
}

@media (max-width: 576px){
  .cart-item{
    grid-template-columns: 20px 56px 1fr auto auto;
    gap: 8px;
    padding: 8px;
  }
  .cart-item .price{
    font-size: 14px;
    min-width: 80px;
  }
  .cart-item .btn-outline-danger{
    min-width: 40px;
    min-height: 32px;
    font-size: 12px;
  }
}

.cart-item{
  transition: transform .15s ease, box-shadow .15s ease;
}
.cart-item:hover{
  transform: translateY(-2px);
  box-shadow: 0 10px 22px rgba(0,0,0,.12);
}

</style>
</head>
<body>

<div class="page-wrap">
  <div class="cart-card">
    <div class="cart-title">🛒 ตะกร้าสินค้า</div>

    <div class="row g-3">
      <div class="col-12 col-lg-8">

        <?php if(empty($items)): ?>
          <div class="alert alert-light text-center py-4">ยังไม่มีสินค้าในตะกร้า</div>
        <?php else: ?>

          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <input type="checkbox" id="checkAll" checked> เลือกทั้งหมด
            </div>
            <button class="btn btn-sm btn-outline-danger" id="removeSelected">ลบที่เลือก</button>
          </div>

          <?php foreach($items as $it): ?>
            <div class="cart-item" data-id="<?= $it['id'] ?>" data-price="<?= $it['price'] ?>">
              <input type="checkbox" class="item-check" checked>
              <img class="cart-thumb" src="uploads/products/<?= htmlspecialchars($it['image']) ?>" onerror="this.src='assets/no-image.png'">
              <div>
                <div class="fw-semibold"><?= htmlspecialchars($it['name']) ?></div>
                <div class="small text-muted">ราคา/ชิ้น ฿<?= number_format($it['price'],2) ?></div>
                <?php if((int)$it['stock'] <= 5): ?>
                  <span class="badge badge-low">ใกล้หมด (เหลือ <?= (int)$it['stock'] ?>)</span>
                <?php endif; ?>
              </div>
              <div class="qty-box">

                <input class="qty-input" type="number" min="1" value="<?= $it['qty'] ?>">

              </div>
              <div class="price item-sum"><?= number_format($it['sum'],2) ?> ฿</div>
              <a href="update-cart.php?id=<?= $it['id'] ?>&act=remove" class="btn btn-sm btn-outline-danger">ลบ</a>
            </div>
          <?php endforeach; ?>

        <?php endif; ?>


      </div>

      <div class="col-12 col-lg-4">
        <div class="sum-box">
          <h6 class="fw-bold mb-2">สรุปคำสั่งซื้อ</h6>

          <div id="shipPromo" class="alert alert-info py-2 small mb-2">
            🚚 ฟรีค่าส่งเมื่อยอดเกิน <b id="freeShipMin"><?= number_format($FREE_SHIP_MIN) ?></b> บาท
            <div class="progress mt-1" style="height:6px">
              <div id="freeShipBar" class="progress-bar bg-success" style="width:0%"></div>
            </div>
            <div id="freeShipHint" class="mt-1"></div>
          </div>

          <div class="d-flex justify-content-between mb-1">
            <span>ราคาสินค้า</span>
            <strong id="sumItems"><?= number_format($total,2) ?> ฿</strong>
          </div>

          <form method="post" action="set-shipping.php" class="mb-2">
            <label class="small mb-1">เลือกพื้นที่จัดส่ง</label>
            <select name="shipping_zone_id" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">-- เลือกพื้นที่ --</option>
              <?php while($z = $zones->fetch_assoc()): ?>
                <option value="<?= (int)$z['id'] ?>" <?= $selectedZone==$z['id']?'selected':'' ?>>
                  <?= htmlspecialchars($z['area_name']) ?> (+<?= number_format($z['fee'],2) ?> ฿)
                </option>
              <?php endwhile; ?>
            </select>
          </form>

          <div class="d-flex justify-content-between mb-1">
            <span>ค่าส่ง</span>
            <strong id="shipFee"><?= number_format($shippingFee,2) ?> ฿</strong>
          </div>

          <div class="border rounded-3 p-2 small mb-2 bg-light">
            <div>✅ จัดส่งเร็ว 1–2 วัน</div>
            <div>🔄 คืนเงิน/เปลี่ยนได้ตามนโยบาย</div>
          </div>

          <hr>
          <div class="d-flex justify-content-between mb-3">
            <span class="fw-bold">ยอดชำระ</span>
            <span class="fw-bold text-danger" id="grandTotal"><?= number_format($grand,2) ?> ฿</span>
          </div>

          <?php if(empty($_SESSION['user_id'])): ?>
            <a href="login.php?next=cart.php" class="btn btn-pink w-100 mb-2">เข้าสู่ระบบเพื่อสั่งซื้อ</a>
          <?php else: ?>
            <a href="checkout.php" class="btn btn-pink w-100 mb-2">สั่งซื้อ</a>
          <?php endif; ?>

          <a href="index.php" class="btn btn-outline w-100">เลือกซื้อเพิ่ม</a>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
const checks = document.querySelectorAll('.item-check');
const checkAll = document.getElementById('checkAll');
const sumItems = document.getElementById('sumItems');
const shipFee = document.getElementById('shipFee');
const grandTotal = document.getElementById('grandTotal');
const FREE_SHIP_MIN = <?= (int)$FREE_SHIP_MIN ?>;

function updateFreeShipUI(sum){
  const bar = document.getElementById('freeShipBar');
  const hint = document.getElementById('freeShipHint');
  if(!bar || !hint) return;

  if(sum >= FREE_SHIP_MIN){
    bar.style.width = '100%';
    hint.innerHTML = '🎉 คุณได้รับ <b>ฟรีค่าส่ง</b> แล้ว';
  }else{
    const pct = Math.min(100, Math.round((sum/FREE_SHIP_MIN)*100));
    bar.style.width = pct + '%';
    hint.innerHTML = 'เพิ่มอีก <b>' + (FREE_SHIP_MIN - sum).toLocaleString() + '</b> บาท เพื่อรับฟรีค่าส่ง';
  }
}

function recalc(){
  let sum = 0;
  document.querySelectorAll('.cart-item').forEach(row=>{
    const checked = row.querySelector('.item-check').checked;
    if(!checked) return;
    const price = parseFloat(row.dataset.price);
    const qty = parseInt(row.querySelector('.qty-input').value || 1);
    sum += price * qty;
    row.querySelector('.item-sum').textContent =
      (price*qty).toLocaleString(undefined,{minimumFractionDigits:2})+' ฿';
  });
  const ship = parseFloat(shipFee.textContent.replace(/[^\d.]/g,'')) || 0;
  sumItems.textContent = sum.toLocaleString(undefined,{minimumFractionDigits:2})+' ฿';
  grandTotal.textContent = (sum + ship).toLocaleString(undefined,{minimumFractionDigits:2})+' ฿';
  updateFreeShipUI(sum);
}

async function saveQty(id, qty){
  await fetch('cart-ajax.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'id='+id+'&qty='+qty
  });
}

document.querySelectorAll('.plus').forEach(btn=>{
  btn.onclick = e => {
    const row = e.target.closest('.cart-item');
    const input = row.querySelector('.qty-input');
    input.value = Math.max(1, parseInt(input.value||1) + 1);
    saveQty(row.dataset.id, input.value);
    recalc();
  }
});
document.querySelectorAll('.minus').forEach(btn=>{
  btn.onclick = e => {
    const row = e.target.closest('.cart-item');
    const input = row.querySelector('.qty-input');
    input.value = Math.max(1, parseInt(input.value||1) - 1);
    saveQty(row.dataset.id, input.value);
    recalc();
  }
});
document.querySelectorAll('.qty-input').forEach(inp=>{
  inp.onchange = e => {
    const row = e.target.closest('.cart-item');
    saveQty(row.dataset.id, e.target.value);
    recalc();
  };
});

checkAll?.addEventListener('change', ()=>{
  checks.forEach(c=> c.checked = checkAll.checked);
  recalc();
});
checks.forEach(c=> c.addEventListener('change', recalc));

document.getElementById('removeSelected')?.addEventListener('click', ()=>{
  const ids = [];
  document.querySelectorAll('.cart-item').forEach(row=>{
    if(row.querySelector('.item-check').checked) ids.push(row.dataset.id);
  });
  if(ids.length===0) return alert('กรุณาเลือกรายการที่จะลบ');
  location.href = 'update-cart.php?act=remove_multi&ids=' + ids.join(',');
});

recalc();
</script>

</body>
</html>