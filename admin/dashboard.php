<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../../config/db.php";

if (!function_exists('timeAgo')) {
    function timeAgo($datetime){
        if(empty($datetime)) return 'เพิ่งเข้าใช้งาน';
        $ts = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60) return 'เมื่อสักครู่';
        if ($diff < 3600) return floor($diff/60) . ' นาทีที่แล้ว';
        if ($diff < 86400) return floor($diff/3600) . ' ชั่วโมงที่แล้ว';
        return floor($diff/86400) . ' วันที่แล้ว';
    }
}

$userId = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT full_name, username, emp_code, avatar, last_login FROM admins WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

$displayName = $me['full_name'] ?: $me['username'];

$avatarImg = (!empty($me['avatar']) && file_exists(__DIR__ . "/../uploads/avatars/" . $me['avatar'])) 
    ? "../uploads/avatars/" . $me['avatar'] 
    : "https://ui-avatars.com/api/?background=007AFF&color=fff&bold=true&name=" . urlencode($displayName);

$today = date('Y-m-d');
$todayStat = $conn->query("SELECT SUM(total) as total FROM sales WHERE DATE(created_at) = '$today'")->fetch_assoc();
$countProducts = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'];
$lowStock = $conn->query("SELECT name, stock FROM products WHERE stock <= alert_stock LIMIT 5");

$pageTitle = "Dashboard Overview";
require __DIR__ . "/partials/header.php";
require __DIR__ . "/partials/sidebar.php";
?>

<style>
    :root {
        --ios-bg: #F2F2F7;
        --ios-blue: #007AFF;
        --ios-card: rgba(255, 255, 255, 0.8);
    }
    .content { 
        background-color: var(--ios-bg); 
        padding: 2rem; 
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "Prompt", sans-serif;
    }
    .ios-card {
        background: var(--ios-card);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: 22px;
        border: 1px solid rgba(255,255,255,0.4);
        padding: 24px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.03);
    }
    .avatar-squircle {
        width: 100px; height: 100px;
        object-fit: cover;
        border-radius: 30px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border: 4px solid #fff;
    }
    .stat-badge {
        font-size: 2.5rem;
        font-weight: 800;
        letter-spacing: -1px;
    }
    .btn-edit {
        background: #fff;
        border: 1px solid #E5E5EA;
        border-radius: 12px;
        padding: 8px 20px;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-edit:hover { background: #F2F2F7; }
</style>

<main class="content">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h5 class="text-muted fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase;">Overview</h5>
            <h1 class="fw-bold mb-0" style="letter-spacing: -1px;">Dashboard</h1>
        </div>
        <div class="ios-card py-2 px-4 shadow-sm" style="border-radius: 14px;">
            <i class="bi bi-calendar3 me-2 text-primary"></i> <strong><?= date('d M Y') ?></strong>
        </div>
    </div>

    <div class="ios-card mb-4 overflow-hidden position-relative">
        <div class="row align-items-center">
            <div class="col-auto">
                <img src="<?= $avatarImg ?>" class="avatar-squircle">
            </div>
            <div class="col ms-3">
                <h2 class="fw-bold mb-1">สวัสดีครับ, <?= htmlspecialchars($displayName) ?> 👋</h2>
                <div class="d-flex gap-4 text-muted">
                    <span><i class="bi bi-person-badge-fill me-1"></i> ID: <?= $me['emp_code'] ?: 'N/A' ?></span>
                    <span><i class="bi bi-clock-fill me-1"></i> <?= timeAgo($me['last_login']) ?></span>
                </div>
            </div>
            <div class="col-auto text-end">
                <a href="profile_edit.php" class="btn btn-edit text-primary">
                    <i class="bi bi-gear-wide-connected me-2"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="ios-card text-center py-4">
                <div class="text-muted small fw-bold mb-2">DAILY SALES</div>
                <div class="stat-badge text-success">฿<?= number_format($todayStat['total'] ?? 0, 0) ?></div>
                <div class="text-muted small">ยอดรวมวันนี้</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ios-card text-center py-4">
                <div class="text-muted small fw-bold mb-2">INVENTORY</div>
                <div class="stat-badge text-primary"><?= number_format($countProducts) ?></div>
                <div class="text-muted small">สินค้าทั้งหมดในคลัง</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ios-card text-center py-4">
                <div class="text-muted small fw-bold mb-2">LOW STOCK</div>
                <div class="stat-badge text-danger"><?= $lowStock->num_rows ?></div>
                <div class="text-muted small">รายการสินค้าใกล้หมด</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="ios-card h-100">
                <h5 class="fw-bold mb-4 d-flex align-items-center">
                    <span class="p-2 bg-primary-subtle rounded-3 me-2"><i class="bi bi-receipt text-primary"></i></span>
                    Recent Transactions
                </h5>
                <div class="table-responsive">
                    <table class="table table-borderless align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>BILL ID</th>
                                <th>TOTAL AMOUNT</th>
                                <th>TIME</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $sales = $conn->query("SELECT id, total, created_at FROM sales ORDER BY id DESC LIMIT 5");
                            while($s = $sales->fetch_assoc()): 
                            ?>
                            <tr style="border-bottom: 1px solid rgba(0,0,0,0.03);">
                                <td class="fw-bold text-dark">#<?= $s['id'] ?></td>
                                <td class="fw-bold text-primary">฿<?= number_format($s['total'], 2) ?></td>
                                <td class="text-muted small"><?= date('H:i', strtotime($s['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ios-card h-100">
                <h5 class="fw-bold mb-4 text-danger d-flex align-items-center">
                    <span class="p-2 bg-danger-subtle rounded-3 me-2"><i class="bi bi-bell-fill"></i></span>
                    Stock Alerts
                </h5>
                <?php if($lowStock->num_rows > 0): ?>
                    <?php while($p = $lowStock->fetch_assoc()): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded-4" style="background: rgba(255, 59, 48, 0.05);">
                        <span class="fw-medium text-dark"><?= $p['name'] ?></span>
                        <span class="badge bg-danger rounded-pill px-3">เหลือ <?= $p['stock'] ?></span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle-fill display-4 text-success opacity-25"></i>
                        <p class="text-muted mt-2">สต็อกปกติทุกรายการ</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require __DIR__ . "/partials/footer.php"; ?>