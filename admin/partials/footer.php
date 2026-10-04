<?php
/**
 * footer.php - ปิดแท็กเนื้อหาหลักและโหลดสคริปต์
 * ส่วนนี้จะทำงานร่วมกับระบบ Sidebar และ Topbar ที่มีคลาส .sidebar-show และ .main-content
 */
?>

    </main> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
/**
 * ระบบจัดการ Sidebar สำหรับสมศรีซุปเปอร์สโตร์
 */
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');

    // 1. ฟังก์ชันสลับการแสดงผล Sidebar
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });
    }

    // 2. คลิกพื้นที่ด้านนอกเพื่อปิด Sidebar (เฉพาะบนมือถือ)
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 992) {
            if (sidebar && sidebar.classList.contains('show') && !sidebar.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // 3. ตรวจสอบขนาดหน้าจออัตโนมัติ
    function checkResponsive() {
        if (window.innerWidth >= 992) {
            // หน้าจอใหญ่: ล้างคลาสที่ค้างจากการใช้งานบนมือถือ
            if (sidebar) sidebar.classList.remove('show');
        }
    }

    window.addEventListener('resize', checkResponsive);
    checkResponsive();
});

/**
 * ฟังก์ชันเสริมสำหรับ UI (เช่น ระบบแจ้งเตือน)
 */
const autoCloseAlerts = () => {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
};
autoCloseAlerts();
</script>

</body>
</html>