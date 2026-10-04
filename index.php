<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$keyword     = $_GET['keyword'] ?? '';
$category_id = $_GET['category_id'] ?? '';
$min_price   = $_GET['min_price'] ?? '';
$max_price   = $_GET['max_price'] ?? '';
$in_stock    = isset($_GET['in_stock']) ? 1 : 0;
$role        = $_SESSION['role'] ?? 'GUEST';

$catsRes = $conn->query("SELECT c.id, c.name, COUNT(p.id) AS total FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id, c.name ORDER BY c.name");
$cats = [];
if ($catsRes) while ($r = $catsRes->fetch_assoc()) $cats[] = $r;
$totalAll = (int)$conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];

$sql = "SELECT p.* FROM products p WHERE 1 ";
if ($keyword !== '') {
    $kw = $conn->real_escape_string($keyword);
    $sql .= " AND p.name LIKE '%$kw%'";
}
if ($category_id !== '') $sql .= " AND p.category_id = " . intval($category_id);
if ($min_price   !== '') $sql .= " AND p.price >= " . floatval($min_price);
if ($max_price   !== '') $sql .= " AND p.price <= " . floatval($max_price);
if ($in_stock)           $sql .= " AND p.stock > 0";
$sql .= " ORDER BY p.id DESC LIMIT 12";
$products = $conn->query($sql);

$newProducts = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 12");

function renderSlider($id, $title, $res) {
    if (!$res || $res->num_rows == 0) return;
    $items = [];
    while ($r = $res->fetch_assoc()) $items[] = $r;
    $chunks = array_chunk($items, 6);
?>
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-end mb-4 px-2">
            <div>
                <span class="text-uppercase small fw-bold text-secondary tracking-widest" style="letter-spacing: 2px;">Featured Items</span>
                <h2 class="fw-extrabold mb-0 ios-gradient-title"><?= $title ?></h2>
            </div>
            <div class="d-flex gap-2">
                <button class="ios-nav-btn" data-bs-target="#<?= $id ?>" data-bs-slide="prev"><i class="bi bi-chevron-left"></i></button>
                <button class="ios-nav-btn" data-bs-target="#<?= $id ?>" data-bs-slide="next"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

        <div id="<?= $id ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($chunks as $i => $group): ?>
                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                        <div class="row g-4">
                            <?php foreach ($group as $p): 
                                $price = (float)$p['price'];
                                $stock = (int)$p['stock'];
                                $discount = (int)($p['discount_percent'] ?? 0);
                                $finalPrice = $discount > 0 ? $price * (100 - $discount) / 100 : $price;
                            ?>
                                <div class="col-6 col-md-2">
                                    <div class="ios-product-card shadow-sm <?= $stock <= 0 ? 'out-of-stock' : '' ?>">
                                        <div class="img-container">
                                            <a href="product-detail.php?id=<?= $p['id'] ?>">
                                                <img src="uploads/products/<?= htmlspecialchars($p['image'] ?: 'no-image.png') ?>" class="img-fluid">
                                            </a>
                                            <?php if($discount > 0): ?>
                                                <div class="ios-badge-discount">-<?= $discount ?>%</div>
                                            <?php endif; ?>
                                            
                                            <?php if($stock > 0): ?>
                                                <button class="ios-add-btn add-cart-btn" data-id="<?= $p['id'] ?>">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            <?php else: ?>
                                                <div class="ios-badge-out">สินค้าหมด</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="p-3">
                                            <div class="product-name text-truncate"><?= htmlspecialchars($p['name']) ?></div>
                                            <div class="price-container mt-1">
                                                <span class="price-final">฿<?= number_format($finalPrice, 2) ?></span>
                                                <?php if($discount > 0): ?>
                                                    <span class="price-original">฿<?= number_format($price, 0) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php } ?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Somsri Superstore | iOS 26 Edition</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ios-bg: #f2f2f7;
            --ios-blue: #007aff;
            --ios-card: rgba(255, 255, 255, 0.85);
            --ios-dark: #1c1c1e;
            --ios-red: #ff3b30;
        }

        body { 
            font-family: 'Prompt', -apple-system, sans-serif; 
            background: var(--ios-bg); 
            color: var(--ios-dark);
            overflow-x: hidden;
        }

        .ios-gradient-title {
            background: linear-gradient(135deg, #000 30%, #444 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        .ios-product-card {
            background: var(--ios-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 28px;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            height: 100%;
        }

        .ios-product-card:hover:not(.out-of-stock) {
            transform: scale(1.03) translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08) !important;
            background: #fff;
        }

        .ios-product-card.out-of-stock img {
            filter: grayscale(1);
            opacity: 0.6;
        }
        
        .ios-badge-out {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 4px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
        }

        .img-container {
            position: relative;
            padding: 12px;
            overflow: hidden;
        }

        .img-container img {
            border-radius: 20px;
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .ios-product-card:hover:not(.out-of-stock) .img-container img {
            transform: scale(1.1);
        }

        .ios-add-btn {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--ios-dark);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .ios-product-card:hover .ios-add-btn {
            opacity: 1;
            transform: translateY(0);
        }

        .ios-badge-discount {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--ios-red);
            color: #fff;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            z-index: 2;
        }

        .product-name {
            font-size: 0.95rem;
            font-weight: 500;
            color: #3a3a3c;
        }

        .price-final {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ios-dark);
        }

        .price-original {
            font-size: 0.8rem;
            text-decoration: line-through;
            color: #8e8e93;
            margin-left: 5px;
        }

        .ios-nav-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: 0.2s;
        }

        .custom-toast {
            position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%) translateY(100px);
            background: rgba(0,0,0,0.75); backdrop-filter: blur(15px); color: #fff;
            padding: 14px 28px; border-radius: 30px; z-index: 10000; transition: 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .custom-toast.show { transform: translateX(-50%) translateY(0); }

        .ios-sidebar-card { background: #fff; border-radius: 24px; border: none; overflow: hidden; }

        .admin-fab {
            position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px;
            backdrop-filter: blur(20px); background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3); color: #000; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 22px;
            text-decoration: none; box-shadow: 0 10px 30px rgba(0,0,0,0.25); z-index: 9999;
        }
    </style>
</head>
<body>

<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/th_TH/1KV9ewcnNw/?mibextid=wwXIfr"></script>

<?php include __DIR__ . "/partials/header.php"; ?>

<?php if (!$keyword && !$category_id): 
    renderSlider('newProductCarousel', 'มาใหม่วันนี้', $newProducts); 
endif; ?>

<div class="container py-4">
    <div class="row g-4">
        <aside class="col-lg-3 d-none d-lg-block">
            
            <div class="card ios-sidebar-card shadow-sm p-3 mb-4">
                <h6 class="fw-bold mb-3 px-1"><i class="bi bi-facebook text-primary me-2"></i>ติดตามเรา ร้านสมศรีกันทรลักษ์ขายส่ง</h6>
                <div class="fb-page" 
                    data-href="https://www.facebook.com/share/1KV9ewcnNw/?mibextid=wwXIfr" 
                    data-tabs="timeline" 
                    data-width="" 
                    data-height="70" 
                    data-small-header="false" 
                    data-adapt-container-width="true" 
                    data-hide-cover="false" 
                    data-show-facepile="true">
                    <blockquote cite="https://www.facebook.com/share/1KV9ewcnNw/?mibextid=wwXIfr" class="fb-xfbml-parse-ignore">
                        <a href="https://www.facebook.com/share/1KV9ewcnNw/?mibextid=wwXIfr">Facebook</a>
                    </blockquote>
                </div>
            </div>

            <div class="card ios-sidebar-card shadow-sm p-4 mb-4">
                <h6 class="fw-bold mb-4">หมวดหมู่สินค้า</h6>
                <div class="list-group list-group-flush">
                    <a href="index.php" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center">
                        ทั้งหมด <span class="badge rounded-pill bg-light text-dark border"><?= $totalAll ?></span>
                    </a>
                    <?php foreach ($cats as $c): ?>
                        <a href="index.php?category_id=<?= $c['id'] ?>" class="list-group-item list-group-item-action border-0 px-0 d-flex justify-content-between align-items-center <?= $category_id == $c['id'] ? 'fw-bold text-primary' : '' ?>">
                            <?= htmlspecialchars($c['name']) ?> 
                            <span class="badge rounded-pill bg-light text-dark border"><?= $c['total'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card ios-sidebar-card shadow-sm p-4">
                <h6 class="fw-bold mb-3">ระบุช่วงราคา</h6>
                <form action="index.php" method="GET">
                    <div class="mb-3">
                        <input type="number" name="min_price" class="form-control rounded-4 bg-light border-0" placeholder="ต่ำสุด" value="<?= $min_price ?>">
                    </div>
                    <div class="mb-4">
                        <input type="number" name="max_price" class="form-control rounded-4 bg-light border-0" placeholder="สูงสุด" value="<?= $max_price ?>">
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-pill py-2">ใช้ตัวกรอง</button>
                </form>
            </div>
        </aside>

        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">
                    <?= $keyword ? 'ผลการค้นหา: ' . htmlspecialchars($keyword) : 'รายการทั้งหมด' ?>
                </h4>
            </div>
            
            <div class="row g-4" id="productGrid">
                <?php while ($p = $products->fetch_assoc()): 
                    $price = (float)$p['price']; 
                    $stock = (int)$p['stock'];
                    $discount = (int)($p['discount_percent'] ?? 0);
                    $finalPrice = $discount > 0 ? $price * (100 - $discount) / 100 : $price;
                ?>
                    <div class="col-6 col-md-4">
                        <div class="ios-product-card shadow-sm h-100 d-flex flex-column <?= $stock <= 0 ? 'out-of-stock' : '' ?>">
                            <div class="img-container">
                                <a href="product-detail.php?id=<?= $p['id'] ?>">
                                    <img src="uploads/products/<?= htmlspecialchars($p['image'] ?: 'no-image.png') ?>" class="img-fluid">
                                </a>
                                <?php if($discount > 0): ?>
                                    <div class="ios-badge-discount">-<?= $discount ?>%</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
                                <div class="product-name text-truncate mb-2"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="price-container">
                                        <div class="price-final">฿<?= number_format($finalPrice, 2) ?></div>
                                        <?php if($discount > 0): ?>
                                            <span class="price-original">฿<?= number_format($price, 0) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if($stock > 0): ?>
                                        <button class="btn btn-outline-dark btn-sm rounded-pill px-3 add-cart-btn" data-id="<?= $p['id'] ?>">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-secondary-subtle text-secondary border px-3 py-2" style="font-size: 10px;">หมดสต็อก</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="text-center mt-4">
    <button id="loadMoreBtn" class="btn btn-dark rounded-pill px-4 py-2">
        โหลดเพิ่มเติม
    </button>
</div>
        </div>
    </div>
</div>

<div id="cartToast" class="custom-toast">🛒 เพิ่มไปยังตะกร้าสำเร็จ</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.add-cart-btn')) {
            const btn = e.target.closest('.add-cart-btn');
            const pid = btn.dataset.id;
            
            btn.style.transform = 'scale(0.8)';
            setTimeout(() => btn.style.transform = '', 200);

            const res = await fetch('update-cart.php?id=' + pid + '&act=add_ajax');
            if (res.ok) {
                const toast = document.getElementById('cartToast');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2500);
                
                const cartBadge = document.getElementById('cart-badge-count');
                if (cartBadge) {
                    let count = parseInt(cartBadge.innerText) || 0;
                    cartBadge.innerText = count + 1;
                }
            }
        }
    });
</script>

<?php if ($role === 'ADMIN'): ?>
    <a href="admin/index.php" class="admin-fab">
        <i class="bi bi-gear-fill"></i>
    </a>
<?php endif; ?>

<script>

let page = 1;
const limit = 12;

document.getElementById("loadMoreBtn")?.addEventListener("click", async () => {

    page++;

    const params = new URLSearchParams(window.location.search);
    params.set("page", page);

    const res = await fetch("load-more-products.php?" + params.toString());

    const html = await res.text();

    if(html.trim() === ""){
        document.getElementById("loadMoreBtn").style.display="none";
        return;
    }

    document.getElementById("productGrid")
        .insertAdjacentHTML("beforeend", html);

});

</script>

</body>
</html>