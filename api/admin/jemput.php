<?php
session_start();

// 1. Jalur koneksi yang benar dan stabil untuk Vercel
include __DIR__ . '/../koneksi.php';

// 2. Logika Satpam Gabungan (Session & Cookie)
$is_login = (isset($_SESSION['status']) && $_SESSION['status'] == "login") || 
             (isset($_COOKIE['user_status']) && $_COOKIE['user_status'] == "login");

$user_level = $_SESSION['level'] ?? $_COOKIE['user_level'] ?? '';

// Jika tidak ada Session dan tidak ada Cookie, arahkan ke login
if (!$is_login) {
    header("location:../login/login.php?pesan=belum_login");
    exit;
}

// Proteksi Level (Contoh untuk folder Admin)
if ($user_level != "admin") {
    header("location:../login/login.php?pesan=bukan_admin");
    exit;
}
// 1. LOGIKA PENCARIAN & TAMPIL DATA
$where = "WHERE status_laundry IN ('Menunggu Penjemputan', 'Sedang Menjemput')"; // Filter Wajib

if(isset($_GET['cari'])){
    $keyword = $_GET['cari'];
    // Gabungkan filter status + pencarian nama/ID
    $where .= " AND (nama_pelanggan LIKE '%$keyword%' OR order_id LIKE '%$keyword%')";
}

// Ambil data (Urutkan dari yang terbaru)
$query = "SELECT * FROM transaksi $where ORDER BY order_id DESC";
$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifest Penjemputan - Laundry Putri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0077b6;
            --sidebar-dark: #023e8a;
            --bg-light: #f8f9fa;
        }
        body { background-color: var(--bg-light); font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }

        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-dark); position: fixed; left: 0; top: 0; color: white; z-index: 1000; }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .nav-link { color: rgba(255,255,255,0.7); padding: 15px 25px; display: flex; align-items: center; gap: 12px; text-decoration: none; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid #00b4d8; }

        /* Content */
        .main-content { margin-left: 260px; padding: 40px; }
        .table-box { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        
        /* Badges */
        .badge-status { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; }
        .badge-pay { font-size: 10px; padding: 5px 10px; border-radius: 20px; font-weight: bold; }

        @media (max-width: 768px) {
            .sidebar { margin-left: -260px; }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

    <div class="sidebar shadow">
        <div class="sidebar-header">
            <h4 class="fw-bold mb-0 text-white"><i class="fa-solid fa-wand-sparkles me-2 text-info"></i>LAUNDRY PUTRI</h4>
            <small class="opacity-50">Petamburan Admin Case</small>
        </div>
        <div class="mt-4">
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            
            <a href="jemput.php" class="nav-link active"><i class="fa-solid fa-truck-pickup"></i> Jemputan</a>
            
            <a href="list.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Semua Pesanan</a> 
            <a href="tambah_kasir.php" class="nav-link"><i class="fa-solid fa-user-plus"></i> Tambah Kasir</a>
            <div class="px-4 mt-5"><small class="text-uppercase opacity-50" style="font-size: 10px; letter-spacing: 1px;">Pengaturan</small></div>  
            <a href="logout.php" class="nav-link text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark">Manifest Penjemputan</h2>
                <p class="text-muted small mb-0">Daftar cucian yang perlu diambil kurir.</p>
            </div>
            
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()">
                    <i class="fa-solid fa-rotate-right me-1"></i> Refresh
                </button>

                <form action="" method="GET">
                    <div class="input-group shadow-sm" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="cari" class="form-control border-start-0 ps-0" placeholder="Cari Nama / ID..." value="<?php if(isset($_GET['cari'])) echo $_GET['cari']; ?>">
                    </div>
                </form>
            </div>
        </div>

        <div class="table-box">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-3">ID Order</th>
                            <th>Pelanggan</th>
                            <th style="width: 30%;">Lokasi Jemput</th>
                            <th>Layanan</th>
                            <th>Berat</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($data) > 0) {
                            while($row = mysqli_fetch_assoc($data)) {
                                // Logic Warna Status
                                $st = $row['status_laundry'];
                                if($st == 'Sedang Menjemput') {
                                    $badgeColor = 'bg-warning text-dark';
                                    $iconSt = 'fa-motorcycle';
                                } else {
                                    $badgeColor = 'bg-secondary';
                                    $iconSt = 'fa-clock';
                                }
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary"><?php echo $row['order_id']; ?></td>
                            
                            <td>
                                <div class="fw-bold"><?php echo $row['nama_pelanggan']; ?></div>
                                <a href="https://wa.me/<?php echo $row['no_wa']; ?>" target="_blank" class="text-success small text-decoration-none">
                                    <i class="fab fa-whatsapp me-1"></i> Hubungi
                                </a>
                            </td>

                            <td>
                                <div class="small bg-light p-2 rounded border">
                                    <i class="fa-solid fa-map-pin text-danger me-1"></i>
                                    <?php 
                                        if (!empty($row['alamat_jemput'])) {
                                            echo $row['alamat_jemput'];
                                        } elseif (!empty($row['alamat'])) {
                                            echo $row['alamat'];
                                        } else {
                                            echo "<span class='text-muted fst-italic'>- Lokasi tidak terdeteksi -</span>";
                                        }
                                    ?>
                                </div>
                                <?php if(!empty($row['alamat_jemput']) || !empty($row['alamat'])): ?>
                                    <div class="mt-1">
                                        <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($row['alamat_jemput'] ?? $row['alamat']); ?>" target="_blank" class="btn btn-sm btn-link p-0 text-decoration-none" style="font-size: 11px;">
                                            <i class="fa-solid fa-map-location-arrow me-1"></i> Buka Maps
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="small fw-bold"><?php echo $row['jenis_layanan']; ?></div>
                                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 9px;"><?php echo $row['durasi_layanan']; ?> Hari</span>
                            </td>
                            
                            <td class="fw-bold"><?php echo $row['berat_qty']; ?> <small class="text-muted fw-normal">kg</small></td>
                            
                            <td>
                                <span class="badge-status <?php echo $badgeColor; ?>">
                                    <i class="fa-solid <?php echo $iconSt; ?> me-1"></i> <?php echo $st; ?>
                                </span>
                            </td>
                        </tr>

                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'><i class='fa-solid fa-clipboard-check fs-1 mb-3 d-block opacity-25'></i>Semua jemputan sudah selesai!</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>