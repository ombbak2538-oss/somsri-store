<?php
session_start();
$pageTitle = "แก้ไขสินค้า & จัดการราคา";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require_once __DIR__ . "/../../config/db.php";

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: products.php");
    exit;
}

$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
if (!$product) {
    header("Location: products.php");
    exit;
}

$current_branch_stocks = [];
$res_bs = $conn->query("SELECT branch_id, qty FROM branch_stocks WHERE product_id=$id");
while($row = $res_bs->fetch_assoc()) {
    $current_branch_stocks[$row['branch_id']] = $row['qty'];
}

$cats = $conn->query("SELECT * FROM categories ORDER BY name");
$branches = $conn->query("SELECT id, name FROM branches ORDER BY id ASC");
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = trim($_POST['barcode'] ?? '');
    $name    = trim($_POST['name'] ?? '');
    $cost_price      = floatval($_POST['cost_price'] ?? 0);
    $wholesale_price = floatval($_POST['wholesale_price'] ?? 0);
    $retail_price    = floatval($_POST['retail_price'] ?? 0);
    $catId   = (int)($_POST['category_id'] ?? 0);
    $branch_stocks = $_POST['branch_stocks'] ?? [];
    $total_stock   = array_sum($branch_stocks);
    $imgName = $product['image'];

    $chk = $conn->prepare("SELECT id FROM products WHERE barcode=? AND id<>? LIMIT 1");
    $chk->bind_param("si", $barcode, $id);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $error = "❌ รหัสสินค้านี้ถูกใช้โดยสินค้าชิ้นอื่นแล้ว";
    }

    if (!$error && $name !== '' && $retail_price > 0) {
        $conn->begin_transaction();
        try {
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $imgName = time() . "_" . random_int(1000,9999) . "." . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . "/../uploads/products/" . $imgName);
            }

            $stmt = $conn->prepare("UPDATE products SET 
                barcode=?, name=?, cost_price=?, wholesale_price=?, price=?, stock=?, image=?, category_id=? 
                WHERE id=?");
            $stmt->bind_param("ssdddisii", $barcode, $name, $cost_price, $wholesale_price, $retail_price, $total_stock, $imgName, $catId, $id);
            $stmt->execute();

            $conn->query("DELETE FROM branch_stocks WHERE product_id=$id");
            $stmtStock = $conn->prepare("INSERT INTO branch_stocks (branch_id, product_id, qty) VALUES (?, ?, ?)");
            foreach ($branch_stocks as $b_id => $qty) {
                $qty = (int)$qty;
                if ($qty >= 0) {
                    $stmtStock->bind_param("iii", $b_id, $id, $qty);
                    $stmtStock->execute();
                }
            }

            $conn->commit();
            echo "<script>window.location.href='products.php?updated=1';</script>";
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<main class="content">
    <div class="topbar mb-3 d-flex align-items-center gap-2">
        <strong>✏️ แก้ไขสินค้า: <?= htmlspecialchars($product['name']) ?></strong>
        <a href="products.php" class="ms-auto btn btn-outline-secondary">กลับหน้าจัดการ</a>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger shadow-sm"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="card-soft p-4 shadow-sm mb-3">
                    <h5 class="mb-4 border-bottom pb-2">📦 รายละเอียดสินค้า</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">บาร์โค้ด</label>
                            <input type="text" name="barcode" value="<?= htmlspecialchars($product['barcode']) ?>" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">หมวดหมู่</label>
                            <select name="category_id" class="form-select" required>
                                <?php $cats->data_seek(0); while($c = $cats->fetch_assoc()): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">ชื่อสินค้า</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-danger">ราคาทุน</label>
                            <input type="number" step="0.01" name="cost_price" id="cost_price" value="<?= $product['cost_price'] ?>" class="form-control border-danger" oninput="autoCalculate()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-primary">ขายส่ง (+15%)</label>
                            <input type="number" step="0.01" name="wholesale_price" id="wholesale_price" value="<?= $product['wholesale_price'] ?>" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-success">ขายปลีก (+30%)</label>
                            <input type="number" step="0.01" name="retail_price" id="retail_price" value="<?= $product['price'] ?>" class="form-control border-success" required>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="form-label fw-bold">รูปภาพสินค้า</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div id="imagePreviewContainer" style="width:100px; height:100px; border:1px solid #ddd; border-radius:8px; overflow:hidden;">
                                    <img id="imagePreview" src="../uploads/products/<?= htmlspecialchars($product['image'] ?: 'no-image.png') ?>" style="width:100%; height:100%; object-fit:cover;">
                                </div>
                                <input type="file" name="image" class="form-control" onchange="previewImage(this)">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card-soft p-4 shadow-sm h-100 d-flex flex-column">
                    <h5 class="mb-3 text-secondary border-bottom pb-2">🏢 แก้ไขสต็อกสาขา</h5>
                    <div class="table-responsive flex-grow-1">
                        <table class="table table-sm align-middle">
                            <tbody>
                                <?php $branches->data_seek(0); while($b = $branches->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['name']) ?></td>
                                    <td>
                                        <input type="number" name="branch_stocks[<?= $b['id'] ?>]" 
                                               value="<?= $current_branch_stocks[$b['id']] ?? 0 ?>" 
                                               class="form-control form-control-sm text-end">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg w-100 shadow mt-3">💾 บันทึกการแก้ไข</button>
                </div>
            </div>
        </div>
    </form>
</main>

<script>
function autoCalculate() {
    const cost = parseFloat(document.getElementById('cost_price').value) || 0;
    if (cost > 0) {
        document.getElementById('wholesale_price').value = (cost * 1.15).toFixed(2);
        document.getElementById('retail_price').value = (cost * 1.30).toFixed(2);
    }
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { document.getElementById('imagePreview').src = e.target.result; }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require __DIR__ . "/partials/footer.php"; ?>