<?php
session_start();
include __DIR__ . '/../koneksi.php'; 

// Cek Login (Session atau Cookie)
$is_login = (isset($_SESSION['status']) && $_SESSION['status'] == "login") || 
             (isset($_COOKIE['user_status']) && $_COOKIE['user_status'] == "login");

$user_level = $_SESSION['level'] ?? $_COOKIE['user_level'] ?? '';

if (!$is_login) {
    header("location:../login/login.php?pesan=belum_login");
    exit;
}

// PERBAIKAN DI SINI: Pastikan levelnya adalah 'kasir'
if ($user_level != "kasir") {
    // Jika Admin mencoba masuk halaman Kasir, lempar ke login dengan pesan berbeda
    header("location:../login/login.php?pesan=bukan_kasir");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - Laundry Putri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background-color: #f0f9ff; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: #0077b6; }
        /* Style Card biasa, tanpa garis putus-putus aneh */
        .card { border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-dark mb-4 shadow-sm">
    <div class="container">
        <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-cash-register me-2"></i>KASIR PANEL</span>
        <div class="d-flex align-items-center text-white">
            
            <a href="../admin/logout.php" class="btn btn-sm btn-danger rounded-pill px-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row g-4">
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-light py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-pen-to-square me-2"></i>Input Pesanan</h6>
                </div>
                <div class="card-body">
                    <form action="proses_kasir.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Pelanggan</label>
                            <input type="text" name="nama" class="form-control" placeholder="Nama Pelanggan" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">No WhatsApp</label>
                            <input type="number" name="wa" class="form-control" placeholder="08xxx" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Jenis Layanan</label>
                            <select name="layanan" id="layanan" class="form-select">
                                <option value="Lipat">Cuci Lipat (4rb - 6rb)</option>
                                <option value="Gosok">Cuci Gosok (6rb - 10rb)</option>
                                <option value="Karpet">Laundry Karpet (20rb)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Durasi Layanan</label>
                            <select name="durasi" id="durasi" class="form-select">
                                <option value="3">Reguler (3 Hari)</option>
                                <option value="2">Express (2 Hari)</option>
                                <option value="1">Kilat (1 Hari)</option>
                            </select>
                            <small class="text-danger d-none" id="alertKarpet" style="font-size: 11px;">*Karpet hanya tersedia Reguler</small>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Berat (Kg/m²)</label>
                                <input type="number" name="berat" id="berat" step="0.1" class="form-control" value="1" min="0.1" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small fw-bold">Pembayaran</label>
                                <select name="bayar" class="form-select">
                                    <option value="lunas">Lunas</option>
                                    <option value="pending">Belum Bayar</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-primary">Total Harus Dibayar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white fw-bold">Rp</span>
                                <input type="text" id="totalDisplay" class="form-control fw-bold text-dark bg-white" readonly value="4.000">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="simpan_transaksi" class="btn btn-success fw-bold">
                                <i class="fa-solid fa-save me-2"></i>SIMPAN PESANAN
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-light py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-list me-2"></i>Transaksi Hari Ini</h6>
                    <small class="text-muted"><?php echo date('d M Y'); ?></small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">ID Order</th>
                                    <th>Pelanggan</th>
                                    <th>Layanan</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY tgl_transaksi DESC LIMIT 10");
                                
                                if(mysqli_num_rows($query) > 0){
                                    while($row = mysqli_fetch_assoc($query)){
                                        $st = $row['status_laundry'];
                                        $badge = ($st == 'Selesai') ? 'bg-success' : 'bg-warning text-dark';
                                        $label = ($st == 'Selesai') ? 'SELESAI' : 'PROSES';
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-primary"><?php echo $row['order_id']; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo $row['nama_pelanggan']; ?></div>
                                        <small class="text-muted"><?php echo $row['no_wa']; ?></small>
                                    </td>
                                    <td>
                                        <small class="fw-bold"><?php echo $row['jenis_layanan']; ?></small><br>
                                        <small class="text-muted"><?php echo $row['durasi_layanan']; ?> Hari • <?php echo $row['berat_qty']; ?> kg</small>
                                    </td>
                                    <td class="fw-bold">Rp <?php echo number_format($row['total_harga']); ?></td>
                                    <td><span class="badge <?php echo $badge; ?>"><?php echo $label; ?></span></td>
                                </tr>
                                <?php 
                                    } 
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>Belum ada transaksi hari ini.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Ambil elemen
    const inputLayanan = document.getElementById('layanan');
    const inputDurasi = document.getElementById('durasi');
    const inputBerat = document.getElementById('berat');
    const displayTotal = document.getElementById('totalDisplay');
    const alertKarpet = document.getElementById('alertKarpet');

    // Ambil Option Durasi untuk di-disable nanti
    const optExpress = inputDurasi.querySelector('option[value="2"]'); 
    const optKilat = inputDurasi.querySelector('option[value="1"]');  

    function hitungRealtime() {
        let layanan = inputLayanan.value;
        let berat = parseFloat(inputBerat.value) || 0;
        let harga = 0;

        if (layanan === 'Karpet') {
            // Matikan opsi 1 dan 2 hari
            optExpress.disabled = true;
            optKilat.disabled = true;
            
            // Paksa pilih Reguler (3 hari)
            inputDurasi.value = '3';
            
            // Munculkan pesan peringatan kecil
            alertKarpet.classList.remove('d-none');
        } else {
            // Hidupkan kembali opsi 1 dan 2 hari
            optExpress.disabled = false;
            optKilat.disabled = false;
            
            // Sembunyikan pesan peringatan
            alertKarpet.classList.add('d-none');
        }

        let durasi = inputDurasi.value; 

        // --- LOGIKA HARGA ---
        if (layanan === 'Lipat') {
            if (durasi === '1') harga = 6000;
            else if (durasi === '2') harga = 5000;
            else harga = 4000;
        } 
        else if (layanan === 'Gosok') {
            if (durasi === '1') harga = 10000;
            else if (durasi === '2') harga = 8000;
            else harga = 6000;
        } 
        else if (layanan === 'Karpet') {
            harga = 20000; // Harga Karpet tetap
        }

        let total = harga * berat;

        // Format ke Ribuan (contoh: 4.000)
        displayTotal.value = total.toLocaleString('id-ID');
    }

    // Pasang Event Listener
    inputLayanan.addEventListener('change', hitungRealtime);
    inputDurasi.addEventListener('change', hitungRealtime);
    inputBerat.addEventListener('input', hitungRealtime);

    // Jalankan sekali saat loading
    hitungRealtime();
</script>

</body>
</html>