<?php
$pageTitle = "Orders จากเว็บ";
require __DIR__ . "/../partials/header.php";
require __DIR__ . "/../partials/sidebar.php";
require_once __DIR__ . "/../../../config/db.php";

$sql = "
  SELECT id, order_no, customer_name, customer_phone, total_amount, status, created_at
  FROM orders
  ORDER BY id DESC
";
$result = $conn->query($sql);
?>

<style>
    .order-row:hover { background-color: rgba(220, 53, 69, 0.02); }
    .badge-status { width: 85px; padding: 6px; font-weight: 600; border-radius: 6px; }
    .order-no { font-family: 'Courier New', Courier, monospace; font-weight: bold; color: #2c3e50; }
    /* ปรับแต่งปุ่มให้ดู Soft ตามสไตล์การ์ด */
    .btn-action { border-radius: 8px; transition: 0.2s; }
    .btn-action:hover { transform: translateY(-1px); }
</style>

<main class="content">
    <div class="topbar mb-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="hamburger" onclick="toggleSidebar()">☰</button>
            <strong class="fs-5">📦 Orders จากเว็บ</strong>
        </div>
        <div class="input-group w-auto">
            <input type="text" id="orderSearch" class="form-control form-control-sm border-0 shadow-sm" placeholder="ค้นหาเลขออเดอร์/ชื่อลูกค้า..." style="min-width: 250px;">
        </div>
    </div>

    <div class="card-soft border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-secondary small text-uppercase">
                        <th class="ps-3" width="60">ID</th>
                        <th width="140">เลขออเดอร์</th>
                        <th>ข้อมูลลูกค้า</th>
                        <th class="text-end" width="130">ยอดรวม</th>
                        <th class="text-center" width="120">สถานะ</th>
                        <th width="160">วันที่ทำรายการ</th>
                        <th class="text-center" width="220">การจัดการ</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody">
                    <?php if($result && $result->num_rows): while($r=$result->fetch_assoc()): ?>
                    <tr class="order-row">
                        <td class="ps-3 text-muted">#<?= $r['id'] ?></td>
                        <td><span class="order-no"><?= htmlspecialchars($r['order_no']) ?></span></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($r['customer_name'] ?: 'ไม่ระบุชื่อ') ?></div>
                            <div class="small text-muted"><i class="bi bi-telephone"></i> <?= htmlspecialchars($r['customer_phone'] ?: '-') ?></div>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-dark">฿<?= number_format((float)$r['total_amount'], 2) ?></span>
                        </td>
                        <td class="text-center">
                            <?php 
                                $statusClass = [
                                    'DONE'    => 'bg-success-subtle text-success border-success',
                                    'PENDING' => 'bg-warning-subtle text-warning-emphasis border-warning',
                                    'CANCEL'  => 'bg-danger-subtle text-danger border-danger'
                                ];
                                $currentStatus = $r['status'] ?: 'PENDING';
                                $class = $statusClass[$currentStatus] ?? 'bg-secondary-subtle text-secondary';
                            ?>
                            <span class="badge badge-status border <?= $class ?>"><?= $currentStatus ?></span>
                        </td>
                        <td class="small">
                            <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?> น.
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="view.php?id=<?= $r['id'] ?>" class="btn btn-light btn-sm btn-action border shadow-sm">
                                    <i class="bi bi-search"></i> ดูรายละเอียด
                                </a>

                                <?php if($r['status'] !== 'DONE'): ?>
                                    <a href="../orders/convert_to_pos.php?id=<?= $r['id'] ?>"
                                       class="btn btn-success btn-sm btn-action shadow-sm"
                                       onclick="return confirm('ยืนยันแปลงออเดอร์นี้เป็นบิล POS เพื่อตัดสต็อกสินค้า?')">
                                        <i class="bi bi-receipt-cutoff"></i> แปลงบิล
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted d-flex align-items-center px-3 border">
                                        <i class="bi bi-check-all me-1"></i> เรียบร้อย
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-4 d-block mb-2 opacity-25"></i>
                            ไม่พบข้อมูลรายการสั่งซื้อ
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    // ระบบค้นหาข้อมูลในตารางแบบ Real-time
    document.getElementById('orderSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#orderTableBody tr');
        
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>

<?php require __DIR__ . "/../partials/footer.php"; ?>