<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = "เพิ่มสินค้า & จัดการราคา";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require_once __DIR__ . "/../../config/db.php";

$error = null;
$success = isset($_GET['success']);

$cats = $conn->query("SELECT * FROM categories ORDER BY name");
$branches = $conn->query("SELECT id, name FROM branches ORDER BY id ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode         = trim($_POST['barcode'] ?? '');
    $name            = trim($_POST['name'] ?? '');
    $size            = trim($_POST['size'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $cost_price      = floatval($_POST['cost_price'] ?? 0);
    $wholesale_price = floatval($_POST['wholesale_price'] ?? 0);
    $retail_price    = floatval($_POST['retail_price'] ?? 0);
    $old_price       = !empty($_POST['old_price']) ? floatval($_POST['old_price']) : null;
    $catId           = intval($_POST['category_id'] ?? 0);
    $alert           = intval($_POST['alert_stock'] ?? 5);
    $branch_stocks   = $_POST['branch_stocks'] ?? [];
    $total_stock     = array_sum($branch_stocks);

    if ($name !== '' && $retail_price > 0 && $catId > 0) {
        $conn->begin_transaction();
        try {
            $imgName = null;
            if (!empty($_FILES['image']['name'])) {
                $uploadDir = __DIR__ . "/../uploads/products/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $imgName = time() . "_" . random_int(1000,9999) . "." . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imgName);
            }

            $stmt = $conn->prepare("INSERT INTO products 
                (barcode, name, size, description, cost_price, wholesale_price, price, old_price, stock, image, category_id, alert_stock) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssddddisii", 
                $barcode, $name, $size, $description, $cost_price, $wholesale_price, $retail_price, 
                $old_price, $total_stock, $imgName, $catId, $alert);
            $stmt->execute();
            $newProductId = $conn->insert_id;

            $stmtStock = $conn->prepare("INSERT INTO branch_stocks (branch_id, product_id, qty) VALUES (?, ?, ?)");
            foreach ($branch_stocks as $branchId => $qty) {
                $qty = (int)$qty;
                if ($qty > 0) {
                    $stmtStock->bind_param("iii", $branchId, $newProductId, $qty);
                    $stmtStock->execute();
                }
            }
            $conn->commit();
            echo "<script>window.location.href='add-product.php?success=1';</script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<script src="https://unpkg.com/html5-qrcode"></script>

<style>
    :root { --hp-blue: #003876; --hp-orange: #ff6b00; }
    body { background-color: #f4f7f6; }
    .card-soft { background: #fff; border-radius: 20px; padding: 25px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .price-card { border-radius: 12px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; transition: 0.3s; }
    .price-card:focus-within { border-color: var(--hp-blue); background: #fff; }
    #barcodeInput:focus { background-color: #fff9e6; border: 2px solid var(--hp-orange); box-shadow: none; }
    #reader { width: 100%; border-radius: 15px; overflow: hidden; border: 2px solid #eee; display: none; margin-top: 15px; }
    .form-label { color: #4a5568; margin-bottom: 0.5rem; }
    textarea.form-control { border-radius: 12px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0" style="color: var(--hp-blue);"><i class="bi bi-plus-circle-fill me-2"></i>เพิ่มสินค้าใหม่</h4>
        <a href="products.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3"><i class="bi bi-arrow-left"></i> กลับหน้ารายการ</a>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><?= $error ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> บันทึกข้อมูลสินค้าเรียบร้อยแล้ว!</div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="productForm">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card-soft mb-4">
                    <h5 class="fw-bold mb-4" style="color: var(--hp-blue);">📦 ข้อมูลพื้นฐานสินค้า</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">รหัสบาร์โค้ด</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" id="barcodeInput" class="form-control" placeholder="สแกนหรือพิมพ์รหัส..." autocomplete="off">
                                <button type="button" id="btn-camera" class="btn btn-dark" onclick="toggleCamera()"><i class="bi bi-camera"></i></button>
                            </div>
                            <div id="reader"></div>
                            <button type="button" id="btn-stop" class="btn btn-danger btn-sm w-100 mt-2" style="display:none;" onclick="stopScanner()">ปิดกล้องสแกนเนอร์</button>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">หมวดหมู่สินค้า</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                <?php $cats->data_seek(0); while($c = $cats->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">ชื่อสินค้า</label>
                            <input type="text" name="name" id="nameInput" class="form-control" placeholder="ระบุชื่อสินค้าให้ชัดเจน" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">ขนาด/หน่วย</label>
                            <input type="text" name="size" class="form-control" placeholder="เช่น 500มล. / แพ็ค 6">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">รายละเอียดสินค้า</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="ระบุสรรพคุณ ส่วนประกอบ หรือข้อมูลเพิ่มเติม..."></textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold mt-5 mb-4" style="color: var(--hp-blue);">💰 การตั้งค่าราคา (บาท)</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="price-card" style="border-top: 4px solid #dc3545;">
                                <label class="small fw-bold text-danger">ราคาทุน</label>
                                <input type="number" step="0.01" name="cost_price" id="cost_price" class="form-control border-0 bg-transparent fs-5 fw-bold" placeholder="0.00" oninput="autoCalculate()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="price-card" style="border-top: 4px solid var(--hp-blue);">
                                <label class="small fw-bold text-primary">ราคาส่ง (+15%)</label>
                                <input type="number" step="0.01" name="wholesale_price" id="wholesale_price" class="form-control border-0 bg-transparent fs-5 fw-bold" placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="price-card" style="border-top: 4px solid #28a745;">
                                <label class="small fw-bold text-success">ราคาปลีก (+30%)</label>
                                <input type="number" step="0.01" name="retail_price" id="retail_price" class="form-control border-0 bg-transparent fs-5 fw-bold" placeholder="0.00" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card-soft mb-4 text-center">
                    <h5 class="fw-bold mb-3">🖼️ รูปภาพสินค้า</h5>
                    <div id="previewContainer" style="width:100%; height:220px; border:2px dashed #cbd5e0; border-radius:15px; display:flex; align-items:center; justify-content:center; cursor:pointer; overflow:hidden; background:#fdfdfd;" onclick="document.getElementById('imgInp').click()">
                        <img id="imgPreview" style="width:100%; height:100%; object-fit:contain; display:none;">
                        <div id="placeholderText" class="text-center">
                            <i class="bi bi-image fs-1 text-muted"></i>
                            <p class="small text-muted mb-0">คลิกเพื่ออัปโหลดรูปภาพ</p>
                        </div>
                    </div>
                    <input type="file" name="image" id="imgInp" class="d-none" accept="image/*" onchange="previewFile()">
                </div>
                
                <div class="card-soft">
                    <h5 class="fw-bold mb-3">🏢 จัดการสต็อกเริ่มต้น</h5>
                    <div class="stock-list" style="max-height: 300px; overflow-y: auto;">
                        <?php $branches->data_seek(0); while($b = $branches->fetch_assoc()): ?>
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <span class="small text-dark fw-medium"><?= htmlspecialchars($b['name']) ?></span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <input type="number" name="branch_stocks[<?= $b['id'] ?>]" class="form-control text-center" value="0" min="0">
                                <span class="input-group-text bg-light">ชิ้น</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label class="form-label small fw-bold">แจ้งเตือนเมื่อสินค้าต่ำกว่า (ชิ้น)</label>
                        <input type="number" name="alert_stock" class="form-control form-control-sm" value="5">
                    </div>

                    <button type="submit" class="btn btn-warning w-100 py-3 fw-bold text-white shadow" style="background:var(--hp-orange); border:none; border-radius:12px;">
                        <i class="bi bi-save2 me-2"></i> บันทึกสินค้าใหม่
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>



<script>
let html5QrCode;

async function toggleCamera() {
    const reader = document.getElementById('reader');
    const btnStop = document.getElementById('btn-stop');
    
    if (reader.style.display === 'none' || reader.style.display === '') {
        reader.style.display = 'block';
        btnStop.style.display = 'block';
        
        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 20, qrbox: { width: 250, height: 150 } };
        
        html5QrCode.start({ facingMode: "environment" }, config, (text) => {
            document.getElementById('barcodeInput').value = text;
            stopScanner();
            document.getElementById('nameInput').focus();
            if(navigator.vibrate) navigator.vibrate(100);
        }).catch(err => console.error("Camera Error:", err));
    } else {
        stopScanner();
    }
}

function stopScanner() {
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            document.getElementById('reader').style.display = 'none';
            document.getElementById('btn-stop').style.display = 'none';
        }).catch(err => console.log(err));
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const bc = document.getElementById('barcodeInput');
    bc.focus();
    bc.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            if(bc.value.trim() !== "") document.getElementById('nameInput').focus();
        }
    });
});

function autoCalculate() {
    const cost = parseFloat(document.getElementById('cost_price').value) || 0;
    if (cost > 0) {
        document.getElementById('wholesale_price').value = (Math.ceil(cost * 1.15)).toFixed(2);
        document.getElementById('retail_price').value = (Math.ceil(cost * 1.30)).toFixed(2);
    }
}

function previewFile() {
    const file = document.getElementById('imgInp').files[0];
    const preview = document.getElementById('imgPreview');
    const placeholder = document.getElementById('placeholderText');
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php require __DIR__ . "/partials/footer.php"; ?>