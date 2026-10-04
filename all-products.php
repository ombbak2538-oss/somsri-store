<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$keyword     = trim($_GET['keyword'] ?? '');
$category_id = intval($_GET['category_id'] ?? 0);
$page        = max(1, intval($_GET['page'] ?? 1));
$limit       = 20;
$offset      = ($page - 1) * $limit;

$sql = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1";
$params = [];
$types  = "";

if ($keyword !== '') {
    $sql .= " AND MATCH(p.name, p.description) AGAINST (? IN NATURAL LANGUAGE MODE)";
    $params[] = $keyword;
    $types   .= "s";
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types   .= "i";
}

$countSql = str_replace("SELECT p.*, c.name as cat_name", "SELECT COUNT(*) as total", $sql);
$countStmt = $conn->prepare($countSql);
if (!empty($params)) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalRows  = $countStmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

$sql .= " ORDER BY p.id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types   .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result();
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>สินค้าทั้งหมด | สมศรีซุปเปอร์สโตร์</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --ios-bg: #f2f2f7;
            --ios-card-bg: rgba(255, 255, 255, 0.8);
            --ios-blue: #007aff;
            --ios-red: #ff3b30;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--ios-bg);
            color: #1c1c1e;
            letter-spacing: -0.02em;
        }

        .product-card {
            border: none;
            border-radius: 24px;
            background: var(--ios-card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .product-card:hover {
            transform: scale(1.02) translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            background: #ffffff;
        }

        .img-wrap {
            padding: 15px;
            position: relative;
            overflow: hidden;
        }

        .product-img {
            height: 160px;
            object-fit: contain;
            transition: transform 0.6s ease;
        }

        .product-card:hover .product-img {
            transform: scale(1.1);
        }

        .product-name {
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.4;
            color: #1c1c1e;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.8em;
        }

        .price-text {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--ios-red);
        }

        .btn-ios-primary {
            background: var(--ios-blue);
            color: white;
            border-radius: 14px;
            font-weight: 500;
            padding: 8px 12px;
            border: none;
            transition: 0.2s;
        }

        .btn-ios-primary:active {
            transform: scale(0.95);
            opacity: 0.8;
        }

        .page-link {
            border: none;
            margin: 0 4px;
            border-radius: 10px !important;
            color: var(--ios-dark);
            background: transparent;
        }

        .page-item.active .page-link {
            background-color: var(--ios-blue);
        }

        .ios-title {
            font-weight: 700;
            letter-spacing: -0.5px;
        }
    </style>
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-end mb-4 px-2">
        <div>
            <h2 class="ios-title mb-0">🛒 สินค้าทั้งหมด</h2>
            <?php if($keyword): ?>
                <span class="badge bg-white text-dark shadow-sm mt-2 fw-normal">ผลการค้นหา: "<?= htmlspecialchars($keyword) ?>"</span>
            <?php endif; ?>
        </div>
        <div class="text-secondary small">พบ <?= number_format($totalRows) ?> รายการ</div>
    </div>

    <div class="row g-4" id="productGrid">
        <?php while($p = $products->fetch_assoc()): ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <div class="card product-card shadow-sm h-100 d-flex flex-column">
                <div class="img-wrap">
                    <a href="product-detail.php?id=<?= $p['id'] ?>">
                        <img src="uploads/products/<?= htmlspecialchars($p['image'] ?: 'no-image.png') ?>" 
                             class="product-img w-100" loading="lazy">
                    </a>
                </div>

                <div class="card-body p-3 pt-0 d-flex flex-column">
                    <a href="product-detail.php?id=<?= $p['id'] ?>" class="product-name mb-2">
                        <?= htmlspecialchars($p['name']) ?>
                    </a>

                    <div class="price-text mt-auto mb-3">
                        ฿<?= number_format($p['price'], 2) ?>
                    </div>

                    <button class="btn btn-ios-primary btn-sm w-100" onclick="addToCart(<?= $p['id'] ?>)">
                        <i class="bi bi-plus-lg"></i> ใส่รถเข็น
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

            <div class="text-center mt-4">
    <button id="loadMoreBtn" class="btn btn-ios-primary px-4">
        โหลดสินค้าเพิ่ม
    </button>
</div>

    <?php if($totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php if($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page-1 ?>"><i class="bi bi-chevron-left"></i></a>
            </li>
            <?php endif; ?>

            <?php 
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            for($i = $start; $i <= $end; $i++): 
            ?>
            <li class="page-item <?= $i==$page?'active':'' ?>">
                <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>

            <?php if($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?keyword=<?= urlencode($keyword) ?>&page=<?= $page+1 ?>"><i class="bi bi-chevron-right"></i></a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>

</div>

<script>
async function addToCart(id){
    const res = await fetch('add-to-cart.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'id='+id+'&qty=1'
    });
    location.reload();
}
</script>

<script>

let page = <?= $page ?>;
const totalPages = <?= $totalPages ?>;

document.getElementById("loadMoreBtn")?.addEventListener("click", async () => {

    page++;

    if(page > totalPages){
        document.getElementById("loadMoreBtn").style.display="none";
        return;
    }

    const params = new URLSearchParams(window.location.search);
    params.set("page",page);

    const res = await fetch("load-more-products.php?"+params.toString());
    const html = await res.text();

    document.getElementById("productGrid")
        .insertAdjacentHTML("beforeend", html);

});

</script>

</body>
</html>