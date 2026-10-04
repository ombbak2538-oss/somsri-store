<?php
// ===== Guard =====
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . "/../_guard.php"; // ใช้ guard กลาง

$pageTitle = "POS ขายหน้าร้าน";
require __DIR__ . "/../_sidebar.php";
require __DIR__ . "/../_header.php"; // ถ้ามี header กลาง (ถ้าไม่มีลบได้)
?>

<main class="content">
  <div class="topbar mb-3 d-flex align-items-center gap-2">
    <button class="hamburger btn btn-light d-lg-none" onclick="toggleSidebar()">☰</button>
    <strong>🛒 POS ขายหน้าร้าน</strong>
    <span class="ms-auto small text-muted">ผู้ใช้งาน: <?= htmlspecialchars($_SESSION['username'] ?? '-') ?></span>
  </div>

  <div class="alert alert-warning d-none" id="offlineAlert">
    ⚠️ ออฟไลน์อยู่ – บิลจะถูกเก็บชั่วคราว และซิงค์เมื่อเน็ตกลับมา
  </div>

  <button class="btn btn-danger btn-lg w-100 mb-2" onclick="openScanner()">
    📷 สแกนบาร์โค้ดเพิ่มสินค้า
  </button>

  <div id="cart" class="mb-2"></div>

  <div class="card p-3 mb-2 shadow-sm rounded-4">
    <label class="form-label fw-semibold">วิธีชำระเงิน</label>
    <select id="payment_method" class="form-select mb-2">
      <option value="CASH">เงินสด</option>
      <option value="TRANSFER">โอน</option>
      <option value="QR">QR PromptPay</option>
    </select>

    <div id="cashBox">
      <label class="form-label">รับเงินสด (บาท)</label>
      <input type="number" id="paid_amount" class="form-control" placeholder="จำนวนเงินที่รับ">
    </div>
  </div>

  <div class="card p-3 mb-3 shadow-sm rounded-4 d-flex justify-content-between align-items-center">
    <div class="fs-4 fw-bold">รวม: ฿<span id="total">0.00</span></div>
    <button class="btn btn-success btn-lg" onclick="checkout()">ชำระเงิน</button>
  </div>

  <div id="result" class="alert d-none"></div>
</main>

<!-- Scanner Modal -->
<div class="modal fade" id="scanModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div id="scanner" style="width:100%; height:100vh; background:#000"></div>
      </div>
    </div>
  </div>
</div>

<button class="btn btn-outline-secondary d-none" id="syncBtn"
        style="position:fixed; right:16px; bottom:16px; z-index:1050"
        onclick="syncOfflineBills()">
  🔄 ซิงค์บิลออฟไลน์
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/@zxing/library@latest"></script>

<script>
let codeReader;
let cart = [];

function openScanner() {
  const modal = new bootstrap.Modal(document.getElementById('scanModal'));
  modal.show();

  codeReader = new ZXing.BrowserMultiFormatReader();
  codeReader.decodeFromVideoDevice(null, 'scanner', async (result, err) => {
    if (result) {
      codeReader.reset();
      modal.hide();
      addToCart(result.text);
    }
  });
}

async function addToCart(barcode) {
  const res = await fetch('/somsri_store_web/public/admin/products/check_barcode.php?barcode=' + barcode);
  const data = await res.json();

  if (!data.found) return alert('❌ ไม่พบสินค้า');

  const p = data.product;
  const exist = cart.find(i => i.barcode === barcode);
  if (exist) exist.qty++;
  else cart.push({ barcode, name: p.name, price: parseFloat(p.price), qty: 1 });

  renderCart();
}

function renderCart() {
  let html = '', total = 0;
  cart.forEach((i, idx) => {
    const sub = i.price * i.qty;
    total += sub;
    html += `
      <div class="border-bottom py-2">
        <b>${i.name}</b><br>
        ฿${i.price.toFixed(2)} x 
        <input type="number" value="${i.qty}" min="1" style="width:60px" onchange="cart[${idx}].qty=this.value;renderCart()">
        = ฿${sub.toFixed(2)}
        <button class="btn btn-sm btn-danger ms-2" onclick="cart.splice(${idx},1);renderCart()">ลบ</button>
      </div>
    `;
  });
  document.getElementById('cart').innerHTML = html || '<div class="text-muted text-center py-4">ยังไม่มีสินค้า</div>';
  document.getElementById('total').innerText = total.toFixed(2);
}

async function checkout() {
  const payload = { items: cart };
  const res = await fetch('/somsri_store_web/public/admin/products/pos_checkout.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const data = await res.json();

  if (data.ok) {
    alert('บันทึกบิลเรียบร้อย #' + data.sale_id);
    cart = [];
    renderCart();
  } else {
    alert('❌ ทำรายการไม่สำเร็จ');
  }
}
</script>