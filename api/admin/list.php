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

// 1. LOGIKA UPDATE (Status Laundry & Pembayaran)
if(isset($_POST['update_order'])){
    $id = $_POST['order_id'];
    $st_laundry = $_POST['status_laundry'];
    $st_bayar = $_POST['status_bayar'];
    
    $query = "UPDATE transaksi SET status_laundry='$st_laundry', status_bayar='$st_bayar' WHERE order_id='$id'";
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Data pesanan berhasil diperbarui!'); window.location='list.php';</script>";
    }
}

// 2. LOGIKA HAPUS
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM transaksi WHERE order_id='$id'");
    echo "<script>alert('Data berhasil dihapus permanen!'); window.location='list.php';</script>";
}

// 3. LOGIKA PENCARIAN & TAMPIL DATA
$where = "";
if(isset($_GET['cari'])){
    $keyword = $_GET['cari'];
    $where = "WHERE nama_pelanggan LIKE '%$keyword%' OR order_id LIKE '%$keyword%'";
}

// Ambil data (Urutkan dari yang terbaru)
$query = "SELECT * FROM transaksi $where ORDER BY tgl_transaksi DESC";
$data = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Pesanan - Laundry Putri</title>
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
            <a href="jemput.php" class="nav-link"><i class="fa-solid fa-truck-pickup"></i> Jemputan</a>
            <a href="list.php" class="nav-link active"><i class="fa-solid fa-list-check"></i> Semua Pesanan</a>
            <a href="tambah_kasir.php" class="nav-link"><i class="fa-solid fa-user-plus"></i> Tambah Kasir</a>
            <div class="px-4 mt-5"><small class="text-uppercase opacity-50" style="font-size: 10px; letter-spacing: 1px;">Pengaturan</small></div>  
            <a href="logout.php" class="nav-link text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Daftar Semua Pesanan</h2>
            
            <form action="" method="GET" class="w-25">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="cari" class="form-control border-start-0 ps-0" placeholder="Cari Nama / ID..." value="<?php if(isset($_GET['cari'])) echo $_GET['cari']; ?>">
                </div>
            </form>
        </div>

        <div class="table-box">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light text-muted">
                        <tr>
                            <th class="ps-3">ID</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Berat</th>
                            <th>Biaya</th>
                            <th>Pembayaran</th> 
                            <th>Status Laundry</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($data) > 0) {
                            while($row = mysqli_fetch_assoc($data)) {
                                // 1. Logic Warna Status Laundry (SEDERHANA: Hijau atau Kuning)
                                $st = $row['status_laundry'];
                                $badgeColor = ($st == 'Selesai') ? 'bg-success' : 'bg-warning text-dark';
                                $statusText = ($st == 'Selesai') ? 'SELESAI' : 'DIJEMPUT / PROSES';

                                // 2. Logic Warna Pembayaran
                                $bayar = $row['status_bayar']; 
                                $payBadge = ($bayar == 'lunas') ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
                                $payIcon = ($bayar == 'lunas') ? 'fa-circle-check' : 'fa-circle-xmark';
                                $payText = ($bayar == 'lunas') ? 'LUNAS' : 'BELUM BAYAR';
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold"><?php echo $row['order_id']; ?></td>
                            <td>
                                <div class="fw-bold"><?php echo $row['nama_pelanggan']; ?></div>
                                <small class="text-muted"><i class="fab fa-whatsapp me-1"></i><?php echo $row['no_wa']; ?></small>
                            </td>
                            <td>
                                <div class="small fw-bold"><?php echo $row['jenis_layanan']; ?></div>
                                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 9px;"><?php echo $row['durasi_layanan']; ?> Hari</span>
                            </td>
                            <td class="fw-bold"><?php echo $row['berat_qty']; ?> <small class="text-muted fw-normal">kg</small></td>
                            <td class="fw-bold text-primary">Rp <?php echo number_format($row['total_harga'],0,',','.'); ?></td>
                            
                            <td>
                                <span class="badge-pay <?php echo $payBadge; ?>">
                                    <i class="fa-solid <?php echo $payIcon; ?> me-1"></i> <?php echo $payText; ?>
                                </span>
                            </td>

                            <td><span class="badge-status <?php echo $badgeColor; ?>"><?php echo $statusText; ?></span></td>
                            
                            <td class="text-center">
                                <div class="btn-group gap-2">
                                    <button class="btn btn-sm btn-outline-primary border-0 bg-light rounded-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit<?php echo $row['order_id']; ?>" 
                                            title="Edit Data">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <a href="list.php?hapus=<?php echo $row['order_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0 bg-light rounded-2" 
                                       onclick="return confirm('Hapus permanen data ini?')" 
                                       title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="modalEdit<?php echo $row['order_id']; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header bg-primary text-white">
                                        <h6 class="modal-title fw-bold">Edit Pesanan #<?php echo $row['order_id']; ?></h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Status Laundry</label>
                                                <select name="status_laundry" class="form-select">
                                                    <option value="Menunggu Penjemputan" <?php if($st!='Selesai') echo 'selected'; ?>>Dijemput / Proses</option>
                                                    <option value="Selesai" <?php if($st=='Selesai') echo 'selected'; ?>>Selesai</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Status Pembayaran</label>
                                                <select name="status_bayar" class="form-select">
                                                    <option value="pending" class="text-danger" <?php if($bayar=='pending') echo 'selected'; ?>>BELUM BAYAR</option>
                                                    <option value="lunas" class="text-success" <?php if($bayar=='lunas') echo 'selected'; ?>>LUNAS</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_order" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-5 text-muted'>Tidak ada data ditemukan.</td></tr>";
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