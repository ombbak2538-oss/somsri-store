<?php
// ดึงจำนวนสินค้าในแต่ละหมวด + เรียงมากไปน้อย
$sqlCounts = "
  SELECT c.id, c.name, c.created_at, COUNT(p.id) AS total
  FROM categories c
  LEFT JOIN products p ON p.category_id = c.id
  GROUP BY c.id, c.name, c.created_at
  ORDER BY total DESC, c.name ASC
";
$cats = $conn->query($sqlCounts);

$currentCat = $_GET['category_id'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// คำนวณช่วงเวลา 7 วันย้อนหลัง สำหรับป้าย “ใหม่”
$sevenDaysAgo = strtotime('-7 days');

// หาค่าสูงสุด (เพื่อป้าย “ฮิต”)
$maxCount = 0;
$tmp = [];
while ($r = $cats->fetch_assoc()) {
  $tmp[] = $r;
  if ((int)$r['total'] > $maxCount) $maxCount = (int)$r['total'];
}
// รีเซ็ต pointer เพื่อวนซ้ำ
$cats = $tmp;
?>

<h6 class="fw-bold mb-2">หมวดหมู่สินค้า</h6>
<div class="list-group list-group-flush">

  <!-- ทั้งหมด -->
  <a href="index.php<?= $keyword ? '?keyword='.urlencode($keyword) : '' ?>"
     class="list-group-item list-group-item-action <?= $currentCat === '' ? 'active' : '' ?>">
     ทั้งหมด
     <span class="badge bg-secondary float-end">
       <?= array_sum(array_column($cats, 'total')) ?>
     </span>
  </a>

  <?php foreach ($cats as $c): 
    $link = "index.php?category_id={$c['id']}";
    if ($keyword !== '') $link .= "&keyword=" . urlencode($keyword);

    $isHot = ((int)$c['total'] === $maxCount && $maxCount > 0);
    $isNew = (strtotime($c['created_at']) >= $sevenDaysAgo);
  ?>
    <a href="<?= $link ?>"
       class="list-group-item list-group-item-action <?= ($currentCat == $c['id']) ? 'active' : '' ?>">
       <?= htmlspecialchars($c['name']) ?>

       <?php if ($isHot): ?>
         <span class="badge bg-danger ms-1">ฮิต</span>
       <?php endif; ?>

       <?php if ($isNew): ?>
         <span class="badge bg-success ms-1">ใหม่</span>
       <?php endif; ?>

       <span class="badge bg-light text-dark float-end">
         <?= (int)$c['total'] ?>
       </span>
    </a>
  <?php endforeach; ?>

</div>
