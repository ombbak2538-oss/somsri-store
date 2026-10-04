<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

$branchRes = $conn->query("SELECT id, name FROM branches ORDER BY id ASC");
$branches = [];
while($b = $branchRes->fetch_assoc()) { $branches[] = $b; }

$sql = "SELECT p.id, p.name, p.barcode, p.alert_stock,
        GROUP_CONCAT(CONCAT(bs.branch_id, ':', bs.stock)) as stock_data
        FROM products p
        LEFT JOIN branch_stocks bs ON p.id = bs.product_id
        GROUP BY p.id ORDER BY p.name ASC";
$products = $conn->query($sql);

$pageTitle = "Inventory Report";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
?>

<style>
    :root { --ios-bg: #F2F2F7; }
    .content { background: var(--ios-bg); min-height: 100vh; padding: 2rem; }
    .ios-card {
        background: rgba(255,255,255,0.8); backdrop-filter: blur(20px);
        border-radius: 24px; border: 1px solid #fff; padding: 25px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.02);
    }
    .table thead th {
        background: transparent; border: none; color: #8E8E93;
        font-size: 11px; text-transform: uppercase; letter-spacing: 1px;
    }
    .stock-pill {
        padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 14px;
    }
    .stock-low { background: #FF3B3015; color: #FF3B30; }
    .stock-ok { background: #34C75915; color: #34C759; }
    .stock-empty { background: #E5E5EA; color: #8E8E93; }
</style>

<main class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="fw-bold mb-1" style="letter-spacing: -1px;">Inventory Report</h1>
            <p class="text-muted small mb-0">เปรียบเทียบสต็อกสินค้าคงเหลือทุกสาขาแบบ Real-time</p>
        </div>
        <button onclick="window.print()" class="btn btn-white shadow-sm rounded-pill px-4">
            <i class="bi bi-printer me-2"></i> Print PDF
        </button>
    </div>

    <div class="ios-card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 250px;">สินค้า</th>
                        <th class="text-center">สต็อกรวมกลาง</th>
                        <?php foreach($branches as $b): ?>
                            <th class="text-center"><?= htmlspecialchars($b['name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $products->fetch_assoc()): 
                        $s_map = [];
                        if($p['stock_data']) {
                            foreach(explode(',', $p['stock_data']) as $pair) {
                                list($bid, $val) = explode(':', $pair);
                                $s_map[$bid] = $val;
                            }
                        }
                    ?>
                    <tr style="border-bottom: 1px solid rgba(0,0,0,0.03);">
                        <td>
                            <div class="fw-bold text-dark"><?= htmlspecialchars($p['name']) ?></div>
                            <div class="small text-muted"><?= $p['barcode'] ?></div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold"><?= number_format($conn->query("SELECT stock FROM products WHERE id=".$p['id'])->fetch_assoc()['stock']) ?></span>
                        </td>
                        <?php foreach($branches as $b): 
                            $current_stock = $s_map[$b['id']] ?? 0;
                            $status_class = ($current_stock == 0) ? 'stock-empty' : (($current_stock <= $p['alert_stock']) ? 'stock-low' : 'stock-ok');
                        ?>
                            <td class="text-center">
                                <span class="stock-pill <?= $status_class ?>">
                                    <?= number_format($current_stock) ?>
                                </span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>