<div id="trackDrawer" class="drawer">
    <div class="drawer-header">
        <strong class="text-hp-blue">
            <i class="bi bi-box-seam me-2"></i>ติดตามสถานะคำสั่งซื้อ
        </strong>
        <button class="btn-close" onclick="closeDrawer('trackDrawer')"></button>
    </div>

    <div class="drawer-body">
        <div class="p-3 bg-light rounded-4 mb-4 shadow-sm border">
            <label class="small fw-bold mb-2 text-muted">กรอกเลขที่ใบสั่งซื้อ หรือ เบอร์โทรศัพท์</label>
            <div class="input-group">
                <input type="text" id="trackInput" class="form-control border-hp-blue" 
                       placeholder="เช่น ORD2026xxxx" onkeyup="if(event.key==='Enter') quickTrack()">
                <button class="btn btn-primary" style="background:var(--hp-blue)" onclick="quickTrack()">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>

        <div id="trackQuickResult">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-search fs-1 opacity-25"></i>
                <p class="mt-2">ระบุข้อมูลเพื่อตรวจสอบสถานะ</p>
            </div>
        </div>
    </div>

    <div class="drawer-footer bg-white">
        <button class="btn btn-outline-secondary w-100 rounded-3" onclick="closeDrawer('trackDrawer')">ปิดหน้าต่าง</button>
    </div>
</div>

<script>
async function quickTrack() {
    const val = document.getElementById('trackInput').value.trim();
    const resDiv = document.getElementById('trackQuickResult');
    
    if(!val) {
        resDiv.innerHTML = '<div class="alert alert-warning py-2 small">กรุณากรอกข้อมูลที่ต้องการค้นหา</div>';
        return;
    }

    resDiv.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-hp-orange" role="status"></div>
            <p class="mt-2 small text-muted">กำลังค้นหาข้อมูล...</p>
        </div>`;
    
    try {
        const r = await fetch('track-api.php?code=' + encodeURIComponent(val));
        const d = await r.json();

        if(!d.success || d.items.length === 0) {
            resDiv.innerHTML = `
                <div class="text-center py-5 border rounded-4 bg-white">
                    <i class="bi bi-exclamation-circle text-danger fs-2"></i>
                    <p class="mt-2 mb-0 fw-bold">ไม่พบข้อมูล</p>
                    <small class="text-muted">ตรวจสอบเลขที่สั่งซื้ออีกครั้ง</small>
                </div>`;
            return;
        }

        resDiv.innerHTML = d.items.map(it => {
            let statusColor = 'bg-secondary';
            if(it.shipping_status === 'จัดส่งแล้ว') statusColor = 'bg-success';
            if(it.shipping_status === 'กำลังเตรียมสินค้า') statusColor = 'bg-warning text-dark';

            return `
                <div class="card mb-3 border-0 shadow-sm rounded-4 overflow-hidden border-start border-4 border-hp-blue">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <small class="text-muted d-block">เลขที่ใบสั่งซื้อ</small>
                                <span class="fw-bold text-hp-blue">${it.order_code}</span>
                            </div>
                            <span class="badge ${statusColor} rounded-pill">${it.shipping_status}</span>
                        </div>
                        <div class="small mb-3">
                            <i class="bi bi-truck me-1"></i> เลขพัสดุ: 
                            <span class="text-hp-orange fw-bold">${it.tracking_no || 'รอดำเนินการ'}</span>
                        </div>
                        <a href="track.php?order_no=${it.order_code}" class="btn btn-sm btn-outline-primary w-100 rounded-3">
                            ดูรายละเอียดและรายการสินค้า
                        </a>
                    </div>
                </div>
            `;
        }).join('');

    } catch(e) { 
        resDiv.innerHTML = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>'; 
    }
}
</script>

<style>
#trackInput:focus {
    box-shadow: 0 0 0 0.25rem rgba(0, 56, 118, 0.1);
    border-color: var(--hp-blue);
}
.btn-outline-primary {
    color: var(--hp-blue);
    border-color: var(--hp-blue);
}
.btn-outline-primary:hover {
    background-color: var(--hp-blue);
    border-color: var(--hp-blue);
}
</style>