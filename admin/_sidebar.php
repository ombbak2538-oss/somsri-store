<?php
// ดึงชื่อไฟล์ปัจจุบันเพื่อไฮไลต์เมนูที่เปิดอยู่
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        --sidebar-width: 250px;
        --sidebar-bg: #1c1d22;
        --sidebar-active-bg: #2d232e;
        --sidebar-active-border: #e62e4d;
        --sidebar-text: #a0a5b1;
        --sidebar-active-text: #ffffff;
    }

    .sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        background-color: var(--sidebar-bg);
        color: var(--sidebar-text);
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        font-family: 'Prompt', sans-serif;
    }

    .sidebar-brand {
        padding: 24px 24px 16px;
        font-size: 1.25rem;
        font-weight: 700;
        color: #e62e4d;
        text-decoration: none;
        display: block;
        letter-spacing: 0.5px;
    }

    .sidebar-brand span {
        color: #e62e4d;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 10px 0 0 0;
    }

    .sidebar-menu li {
        margin-bottom: 2px;
    }

    .sidebar-menu .nav-link {
        display: flex;
        align-items: center;
        padding: 14px 24px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 400;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
    }

    .sidebar-menu .nav-link:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.03);
    }

    .sidebar-menu .nav-link.active {
        color: var(--sidebar-active-text);
        background-color: var(--sidebar-active-bg);
        border-left-color: var(--sidebar-active-border);
        font-weight: 500;
    }

    .sidebar-menu .nav-link i {
        font-size: 1.1rem;
        margin-right: 14px;
        width: 20px;
        text-align: center;
    }

    .sidebar-footer {
        padding: 24px;
    }

    .sidebar-footer .logout-link {
        display: flex;
        align-items: center;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 0.95rem;
        transition: color 0.2s ease;
    }

    .sidebar-footer .logout-link:hover {
        color: #ffffff;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        padding: 30px;
        transition: margin-left 0.3s ease;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            margin-left: calc(-1 * var(--sidebar-width));
            transition: margin-left 0.3s ease;
        }
        .sidebar.show {
            margin-left: 0;
        }
        .main-content {
            margin-left: 0;
            padding: 15px;
        }
    }
</style>

<aside class="sidebar" id="sidebar">
    <div>
        <!-- โลโก้ส่วนหัว -->
        <a href="dashboard.php" class="sidebar-brand">
            สมศรี ADMIN
        </a>

        <!-- รายการเมนูหลัก -->
        <ul class="sidebar-menu">
            <li>
                <a href="dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                    <i class="bi bi-grid"></i>
                    <span>สรุปภาพรวม</span>
                </a>
            </li>
            <li>
                <a href="products.php" class="nav-link <?= ($current_page == 'products.php') ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i>
                    <span>คลังสินค้า</span>
                </a>
            </li>
            <li>
                <a href="orders.php" class="nav-link <?= (in_array($current_page, ['orders.php', 'order-detail.php', 'order_view.php'])) ? 'active' : '' ?>">
                    <i class="bi bi-cart"></i>
                    <span>รายการสั่งซื้อ</span>
                </a>
            </li>
            <li>
                <a href="categories.php" class="nav-link <?= ($current_page == 'categories.php') ? 'active' : '' ?>">
                    <i class="bi bi-tag"></i>
                    <span>หมวดหมู่</span>
                </a>
            </li>
            <li>
                <a href="members.php" class="nav-link <?= (in_array($current_page, ['members.php', 'members_add.php', 'members_edit.php'])) ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>จัดการสมาชิก</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- ปุ่มออกจากระบบด้านล่าง -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">
            <span>ออกจากระบบ</span>
        </a>
    </div>
</aside>