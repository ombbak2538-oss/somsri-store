<?php
// ตรวจสอบสถานะ Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. ดึงไฟล์ตรวจสิทธิ์การใช้งานระบบ
if (file_exists(__DIR__ . "/_guard.php")) {
    include_once __DIR__ . "/_guard.php";
} elseif (file_exists(__DIR__ . "/auth.php")) {
    include_once __DIR__ . "/auth.php";
}

// 2. ดึงไฟล์เชื่อมต่อฐานข้อมูลแบบอัตโนมัติ (รองรับโฟลเดอร์ 1 ชั้น และ 2 ชั้น)
if (file_exists(__DIR__ . "/../config/db.php")) {
    require_once __DIR__ . "/../config/db.php";
} elseif (file_exists(__DIR__ . "/../../config/db.php")) {
    require_once __DIR__ . "/../../config/db.php";
} else {
    die("<div style='color:red; padding:20px; font-family:sans-serif;'><b>ข้อผิดพลาด:</b> ไม่พบไฟล์ config/db.php</div>");
}

// หา Root Path สำหรับแสดงรูปภาพให้อัตโนมัติ
$base_url = (strpos($_SERVER['SCRIPT_NAME'], '/somsri-store/') !== false) ? '/somsri-store' : '/somsri_store_web';

// --- บันทึกข้อมูล (เพิ่ม/แก้ไข) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $sku = trim($_POST['sku'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $image = null;

    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $dir = __DIR__ . '/../uploads/products/';
        if (!is_dir($dir)) {
            $dir = __DIR__ . '/../../uploads/products/';
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($ext, $allowed)) {
            $fname = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dir . $fname)) {
                $image = $fname;
            }
        }
    }

    if ($id > 0) {
        if ($image) {
            $stmt = $conn->prepare("UPDATE products SET sku=?, name=?, price=?, stock=?, image=? WHERE id=?");
            $stmt->bind_param("ssdisi", $sku, $name, $price, $stock, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET sku=?, name=?, price=?, stock=? WHERE id=?");
            $stmt->bind_param("ssdii", $sku, $name, $price, $stock, $id);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO products (sku, name, price, stock, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdis", $sku, $name, $price, $stock, $image);
    }
    
    if (isset($stmt) && $stmt->execute()) {
        $_SESSION['flash'] = "บันทึกข้อมูลเรียบร้อยแล้ว";
    }
    header("Location: products.php"); 
    exit;
}

// --- ลบ/เพิ่มสต็อก ---
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products.php"); 
    exit;
}

if (isset($_GET['inc'])) {
    $id = (int)$_GET['inc'];
    $conn->query("UPDATE products SET stock = stock + 1 WHERE id = $id");
    header("Location: products.php"); 
    exit;
}

// ดึงข้อมูลสำหรับแก้ไข
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $edit = $res->fetch_assoc();
    }
}

// ดึงรายการสินค้าทั้งหมด
$rows = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จัดการสต็อกสินค้า - สมศรี Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f8f9fa; }
        .sidebar { width: 260px; height: 100vh; position: fixed; background: #1a1c23; color: #fff; padding: 20px 0; z-index: 1000; }
        .sidebar .nav-link { color: #9e9e9e; padding: 12px 25px; display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .sidebar .nav-link.active { color: #fff; background: rgba(255, 77, 109, 0.1); border-left: 4px solid #ff4d6d; }
        .main-content { margin-left: 260px; padding: 40px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .preview-img { width: 100%; height: 160px; border: 2px dashed #ddd; border-radius: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; }
        .preview-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
        @media (max-width: 992px) { .sidebar { display: none; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>

<?php 
if (file_exists(__DIR__ . "/_sidebar.php")) {
    include_once __DIR__ . "/_sidebar.php"; 
}
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">🛒 จัดการสต็อกสินค้า</h4>
        <?php if($edit): ?>
            <a href="products.php" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-plus-circle me-1"></i>ยกเลิกแก้ไข / เพิ่มสินค้าใหม่</a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- ฟอร์มเพิ่ม/แก้ไขสินค้า -->
        <div class="col-lg-4">
            <div class="card p-4">
                <h6 class="fw-bold mb-3"><?= $edit ? '✏️ แก้ไขข้อมูลสินค้า' : '➕ เพิ่มสินค้าใหม่' ?></h6>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="small text-muted mb-1">รหัสสินค้า (SKU / Barcode)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="sku" id="sku_input" value="<?= htmlspecialchars($edit['sku'] ?? '') ?>" placeholder="สแกนหรือกรอกรหัส">
                            <button class="btn btn-outline-primary" type="button" onclick="startScanner()"><i class="bi bi-camera"></i></button>
                        </div>
                        <div id="reader" style="display:none;" class="mt-2 rounded overflow-hidden"></div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted mb-1">ชื่อสินค้า</label>
                        <input class="form-control" name="name" value="<?= htmlspecialchars($edit['name'] ?? '') ?>" placeholder="ระบุชื่อสินค้า" required>
                    </div>

                    <div class="preview-img mb-2">
                        <?php 
                            $img_src = 'https://via.placeholder.com/150?text=No+Image';
                            if (!empty($edit['image'])) {
                                $img_src = $base_url . '/uploads/products/' . $edit['image'];
                            }
                        ?>
                        <img id="img_preview" src="<?= $img_src ?>" alt="Preview">
                    </div>
                    <label class="small text-muted mb-1">รูปภาพสินค้า</label>
                    <input type="file" name="image" class="form-control form-control-sm mb-3" accept="image/*" onchange="previewFile(this)">

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-muted mb-1">ราคา (บาท)</label>
                            <input type="number" step="0.01" class="form-control" name="price" placeholder="0.00" value="<?= $edit['price'] ?? '' ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-1">จำนวนสต็อก</label>
                            <input type="number" class="form-control" name="stock" placeholder="0" value="<?= $edit['stock'] ?? '' ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold rounded-3">
                        <i class="bi bi-save me-1"></i><?= $edit ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูลสินค้า' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- ตารางแสดงรายการสินค้า -->
        <div class="col-lg-8">
            <div class="card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">SKU / รูปภาพ</th>
                                <th>ชื่อสินค้า</th>
                                <th>ราคา</th>
                                <th>สต็อก</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($rows && $rows->num_rows > 0): ?>
                                <?php while($p = $rows->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="small text-muted mb-1"><?= htmlspecialchars($p['sku'] ?: '-') ?></div>
                                        <?php 
                                            $p_img = !empty($p['image']) ? $base_url . '/uploads/products/' . $p['image'] : 'https://via.placeholder.com/50';
                                        ?>
                                        <img src="<?= $p_img ?>" class="rounded shadow-sm" style="width:45px; height:45px; object-fit:cover;" onerror="this.src='https://via.placeholder.com/50'">
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                                    <td class="text-danger fw-bold">฿<?= number_format($p['price'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $p['stock'] > 5 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> px-2 py-1">
                                            <?= number_format($p['stock']) ?> ชิ้น
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm border rounded bg-white">
                                            <a class="btn btn-light text-success border-end" href="?inc=<?= $p['id'] ?>" title="+1 สต็อก"><i class="bi bi-plus-lg"></i></a>
                                            <a class="btn btn-light text-primary border-end" href="?edit=<?= $p['id'] ?>" title="แก้ไข"><i class="bi bi-pencil-square"></i></a>
                                            <a class="btn btn-light text-danger" href="?del=<?= $p['id'] ?>" onclick="return confirm('ยืนยันการลบสินค้าชิ้นนี้?')" title="ลบ"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">ยังไม่มีรายการสินค้าในระบบ</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function previewFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('img_preview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

let html5QrCode;
function startScanner() {
    const readerDiv = document.getElementById('reader');
    if (readerDiv.style.display === 'block') {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                readerDiv.style.display = 'none';
            });
        }
        return;
    }
    
    readerDiv.style.display = 'block';
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        (decodedText) => {
            document.getElementById('sku_input').value = decodedText;
            html5QrCode.stop().then(() => {
                readerDiv.style.display = 'none';
            });
        },
        (errorMessage) => {
            // สแกนยังไม่เจอ ไม่ต้องแสดง error
        }
    ).catch(err => {
        alert("ไม่สามารถเปิดกล้องได้: " + err);
        readerDiv.style.display = 'none';
    });
}
</script>
</body>
</html>