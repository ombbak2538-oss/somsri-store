<?php
$keyword = $keyword ?? '';
$cat = $cat ?? '';
?>
<style>
:root{ --main-red:#e53935; }
.site-header{
  position: sticky; top:0; z-index: 1000;
  background: linear-gradient(90deg, #e53935, #ff6f91);
  padding: 10px;
}
.site-header .brand{ color:#fff; font-weight:700; white-space:nowrap; }
.site-header .search input,
.site-header .search select{ border-radius: 999px; }
.site-header .cart-btn{ background:#fff; border-radius: 999px; }

.banner-wrap{ background:#fff; }
.banner-wrap .carousel-item img{
  width:100%;
  height:160px;          /* ปรับความสูงแบนเนอร์ */
  object-fit:cover;
  border-radius:12px;
}
@media (min-width:768px){
  .banner-wrap .carousel-item img{ height:240px; }
}
</style>

<!-- Top Header -->
<div class="site-header">
  <form class="d-flex gap-2 align-items-center search" method="get" action="index.php">
    <div class="brand">สมศรีซุปเปอร์สโตร์</div>

    <input name="q" value="<?= htmlspecialchars($keyword) ?>"
           class="form-control form-control-sm flex-fill"
           placeholder="ค้นหาสินค้า..."
           oninput="this.form.submit()">

    <?php if(isset($cats) && $cats): ?>
      <select name="cat" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">ทุกหมวด</option>
        <?php while($c = $cats->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>" <?= ($cat==$c['id'])?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    <?php endif; ?>

    <a href="cart.php" class="btn cart-btn btn-sm">🛒</a>
  </form>
</div>

<!-- Banner Slider -->
<div class="container banner-wrap mt-2">
  <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner rounded-3 shadow-sm">

      <div class="carousel-item active">
        <img src="assets/banners/banner1.jpg" alt="โปร 1">
      </div>
      <div class="carousel-item">
        <img src="assets/banners/banner2.jpg" alt="โปร 2">
      </div>
      <div class="carousel-item">
        <img src="assets/banners/banner3.jpg" alt="โปร 3">
      </div>

    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>
</div>
