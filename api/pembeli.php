<?php
session_start();

// 1. Jalur koneksi (Gunakan __DIR__ agar stabil di Vercel)
include __DIR__ . '/koneksi.php'; 

// 2. Logika Satpam Gabungan: Periksa Session ATAU Cookie
$is_login = (isset($_SESSION['status']) && $_SESSION['status'] == "login") || 
             (isset($_COOKIE['user_status']) && $_COOKIE['user_status'] == "login");

$user_level = $_SESSION['level'] ?? $_COOKIE['user_level'] ?? '';

// 3. Jika tidak login, arahkan ke folder login
if (!$is_login) {
    header("location:login/login.php?pesan=belum_login");
    exit;
}

// 4. Pastikan yang masuk adalah level 'pembeli'
if ($user_level != "pembeli") {
    // Jika Admin/Kasir nyasar ke sini, biarkan atau lempar ke dashboard masing-masing
    header("location:login/login.php?pesan=bukan_pembeli");
    exit;
}

// Ambil Nama untuk sapaan (opsional)
$nama_pembeli = $_SESSION['nama_lengkap'] ?? $_COOKIE['user_name'] ?? 'Pelanggan';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laundry Putri - Booking</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

        .navbar { background: rgba(0, 119, 182, 0.9) !important; backdrop-filter: blur(10px); }
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
        }
        .service-box:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0, 180, 216, 0.2); }
        .service-icon { font-size: 2.5rem; color: var(--primary-web); margin-bottom: 8px; }

        .card-custom {
            border: none; border-radius: 20px; overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .footer-custom { background: var(--secondary-web); color: white; padding: 40px 0; }
        
        .op-hours {
            background: rgba(255,255,255,0.1); padding: 10px;
            border-radius: 8px; font-size: 0.85rem;
        }

        /* 2. STYLE KHUSUS MAP */
        #map { 
            height: 250px;       
            width: 100%; 
            border-radius: 10px; 
            border: 2px solid #ddd;
            z-index: 1;          
        }

        .modal-header { border-bottom: none; }
        .modal-footer { border-top: none; }
        .dashed { border-top: 2px dashed #bbb; }
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
            <ul class="navbar-nav ms-auto align-items-center"> 
                <li class="nav-item"><a class="nav-link" href="#layanan">Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="#harga">Harga</a></li>
                <li class="nav-item"><a class="nav-link" href="#kalkulasi">Antar Jemput</a></li>
                
                <li class="nav-item ms-lg-2">
                    <a class="nav-link text-warning fw-bold" href="lacak.php">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Lacak Pesanan
                    </a>
                </li>

                <li class="nav-item ms-lg-3 mt-2 mt-lg-0">
                    <a class="btn btn-danger rounded-pill px-4 shadow-sm" href="admin/logout.php" onclick="return confirm('Apakah Antum yakin ingin keluar?')">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="hero text-white">
    <div class="container">
        <h1 class="display-1 mb-3">Laundry Putri</h1>
        <p class="lead mb-4 fs-4">Pakaian bersih, wangi, dan rapi tanpa repot.<br>Layanan Antar Jemput Wilayah Jakarta.</p>
        <div class="d-flex gap-3 justify-content-center mt-4">
            <a href="#kalkulasi" class="btn btn-primary btn-lg px-5 py-3 shadow">PESAN SEKARANG</a>
            <a href="#harga" class="btn btn-outline-light btn-lg px-5 py-3">LIHAT HARGA</a>
        </div>
    </div>
</section>

<section id="layanan" class="py-5 bg-white text-center">
    <div class="container">
        <h2 class="mb-5">Layanan Kami</h2>
        <div class="row g-4 text-dark">
            <div class="col-md-3 col-6"><div class="service-box h-100 text-center"><i class="fa-solid fa-soap service-icon"></i><h5>Cuci Lipat</h5></div></div>
            <div class="col-md-3 col-6"><div class="service-box h-100 text-center"><i class="fa-solid fa-shirt service-icon"></i><h5>Cuci Gosok</h5></div></div>
            <div class="col-md-3 col-6"><div class="service-box h-100 text-center"><i class="fa-solid fa-rug service-icon"></i><h5>Cuci Karpet</h5></div></div>
            <div class="col-md-3 col-6"><div class="service-box h-100 text-center"><i class="fa-solid fa-motorcycle service-icon"></i><h5>Antar Jemput</h5></div></div>
        </div>
    </div>
</section>

<section id="harga" class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h2 class="mb-5 text-white">Daftar Harga</h2>
        <div class="row g-4 text-dark justify-content-center">
            <div class="col-md-4"><div class="p-4 bg-white rounded shadow h-100 border-top border-5 border-info"><h5>Cuci Lipat</h5><p class="fs-4 fw-bold text-primary mb-0">Rp 1 <small class="fs-6 text-muted">/kg</small></p></div></div>
            <div class="col-md-4"><div class="p-4 bg-white rounded shadow h-100 border-top border-5 border-info"><h5>Cuci Gosok</h5><p class="fs-4 fw-bold text-primary mb-0">Rp 2 <small class="fs-6 text-muted">/kg</small></p></div></div>
            <div class="col-md-4"><div class="p-4 bg-white rounded shadow h-100 border-top border-5 border-info"><h5>Laundry Karpet</h5><p class="fs-4 fw-bold text-primary mb-0">Rp 10 <small class="fs-6 text-muted">/m²</small></p></div></div>
        </div>
    </div>
</section>

<section id="kalkulasi" class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card card-custom shadow border-0">
                    <div class="row g-0">
                        <div class="col-md-4 bg-primary text-white p-5 d-flex flex-column justify-content-center text-center" style="background: linear-gradient(135deg, var(--secondary-web), var(--primary-web)) !important;">
                            <i class="fa-solid fa-truck-fast mb-3" style="font-size: 5rem;"></i>
                            <h2 class="text-white">Booking</h2>
                            <p class="text-white opacity-75">Antum cukup di rumah, biar kurir kami yang menjemput pakaiannya.</p>
                        </div>
                        
                        <div class="col-md-8 p-4 p-lg-5 bg-white">
                            <h3 class="mb-4 text-dark">Estimasi & Antar Jemput</h3>
                            
                            <form id="laundryForm" class="row g-3" onsubmit="cekPesanan(event)">
                                <div class="col-md-6">
    <label class="form-label">Nama</label>
    <input type="text" id="inputNama" class="form-control" value="<?php echo $nama_pembeli; ?>" readonly required>
</div>

<div class="col-md-6">
    <label class="form-label">WhatsApp</label>
    <input type="text" id="inputWa" class="form-control" value="<?php echo $_SESSION['no_wa'] ?? ''; ?>" required>
</div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Layanan</label>
                                    <select class="form-select" id="jenisLayanan">
                                        <option value="Lipat">Cuci Lipat</option>
                                        <option value="Gosok">Cuci Gosok</option>
                                        <option value="Karpet">Laundry Karpet</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Kecepatan</label>
                                    <select class="form-select" id="durasiLayanan">
                                        <option value="1">1 Hari (Kilat)</option>
                                        <option value="2">2 Hari (Express)</option>
                                        <option value="3" selected>3 Hari (Reguler)</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-primary">Berat (Kg)</label>
                                    <input type="number" id="inputBerat" class="form-control border-primary" value="1" min="1">
                                    <div class="form-text text-muted small">
                                        *Isi sesuai timbangan atau <strong>kira-kira</strong> saja.
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Lokasi Penjemputan</label>
                                    
                                    <div id="map" class="mb-2 shadow-sm"></div>
                                    <small class="text-primary d-block mb-2" style="font-size: 11px;">*Geser pin biru untuk titik lokasi rumah.</small>
                                    
                                    <textarea id="inputAlamat" class="form-control bg-light" rows="2" required placeholder="Alamat lengkap akan muncul di sini..."></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <div class="alert alert-info d-flex align-items-start small shadow-sm border-0 mt-2">
                                        <i class="fa-solid fa-circle-info me-2 mt-1 fs-5"></i>
                                        <div>
                                            <strong>Info Pembayaran:</strong><br>
                                            Harga yang muncul adalah <strong>Estimasi</strong> berdasarkan input Antum. <br>
                                            Jika nanti saat ditimbang kurir beratnya berbeda, selisih biaya akan diinfokan via WhatsApp.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-white rounded shadow-sm border">
                                        <div>
                                            <small class="text-muted d-block">Total Estimasi:</small>
                                            <span id="total-harga-display" class="fw-bold fs-3 text-primary">Rp 4.000</span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block" id="durasi-info">3 Hari Kerja</small>
                                            <small class="fw-bold text-dark" id="harga-satuan-text">@ Rp 4.000 /kg</small>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg w-100 py-3 shadow">
                                        <i class="fa-solid fa-credit-card me-2"></i>Lanjut ke Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
            </div>

            <div class="col-md-5">
                <h5 class="mb-3 text-white"><i class="fa-solid fa-clock me-2"></i>Jam Operasional</h5>
                <div class="row g-2">
                    <div class="col-6"><div class="op-hours text-white"><strong>Sesi Pagi</strong><br>09:00 - 12:00 WIB</div></div>
                    <div class="col-6"><div class="op-hours text-white"><strong>Sesi Siang</strong><br>13:30 - Ashar</div></div>
                    <div class="col-6"><div class="op-hours text-white"><strong>Sesi Sore</strong><br>Ashar - Maghrib</div></div>
                    <div class="col-6"><div class="op-hours text-white"><strong>Sesi Malam</strong><br>19:00 - 22:00 WIB</div></div>
                </div>
            </div>

            <div class="col-md-3 text-md-end">
                <h5 class="mb-3 text-white">Navigasi</h5>
                <ul class="list-unstyled small opacity-75">
                    <li class="mb-2"><a href="#layanan" class="text-white text-decoration-none">Layanan</a></li>
                    <li class="mb-2"><a href="#harga" class="text-white text-decoration-none">Daftar Harga</a></li>
                    <li class="mb-2"><a href="#kalkulasi" class="text-white text-decoration-none">Antar Jemput</a></li>
                </ul>
            </div>
        </div>
        <hr class="mt-5 opacity-25 bg-white">
        <p class="text-center small mb-0 opacity-50">© 2025 Laundry Putri Petamburan. All Rights Reserved.</p>
    </div>
</footer>

<div class="modal fade" id="modalKonfirmasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-check me-2"></i>Cek Pesanan Antum</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted text-center mb-4">Pastikan data di bawah ini sudah benar sebelum melanjutkan.</p>
                <table class="table table-sm table-borderless">
                    <tr><td class="text-muted" width="35%">Nama</td><td class="fw-bold" id="konf-nama">: -</td></tr>
                    <tr><td class="text-muted">WhatsApp</td><td class="fw-bold" id="konf-wa">: -</td></tr>
                    <tr><td class="text-muted">Alamat</td><td class="fw-bold" id="konf-alamat">: -</td></tr>
                    <tr><td colspan="2"><hr class="my-1 dashed"></td></tr>
                    <tr><td class="text-muted">Layanan</td><td class="fw-bold" id="konf-layanan">: -</td></tr>
                    <tr><td class="text-muted">Durasi</td><td class="fw-bold" id="konf-durasi">: -</td></tr>
                    <tr><td class="text-muted">Berat</td><td class="fw-bold" id="konf-berat">: -</td></tr>
                    <tr><td class="text-muted">Total Harga</td><td class="fw-bold text-primary fs-5" id="konf-total">: -</td></tr>
                </table>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary px-4 shadow" onclick="kirimPesananAkhir()">Ya, Sudah Benar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // --- 4. SCRIPT PETA (LEAFLET) ---
    // Koordinat Default: Petamburan / Tanah Abang
    var defaultLat = -6.193630;
    var defaultLng = 106.808390;

    var map = L.map('map').setView([defaultLat, defaultLng], 15);
    
    // Tile (Gambar Peta)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Marker (Pin)
    var marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    // Fungsi Update Alamat (Reverse Geocoding)
    async function cariAlamat(lat, lng) {
        document.getElementById('inputAlamat').value = "Sedang mencari titik lokasi...";
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            if(data && data.display_name) {
                document.getElementById('inputAlamat').value = data.display_name;
            } else {
                document.getElementById('inputAlamat').value = "Titik kordinat ditemukan. Silakan lengkapi detail rumah.";
            }
        } catch (error) {
            document.getElementById('inputAlamat').value = "Gagal memuat nama jalan. Ketik manual saja ya.";
        }
    }

    // Event: Saat Pin Digeser
    marker.on('dragend', function(e) {
        var pos = marker.getLatLng();
        cariAlamat(pos.lat, pos.lng);
    });

    // --- LOGIKA HITUNG HARGA ---
    const jenisLayanan = document.getElementById('jenisLayanan');
    const durasiLayanan = document.getElementById('durasiLayanan');
    const inputBerat = document.getElementById('inputBerat');
    
    // Tempat Tampil
    const totalDisplay = document.getElementById('total-harga-display');
    const hargaSatuanText = document.getElementById('harga-satuan-text');
    const durasiInfo = document.getElementById('durasi-info');

    const opt1 = durasiLayanan.querySelector('option[value="1"]');
    const opt2 = durasiLayanan.querySelector('option[value="2"]');

    let totalHargaInt = 0;

    const pricelist = {
        'Lipat': { '1': 3, '2': 2, '3': 1 },
        'Gosok': { '1': 6, '2': 4, '3': 2 },
        'Karpet': { '3': 10 }
    };

    function hitungTotal() {
        let layanan = jenisLayanan.value;
        let durasi = durasiLayanan.value;
        
        // Logika Khusus Karpet (Hanya 3 Hari)
        if (layanan === 'Karpet') {
            if(opt1) opt1.disabled = true;
            if(opt2) opt2.disabled = true;
            durasiLayanan.value = "3";
            durasi = "3"; 
        } else {
            if(opt1) opt1.disabled = false;
            if(opt2) opt2.disabled = false;
        }

        let berat = parseFloat(inputBerat.value) || 0;

        let hargaSatuan = 0;
        if(pricelist[layanan] && pricelist[layanan][durasi]) {
            hargaSatuan = pricelist[layanan][durasi];
        }

        totalHargaInt = hargaSatuan * berat;

        let satuanLabel = (layanan === 'Karpet' ? ' /m²' : ' /kg');
        hargaSatuanText.innerText = 'Rp ' + hargaSatuan.toLocaleString('id-ID') + satuanLabel;
        durasiInfo.innerText = 'Estimasi: ' + durasi + ' Hari Kerja';
        totalDisplay.innerText = 'Rp ' + totalHargaInt.toLocaleString('id-ID');
    }

    jenisLayanan.addEventListener('change', hitungTotal);
    durasiLayanan.addEventListener('change', hitungTotal);
    inputBerat.addEventListener('input', hitungTotal);
    document.addEventListener('DOMContentLoaded', hitungTotal);

    // --- FUNGSI 1: TAMPILKAN POP-UP ---
    function cekPesanan(event) {
        event.preventDefault(); 
        
        let nama = document.getElementById('inputNama').value;
        let wa = document.getElementById('inputWa').value;
        let alamat = document.getElementById('inputAlamat').value;
        let layanan = document.getElementById('jenisLayanan').value;
        let durasi = document.getElementById('durasiLayanan').options[document.getElementById('durasiLayanan').selectedIndex].text;
        let berat = document.getElementById('inputBerat').value;
        let total = document.getElementById('total-harga-display').innerText;

        if(!nama || !wa || !alamat) {
            alert("Mohon lengkapi Nama, WA, dan Alamat dulu ya!");
            return;
        }

        document.getElementById('konf-nama').innerText = ": " + nama;
        document.getElementById('konf-wa').innerText = ": " + wa;
        document.getElementById('konf-alamat').innerText = ": " + alamat;
        document.getElementById('konf-layanan').innerText = ": " + layanan;
        document.getElementById('konf-durasi').innerText = ": " + durasi;
        document.getElementById('konf-berat').innerText = ": " + berat + " Kg/m²";
        document.getElementById('konf-total').innerText = ": " + total;

        var myModal = new bootstrap.Modal(document.getElementById('modalKonfirmasi'));
        myModal.show();
    }

    // --- FUNGSI 2: KIRIM KE DATABASE ---
    async function kirimPesananAkhir() {
        const btn = document.querySelector('#modalKonfirmasi .btn-primary');
        const originalText = btn.innerText;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
        btn.disabled = true;

        let formData = new FormData();
        formData.append('kirim_pesanan', 'true');
        formData.append('nama_pelanggan', document.getElementById('inputNama').value);
        formData.append('no_wa', document.getElementById('inputWa').value);
        formData.append('alamat_jemput', document.getElementById('inputAlamat').value);
        formData.append('jenis_layanan', document.getElementById('jenisLayanan').value);
        formData.append('durasi_layanan', document.getElementById('durasiLayanan').value);
        formData.append('berat_qty', document.getElementById('inputBerat').value);

        try {
            const response = await fetch('proses_pesan.php', { method: 'POST', body: formData });
            if (response.redirected) {
                window.location.href = response.url; 
            } else {
                const resultText = await response.text();
                alert("Terjadi kesalahan: " + resultText);
                console.log(resultText);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error(error);
            alert("Gagal terhubung ke server!");
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>

</body>
</html>