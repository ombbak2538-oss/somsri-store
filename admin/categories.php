<?php
$pageTitle = "จัดการหมวดหมู่สินค้า";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
require_once __DIR__ . "/../../config/db.php";

if (isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: categories.php?success=add");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: categories.php?success=delete");
    exit;
}

$cats = $conn->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.id DESC
");
?>

<style>
    .category-card { border: none; border-radius: 15px; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
    .category-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
    .table thead th { background: #f8f9fa; text-transform: uppercase; font-size: 0.75rem; color: #64748b; letter-spacing: 0.5px; border: none; }
    .cat-name { font-weight: 700; color: #1e293b; font-size: 1rem; }
    .product-counter { font-size: 0.75rem; background: #f1f5f9; padding: 2px 8px; border-radius: 50px; color: #475569; }
    .input-modern { border-radius: 12px; border: 2px solid #f1f5f9; padding: 12px; transition: 0.2s; }
    .input-modern:focus { border-color: #dc3545; box-shadow: none; background: #fff; }
</style>

<main class="content">
    <div class="topbar mb-4 d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold m-0 text-dark">🗂 จัดการหมวดหมู่</h4>
            <p class="text-muted small m-0">จัดการกลุ่มสินค้าเพื่อให้แยกประเภทได้ง่ายขึ้น</p>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark border shadow-sm rounded-pill px-3 py-2">
                ทั้งหมด <?= $cats->num_rows ?> รายการ
            </span>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?= $_GET['success'] == 'add' ? 'เพิ่มหมวดหมู่เรียบร้อยแล้ว' : 'ลบหมวดหมู่สำเร็จ' ?>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="category-card p-4 sticky-top" style="top: 20px; z-index: 10;">
                <h6 class="mb-4 fw-bold text-uppercase" style="letter-spacing: 1px;">
                    <i class="bi bi-plus-square-fill text-danger me-2"></i>เพิ่มหมวดใหม่
                </h6>
                <form method="post">
                    <div class="mb-4">
                        <label class="small fw-bold text-muted mb-2">ชื่อหมวดหมู่</label>
                        <input type="text" name="name" class="form-control input-modern" 
                               placeholder="เช่น เครื่องปรุง, ขนมขบเคี้ยว" required autofocus>
                    </div>
                    <button name="add" class="btn btn-danger w-100 py-3 fw-bold rounded-4 shadow">
                        บันทึกข้อมูล
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="category-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="80" class="ps-4 text-center">ID</th>
                                <th>ชื่อหมวดหมู่ / จำนวนสินค้า</th>
                                <th width="150" class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($cats && $cats->num_rows > 0): ?>
                                <?php while($c = $cats->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 text-center cat-id">#<?= $c['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="cat-name"><?= htmlspecialchars($c['name']) ?></span>
                                                <span class="product-counter border">
                                                    📦 <?= number_format($c['product_count']) ?> ชิ้น
                                                </span>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                รหัสหมวดหมู่อ้างอิง: CAT-<?= str_pad($c['id'], 4, '0', STR_PAD_LEFT) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="?delete=<?= $c['id'] ?>" 
                                               class="btn btn-sm btn-light text-danger rounded-3 p-2 px-3 border shadow-sm"
                                               onclick="return confirm('❗ คำเตือน: หากลบหมวดหมู่ สินค้าในหมวดนี้จะกลายเป็น \'ไม่มีหมวดหมู่\' ยืนยันลบหรือไม่?')">
                                                <i class="bi bi-trash3-fill"></i> ลบ
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <i class="bi bi-folder-x display-4 d-block mb-3 opacity-25"></i>
                                        <p class="text-muted m-0">ยังไม่มีข้อมูลหมวดหมู่ในระบบ</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>