<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Putri - Jakarta Pusat</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary-web: #00b4d8; 
            --secondary-web: #0077b6; 
            --light-bg: #f0f9ff;
        }

        body { 
            background-color: var(--light-bg); 
            font-family: 'Quicksand', sans-serif;
            scroll-behavior: smooth;
        }

        h1, h2, h3, h4, h5, .navbar-brand, .btn { font-weight: 700; }

        .navbar { background: rgba(0, 119, 182, 0.8) !important; backdrop-filter: blur(10px); }
        .navbar-brand, .nav-link { color: white !important; font-weight: bold; }

        .hero { 
            height: 80vh; 
            background: linear-gradient(rgba(0, 72, 102, 0.6), rgba(0, 72, 102, 0.6)), 
                        url('https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?q=80&w=2070&auto=format&fit=crop') center/cover no-repeat; 
            display: flex; align-items: center; justify-content: center; text-align: center; color: white;
        }

        .service-box {
            border: none; border-radius: 15px; padding: 25px;
            background: #fff; transition: 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        .service-box:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 180, 216, 0.2); }
        .service-icon { font-size: 3rem; color: var(--primary-web); margin-bottom: 15px; }

        .card-custom {
            border: none; border-radius: 20px; overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            background: white;
        }

        .footer-custom { background: var(--secondary-web); color: white; padding: 40px 0; }
        .social-box {
            width: 40px; height: 40px; background: white;
            display: flex; justify-content: center; align-items: center;
            border-radius: 10px; color: var(--primary-web); transition: 0.2s; text-decoration: none;
        }
        .social-box:hover { background: var(--primary-web); color: white; }
        
        .cta-section {
            background: linear-gradient(135deg, var(--secondary-web), var(--primary-web));
            border-radius: 20px;
            color: white;
            padding: 50px;
            text-align: center;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="#">
            <i class="fa-solid fa-wand-sparkles me-2"></i> Laundry Putri
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#layanan">Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="#harga">Harga</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-primary ms-lg-3 px-4" href="login/login.php">Masuk Akun</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="hero text-white">
    <div class="container">
        <h1 class="display-1 mb-3">Laundry Putri</h1>
        <p class="lead mb-4 fs-4">Pakaian bersih, wangi, dan rapi tanpa repot.<br>Layanan Antar Jemput Wilayah Jakarta.</p>
        <div class="d-flex gap-3 justify-content-center mt-4">
            <a href="login/login.php" class="btn btn-primary btn-lg px-5 py-3 shadow">BUAT PESANAN SEKARANG</a>
            <a href="#layanan" class="btn btn-outline-light btn-lg px-5 py-3">PELAJARI LAYANAN</a>
        </div>
    </div>
</section>

<section id="layanan" class="py-5 bg-white">
    <div class="container text-center">
        <h2 class="mb-5">Kenapa Memilih Laundry Putri?</h2>
        <div class="row g-4 text-dark">
            <div class="col-md-4">
                <div class="service-box h-100">
                    <i class="fa-solid fa-soap service-icon"></i>
                    <h4>Cuci Higienis</h4>
                    <p class="text-muted">Kami menggunakan deterjen berkualitas dan mesin modern untuk memastikan pakaian Antum bersih maksimal dan bebas kuman.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-box h-100">
                    <i class="fa-solid fa-bolt service-icon"></i>
                    <h4>Proses Kilat</h4>
                    <p class="text-muted">Tersedia layanan ekspres 1 hari selesai untuk Antum yang memiliki mobilitas tinggi dan jadwal yang padat.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-box h-100">
                    <i class="fa-solid fa-truck-ramp-box service-icon"></i>
                    <h4>Gratis Antar Jemput</h4>
                    <p class="text-muted">Antum cukup di rumah, biar kurir kami yang menjemput dan mengantar pakaian Antum kembali dengan rapi.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="harga" class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="mb-5 text-white">Estimasi Daftar Harga</h2>
        <div class="row g-4 text-dark justify-content-center">
            <div class="col-md-4"><div class="p-4 bg-white rounded shadow h-100 border-top border-5 border-info"><h5>Cuci Lipat</h5><p class="fs-4 fw-bold text-primary mb-0">Rp 1 <small class="fs-6 text-muted">/kg</small></p></div></div>
            <div class="col-md-4"><div class="p-4 bg-white rounded shadow h-100 border-top border-5 border-info"><h5>Cuci Gosok</h5><p class="fs-4 fw-bold text-primary mb-0">Rp 2 <small class="fs-6 text-muted">/kg</small></p></div></div>
            <div class="col-md-4"><div class="p-4 bg-white rounded shadow h-100 border-top border-5 border-info"><h5>Laundry Karpet</h5><p class="fs-4 fw-bold text-primary mb-0">Rp 10 <small class="fs-6 text-muted">/m²</small></p></div></div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="cta-section shadow-lg">
            <i class="fa-solid fa-user-lock mb-3" style="font-size: 4rem;"></i>
            <h2 class="text-white mb-3">Siap Booking Laundry?</h2>
            <p class="lead mb-4">Untuk melakukan pemesanan dan menghitung estimasi biaya secara otomatis, silakan masuk ke akun Antum terlebih dahulu.</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="login/login.php" class="btn btn-light btn-lg text-primary px-5 py-3">LOGIN SEKARANG</a>
                <a href="register.php" class="btn btn-outline-light btn-lg px-5 py-3">DAFTAR AKUN BARU</a>
            </div>
        </div>
    </div>
</section>

<footer class="footer-custom">
    <div class="container text-white">
        <div class="row g-4">
            <div class="col-md-4 text-white">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fa-solid fa-wand-sparkles fs-3"></i>
                    <h4 class="mb-0 text-white">Laundry Putri</h4>
                </div>
                <p class="small opacity-75">Jln. Petamburan 1 gang 2 RT04/RW01 NO. 26 Tanah Abang Jakarta Pusat, DKI Jakarta</p>
                <div class="d-flex gap-2 mt-3">
                </div>
            </div>
            <div class="col-md-5">
                <h5 class="mb-3 text-white"><i class="fa-solid fa-clock me-2"></i>Jam Operasional</h5>
                <div class="row g-2 text-white">
                    <div class="col-6"><div class="op-hours"><strong>Sesi Pagi</strong><br>09:00 - 12:00 WIB</div></div>
                    <div class="col-6"><div class="op-hours"><strong>Sesi Malam</strong><br>19:00 - 22:00 WIB</div></div>
                </div>
            </div>
            <div class="col-md-3 text-md-end">
                <h5 class="mb-3 text-white">Navigasi</h5>
                <ul class="list-unstyled small opacity-75">
                    <li class="mb-2"><a href="#layanan" class="text-white text-decoration-none">Layanan</a></li>
                    <li class="mb-2"><a href="#harga" class="text-white text-decoration-none">Daftar Harga</a></li>
                    <li class="mb-2"><a href="register.php" class="text-white text-decoration-none">Login / Register</a></li>
                </ul>
            </div>
        </div>
        <hr class="mt-5 opacity-25">
        <p class="text-center small mb-0 opacity-50">© 2025 Laundry Putri Petamburan. All Rights Reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>