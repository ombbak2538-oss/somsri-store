<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>ติดต่อร้าน | สมศรี ซุปเปอร์ สโตร์</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <meta property="og:title" content="ติดต่อร้าน - สมศรี ซุปเปอร์ สโตร์">
  <meta property="og:description" content="ช่องทางติดต่อร้านสมศรี ซุปเปอร์ สโตร์ พร้อมแผนที่ Google Maps">
  <meta property="og:image" content="/somsri-store/public/assets/banners/banner2.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --brand-1:#0000CC;
      --brand-2:#FF00FF;
      --soft:#f7f7fb;
    }
    body{
      font-family:'Kanit',system-ui,-apple-system,Segoe UI,sans-serif;
      background:var(--soft);
    }
    .hero{
      background: linear-gradient(135deg, var(--brand-1), var(--brand-2));
      color:#fff;
      padding:48px 16px 40px;
      border-radius:0 0 28px 28px;
      box-shadow:0 10px 24px rgba(0,0,0,.12);
      text-align:center;
    }
    .card{
      border-radius:16px;
      box-shadow:0 8px 20px rgba(0,0,0,.08);
      border:0;
    }
    .pill{  
      display:inline-flex; align-items:center; gap:6px;
      padding:8px 12px; border-radius:999px;
      border:1px solid #e9ecef; background:#2F4F4F; font-size:12px;
    }
    .map-wrap{
      border-radius:16px; overflow:hidden;
      box-shadow:0 10px 24px rgba(0,0,0,.12);
      border:1px solid #eee;
    }
    .cta{ border-radius:999px; }
    .icon{
      width:40px; height:40px; border-radius:12px;
      display:flex; align-items:center; justify-content:center;
      background:linear-gradient(135deg, var(--brand-1), var(--brand-2));
      color:#fff; font-size:20px;
      box-shadow:0 8px 16px rgba(0,0,0,.18);
    }
  </style>
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<section class="hero">
  <h1 class="fw-bold mb-1">📞 ติดต่อร้านสมศรี ซุปเปอร์ สโตร์</h1>
  <p class="mb-2">พร้อมให้บริการทุกวันในเวลาทำการ</p>
  <div class="d-flex justify-content-center gap-2 flex-wrap">
    <span class="pill">⏰ เปิดร้าน 08:00–17:00 น.</span>
    <span class="pill">🚚 จัดส่งในพื้นที่</span>
    <span class="pill">💬 แชทได้ทุกวัน</span>
  </div>
</section>

<div class="container py-4">
  <div class="row g-3">

    <div class="col-12 col-lg-5">
      <div class="card p-3 h-100">
        <h5 class="fw-bold mb-3">ข้อมูลติดต่อ</h5>

        <div class="d-flex gap-3 align-items-start mb-3">
          <div class="icon">📍</div>
          <div>
            <div class="fw-semibold">ที่ตั้งร้าน</div>
            <div class="text-muted small">
              ร้านสมศรี ซุปเปอร์ สโตร์<br>
              อ.กันทรลักษ์ จ.ศรีสะเกษ
            </div>
          </div>
        </div>

        <div class="d-flex gap-3 align-items-start mb-3">
          <div class="icon">⏰</div>
          <div>
            <div class="fw-semibold">เวลาเปิดทำการ</div>
            <div class="text-muted small">ทุกวัน 08:00–17:00 น.</div>
          </div>
        </div>

        <div class="d-flex gap-3 align-items-start mb-3">
          <div class="icon">📞</div>
          <div>
            <div class="fw-semibold">โทรศัพท์</div>
            <a class="text-decoration-none" href="tel:0862443413">086-244-3413</a>
          </div>
        </div>

        <div class="d-flex gap-3 align-items-start mb-3">
          <div class="icon">📘</div>
          <div>
            <div class="fw-semibold">Facebook</div>
            <a class="text-decoration-none" target="_blank"
               href="https://www.facebook.com/share/1ArHs5Mo4A/?mibextid=wwXIfr">
              เพจ ร้านสมศรีกันทรลักษ์ค้าส่ง
            </a>
          </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mt-2">
          <a href="tel:0862443413" class="btn btn-danger cta">📞 โทรหาเรา</a>
          <a target="_blank" href="https://line.me/R/ti/p/@YOUR_LINE_ID" class="btn btn-success cta">💬 แชท LINE</a>
          <a target="_blank" href="https://www.facebook.com/share/1ArHs5Mo4A/?mibextid=wwXIfr" class="btn btn-primary cta">📘 แชท Facebook</a>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card p-2 h-100">
        <h5 class="fw-bold mb-2 px-2">🗺️ แผนที่ร้าน</h5>
        <div class="map-wrap">
            <iframe
              src="https://www.google.com/maps?q=14.6471213,104.6315842&hl=th&z=17&output=embed"
              width="100%"
              height="380"
              style="border:0;"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
        <div class="text-muted small mt-2 px-2">
        </div>
      </div>
    </div>

  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>