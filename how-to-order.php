<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>วิธีการสั่งซื้อ | สมศรี ซุปเปอร์ สโตร์</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
--ios-bg:#f2f2f7;
--ios-blue:#007aff;
--ios-purple:#af52de;
--glass:rgba(255,255,255,0.85);
}

body{
font-family:'Kanit',sans-serif;
background:var(--ios-bg);
color:#1c1c1e;
}

.hero-title{
background:linear-gradient(135deg,var(--ios-blue),var(--ios-purple));
color:white;
padding:70px 20px;
text-align:center;
border-bottom-left-radius:40px;
border-bottom-right-radius:40px;
margin-bottom:-40px;
}

.hero-title h1{
font-weight:700;
letter-spacing:-0.3px;
}

.order-step-card{
background:var(--glass);
backdrop-filter:blur(25px);
border-radius:30px;
border:1px solid rgba(255,255,255,0.4);
box-shadow:0 10px 35px rgba(0,0,0,0.06);
padding:35px;
margin-bottom:35px;
transition:.3s;
}

.order-step-card:hover{
transform:translateY(-4px);
box-shadow:0 20px 45px rgba(0,0,0,0.08);
}

.step-badge{
background:var(--ios-blue);
color:white;
width:38px;
height:38px;
border-radius:12px;
display:flex;
align-items:center;
justify-content:center;
font-weight:700;
margin-bottom:12px;
box-shadow:0 5px 15px rgba(0,122,255,0.35);
}

.instruction-img{
border-radius:20px;
border:1px solid rgba(0,0,0,0.05);
transition:.3s;
cursor:zoom-in;
margin:18px 0;
width:100%;
max-width:600px;
display:block;
}

.instruction-img:hover{
transform:scale(1.02);
box-shadow:0 18px 35px rgba(0,0,0,0.12);
}

.alert-ios{
background:rgba(255,45,85,0.1);
color:#ff3b30;
border-radius:20px;
padding:16px 20px;
border:none;
font-size:.9rem;
}

.nav-pills .nav-link{
border-radius:15px;
color:#8e8e93;
font-weight:500;
padding:10px 18px;
}

.nav-pills .nav-link.active{
background:#fff;
color:var(--ios-blue);
box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

.pill-container{
background:#e3e3e8;
padding:5px;
border-radius:20px;
display:inline-flex;
}

.tip-box{
background:#f8f9fb;
border-radius:16px;
padding:15px;
font-size:.9rem;
}

</style>
</head>
<body>

<?php include __DIR__ . "/partials/header.php"; ?>

<section class="hero-title">
<div class="container">
<h1>🛒 วิธีการสั่งซื้อสินค้า</h1>
<p class="opacity-75">สั่งง่าย ส่งไว ครบจบที่ <strong>สมศรี ซุปเปอร์ สโตร์</strong></p>
</div>
</section>

<div class="container pb-5">

<div class="text-center mb-5" style="margin-top:60px;">
<div class="pill-container">
<ul class="nav nav-pills" role="tablist">

<li class="nav-item">
<button class="nav-link active" data-bs-toggle="pill" data-bs-target="#method1">
👤 สมัครสมาชิกก่อน
</button>
</li>

<li class="nav-item">
<button class="nav-link" data-bs-toggle="pill" data-bs-target="#method2">
⚡ สั่งก่อนสมัคร
</button>
</li>

</ul>
</div>
</div>

<div class="tab-content">

<div class="tab-pane fade show active" id="method1">

<div class="row justify-content-center">
<div class="col-lg-10">

<div class="order-step-card">

<div class="step-badge">1</div>
<h4 class="fw-bold">👤 สมัครสมาชิก</h4>

<p class="text-secondary">
เริ่มต้นโดยเข้าไปที่หน้า <strong>สมัครสมาชิก</strong> เพื่อสร้างบัญชีผู้ใช้งาน
</p>

<img src="./advertising/how-to-order1.png" class="instruction-img">

<p class="small text-muted">
🔗 เข้าใช้งานได้ที่  
<a href="/login" class="text-primary fw-bold text-decoration-none">
www.somsristore.com/login
</a>
</p>

</div>


<div class="order-step-card">

<div class="step-badge">2</div>
<h4 class="fw-bold">📝 กรอกข้อมูลสมาชิก</h4>

<p class="text-secondary">
เลือกเมนู <strong>สมัครสมาชิกใหม่</strong> และกรอกข้อมูลให้ครบถ้วน
</p>

<img src="./advertising/how-to-order2.png" class="instruction-img">
<img src="./advertising/how-to-order3.png" class="instruction-img">

<div class="alert alert-info border-0 rounded-4">

ℹ️ กรอกข้อมูลให้ถูกต้อง พร้อมยืนยัน Captcha  
จากนั้นกดปุ่ม <strong>สมัครสมาชิก</strong>

</div>

</div>


<div class="order-step-card">

<div class="step-badge">3</div>
<h4 class="fw-bold">🛍️ เลือกสินค้า</h4>

<p class="text-secondary">
หลังจากสมัครสำเร็จ คุณสามารถเลือกสินค้าได้ทันที
ผ่านหมวดหมู่หรือช่องค้นหา
</p>

<img src="./advertising/how-to-order7.png" class="instruction-img">

<div class="row g-3 mt-2">

<div class="col-md-6">
<div class="tip-box">

<h6 class="fw-bold">🛒 ใส่ตะกร้า</h6>
<p class="mb-0 text-muted">
กดปุ่มตะกร้าเพื่อเพิ่มสินค้า  
รายการจะถูกเก็บไว้ในรถเข็น
</p>

</div>
</div>

<div class="col-md-6">
<div class="tip-box">

<h6 class="fw-bold">✅ ยืนยันคำสั่งซื้อ</h6>
<p class="mb-0 text-muted">
ตรวจสอบรายการสินค้า  
เลือกการชำระเงิน และกดยืนยัน
</p>

</div>
</div>

</div>

</div>


<div class="order-step-card">

<div class="step-badge">4</div>
<h4 class="fw-bold">📦 การจัดส่งสินค้า</h4>

<p class="text-secondary">
ตรวจสอบที่อยู่จัดส่งในหน้า <strong>โปรไฟล์</strong> ให้ถูกต้อง  
พนักงานจะติดต่อยืนยันก่อนจัดส่ง
</p>

<img src="./advertising/how-to-order10.png" class="instruction-img">

<div class="alert-ios mt-3">

⚠️ <strong>สำคัญ</strong>  
หากข้อมูลไม่ถูกต้องหรือไม่สามารถติดต่อได้  
คลังสินค้าจะไม่สามารถจัดส่งสินค้าได้

</div>

</div>

</div>
</div>

</div>


<div class="tab-pane fade" id="method2">

<div class="row justify-content-center">
<div class="col-lg-8">

<div class="order-step-card text-center">

<div style="font-size:3rem">⚠️</div>

<h3 class="fw-bold mt-3">สั่งซื้อก่อนสมัครสมาชิก</h3>

<p class="text-secondary">
คุณสามารถเลือกสินค้าก่อนแล้วค่อยสมัครสมาชิกได้
แต่มีความเสี่ยงที่รายการจะหาย
</p>

<div class="alert-ios text-start my-4">

🛑 หากยังไม่ได้สมัครสมาชิก  
ระบบจะไม่บันทึกสินค้าที่เลือกไว้

หากปิดหน้าเว็บ  
รายการทั้งหมดจะหายทันที

</div>

<img src="./advertising/how-to-order11.png" class="instruction-img mx-auto">

<p class="mt-4 text-muted">

เพื่อความปลอดภัยของข้อมูล  
ระบบจึงแนะนำให้

<strong>สมัครสมาชิกก่อนสั่งซื้อ</strong>

</p>

</div>

</div>
</div>

</div>

</div>
</div>

<div class="modal fade" id="imgModal">
<div class="modal-dialog modal-dialog-centered modal-lg">
<div class="modal-content bg-transparent border-0">
<div class="modal-body p-0">

<img src="" id="modalImg" class="w-100 rounded-4">

<button class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
data-bs-dismiss="modal"></button>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.querySelectorAll('.instruction-img').forEach(img=>{
img.onclick=()=>{
const modal=new bootstrap.Modal(document.getElementById('imgModal'))
document.getElementById('modalImg').src=img.src
modal.show()
}
})

</script>

</body>
</html>