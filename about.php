<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="th">

<head>
    <meta charset="utf-8">

    <title>เกี่ยวกับร้าน | สมศรี ซุปเปอร์ สโตร์</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"
    >

    <!-- Google Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        /* ==============================
           ROOT
        ============================== */

        :root {
            --ios-bg: #f2f2f7;
            --ios-blue: #007aff;
            --ios-purple: #af52de;
            --ios-pink: #ff2d55;
        }


        /* ==============================
           BODY
        ============================== */

        body {
            font-family: 'Kanit', sans-serif;
            background: var(--ios-bg);
            color: #1c1c1e;
            overflow-x: hidden;
            margin: 0;
        }


        /* ==============================
           HERO
        ============================== */

        .story-hero {
            position: relative;

            background:
                linear-gradient(
                    145deg,
                    #007aff 0%,
                    #af52de 100%
                );

            color: #fff;

            padding: 80px 20px;

            text-align: center;

            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;

            box-shadow:
                0 15px 35px
                rgba(0, 122, 255, 0.20);
        }


        .story-hero h1 {
            font-weight: 700;
            letter-spacing: -1px;
        }


        .story-hero p {
            margin-bottom: 0;
        }


        /* ==============================
           HERO PILLS
        ============================== */

        .pill-wrap {
            display: inline-flex;

            gap: 10px;

            background:
                rgba(255, 255, 255, 0.20);

            padding: 10px 20px;

            border-radius: 20px;

            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);

            margin-top: 20px;

            flex-wrap: wrap;

            justify-content: center;
        }


        .pill-item {
            font-size: 0.9rem;
            color: #fff;
            font-weight: 400;
        }


        /* ==============================
           CONTENT
        ============================== */

        .story-content {
            padding-top: 60px;
            padding-bottom: 60px;
        }


        /* ==============================
           INFOGRAPHIC CARD
        ============================== */

        .infographic-card {
            position: relative;

            background: #fff;

            border-radius: 30px;

            overflow: hidden;

            border: 1px solid
                rgba(255, 255, 255, 0.8);

            box-shadow:
                0 15px 45px
                rgba(0, 0, 0, 0.10);

            transition:
                transform 0.3s ease,
                box-shadow 0.3s ease;
        }


        .infographic-card:hover {
            transform: translateY(-5px);

            box-shadow:
                0 20px 55px
                rgba(0, 0, 0, 0.14);
        }


        /* ==============================
           INFOGRAPHIC IMAGE
        ============================== */

        .infographic-card img {
            display: block;

            width: 100%;

            height: auto;

            max-width: 100%;
        }


        /* ==============================
           IMAGE CAPTION
        ============================== */

        .infographic-caption {
            padding: 20px;

            text-align: center;

            background: #fff;
        }


        .infographic-caption h5 {
            margin: 0;

            font-weight: 600;

            color: #1c1c1e;
        }


        .infographic-caption p {
            margin: 5px 0 0;

            color: #6c6c70;

            font-size: 0.9rem;
        }


        /* ==============================
           FOOTER TEXT
        ============================== */

        .story-bottom {
            text-align: center;

            margin-top: 30px;

            color: #6c6c70;
        }


        .story-bottom strong {
            color: var(--ios-blue);
        }


        /* ==============================
           MOBILE
        ============================== */

        @media (max-width: 768px) {

            .story-hero {
                padding: 60px 15px 70px;

                border-bottom-left-radius: 30px;
                border-bottom-right-radius: 30px;
            }


            .story-hero h1 {
                font-size: 2rem;
            }


            .story-hero p {
                font-size: 1rem !important;
            }


            .pill-wrap {
                padding: 9px 14px;

                gap: 7px;
            }


            .pill-item {
                font-size: 0.8rem;
            }


            .story-content {
                padding-top: 35px;
                padding-bottom: 40px;
            }


            .infographic-card {
                border-radius: 20px;
            }


            .infographic-caption {
                padding: 15px;
            }


            .infographic-caption h5 {
                font-size: 1rem;
            }

        }


        /* ==============================
           SMALL MOBILE
        ============================== */

        @media (max-width: 480px) {

            .story-hero {
                padding: 50px 12px 60px;
            }


            .story-hero h1 {
                font-size: 1.7rem;
            }


            .pill-wrap {
                width: 100%;

                justify-content: center;
            }


            .story-content {
                padding-left: 8px;
                padding-right: 8px;
            }


            .infographic-card {
                border-radius: 16px;
            }

        }

    </style>

</head>


<body>


<!-- ==========================================
     HEADER
========================================== -->

<?php include __DIR__ . "/partials/header.php"; ?>


<!-- ==========================================
     HERO
========================================== -->

<section class="story-hero">

    <div class="container">

        <h1 class="display-5 mb-2">
            สมศรี ซุปเปอร์ สโตร์
        </h1>

        <p class="fs-5 opacity-75">
            "ซื้อของครบ พบของถูกใจ — โตไปพร้อมชุมชนกันทรลักษ์"
        </p>


        <div class="pill-wrap">

            <span class="pill-item">
                🏪 ค้าส่ง–ปลีก
            </span>

            <span class="pill-item">
                •
            </span>

            <span class="pill-item">
                🚚 ส่งทั่วไทย
            </span>

            <span class="pill-item">
                •
            </span>

            <span class="pill-item">
                💬 บริการทุกวัน
            </span>

        </div>

    </div>

</section>



<!-- ==========================================
     INFOGRAPHIC
========================================== -->

<main class="story-content">

    <div class="container">

        <div class="infographic-card">

            <img
                src="assets/img/somsri-story-infographic.png"
                alt="เรื่องราวของสมศรี ซุปเปอร์ สโตร์"
                loading="lazy"
            >


            <div class="infographic-caption">

                <h5>

                    <i
                        class="bi bi-clock-history text-primary me-2">
                    </i>

                    เรื่องราวของสมศรี ซุปเปอร์ สโตร์

                </h5>

                <p>
                    จากแผงเล็ก ๆ สู่ศูนย์ค้าส่ง–ปลีกครบวงจร
                </p>

            </div>

        </div>


        <!-- ==================================
             BOTTOM MESSAGE
        ================================== -->

        <div class="story-bottom">

            <p class="mb-0">
                <strong>สมศรี ซุปเปอร์ สโตร์</strong>
                <br>
                เติบโตไปพร้อมลูกค้า และชุมชนกันทรลักษ์
            </p>

        </div>

    </div>

</main>



<!-- ==========================================
     BOOTSTRAP JS
========================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
</script>


</body>

</html>