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

// 2. LOGIKA: Update Status Laundry (Hanya 2 Status)
if(isset($_POST['update_status'])){
    $id = $_POST['order_id'];
    $status_baru = $_POST['status_laundry'];
    
    $query = "UPDATE transaksi SET status_laundry='$status_baru' WHERE order_id='$id'";
    if(mysqli_query($conn, $query)){
        echo "<script>alert('Status berhasil diperbarui!'); window.location='dashboard.php';</script>";
    }
}

// 3. LOGIKA: Hapus Data
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM transaksi WHERE order_id='$id'");
    echo "<script>alert('Data berhasil dihapus!'); window.location='dashboard.php';</script>";
}

// 4. HITUNG STATISTIK (Sederhana)
// Hitung yang sedang "Dijemput / Proses" (Semua yang belum selesai)
$q_proses = mysqli_query($conn, "SELECT * FROM transaksi WHERE status_laundry != 'Selesai'");
$jml_proses = mysqli_num_rows($q_proses);

// Hitung yang "Selesai"
$q_selesai = mysqli_query($conn, "SELECT * FROM transaksi WHERE status_laundry = 'Selesai'");
$jml_selesai = mysqli_num_rows($q_selesai);

// 5. AMBIL SEMUA DATA
$data_transaksi = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY tgl_transaksi DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Laundry Putri - Petamburan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0077b6;
            --accent-blue: #00b4d8;
            --sidebar-dark: #023e8a;
            --bg-light: #f8f9fa;
        }

        body { 
            background-color: var(--bg-light); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            overflow-x: hidden; 
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px; height: 100vh; background: var(--sidebar-dark);
            position: fixed; left: 0; top: 0; color: white;
            transition: 0.3s; z-index: 1000;
        }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .nav-link {
            color: rgba(255,255,255,0.7); padding: 15px 25px;
            display: flex; align-items: center; gap: 12px; transition: 0.3s; font-weight: 500;
        }
        .nav-link:hover, .nav-link.active {
            color: white; background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent-blue);
        }
        .nav-link i { width: 20px; text-align: center; }

        /* Content Styling */
        .main-content { margin-left: 260px; padding: 40px; min-height: 100vh; }
        
        /* Card Stats */
        .card-stat {
            border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            transition: 0.3s; background: white;
        }
        .card-stat:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }

        /* Table Styling */
        .table-box {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        }
        .table thead th {
            font-size: 12px; text-transform: uppercase; letter-spacing: 1px;
            color: #888; border-bottom: 2px solid #eee;
        }
        .badge-custom { padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; }

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
            <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="jemput.php" class="nav-link"><i class="fa-solid fa-truck-pickup"></i> Jemputan</a>
            <a href="list.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Semua Pesanan</a>
            <a href="tambah_kasir.php" class="nav-link"><i class="fa-solid fa-user-plus"></i> Tambah Kasir</a>
            
            <div class="px-4 mt-5">
                <small class="text-uppercase opacity-50" style="font-size: 10px; letter-spacing: 1px;">Pengaturan</small>
            </div>
            <a href="logout.php" class="nav-link text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </div>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold text-dark mb-1">Assalamu'alaikum, <?php echo $_SESSION['nama_lengkap']; ?></h2>
                <p class="text-muted mb-0 small">Manajemen operasional Laundry Putri.</p>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0 text-primary"><?php echo date('H:i'); ?></h5>
                <small class="text-muted fw-medium"><?php echo date('d M Y'); ?></small>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card card-stat p-4 border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-bold small text-uppercase">Dijemput / Proses</p>
                            <h2 class="fw-bold mb-0"><?php echo str_pad($jml_proses, 2, '0', STR_PAD_LEFT); ?></h2>
                        </div>
                        <div class="fs-1 text-warning opacity-25"><i class="fa-solid fa-motorcycle"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card card-stat p-4 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 fw-bold small text-uppercase">Pesanan Selesai</p>
                             <h2 class="fw-bold mb-0"><?php echo str_pad($jml_selesai, 2, '0', STR_PAD_LEFT); ?></h2>
                        </div>
                        <div class="fs-1 text-success opacity-25"><i class="fa-solid fa-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-box shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-list-ul me-2 text-primary"></i>List Semua Pesanan</h5>
                <div class="input-group w-25 shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" class="form-control form-control-sm border-start-0 ps-0" placeholder="Cari ID/Nama...">
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">#ID</th>
                            <th>Pelanggan</th>
                            <th>Layanan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($data_transaksi) > 0){
                            while($row = mysqli_fetch_assoc($data_transaksi)) { 
                                // LOGIC WARNA BADGE (HANYA 2 JENIS)
                                $st = $row['status_laundry'];
                                // Jika Selesai = Hijau, Selain itu = Kuning (Proses/Jemput)
                                $badgeClass = ($st == 'Selesai') ? 'bg-success' : 'bg-warning text-dark';
                                $statusLabel = ($st == 'Selesai') ? 'SELESAI' : 'DIJEMPUT / PROSES';
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold text-primary"><?php echo $row['order_id']; ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo $row['nama_pelanggan']; ?></div>
                                <small class="text-muted">
                                    <i class="fa-brands fa-whatsapp me-1 text-success fw-bold"></i>
                                    <a href="https://wa.me/<?php echo $row['no_wa']; ?>" target="_blank" class="text-decoration-none text-muted"><?php echo $row['no_wa']; ?></a>
                                </small>
                            </td>
                            <td>
                                <div class="small fw-bold"><?php echo $row['jenis_layanan']; ?></div>
                                <span class="badge bg-light text-secondary border fw-normal" style="font-size: 9px;">
                                    <?php echo $row['berat_qty']; ?> Kg
                                </span>
                            </td>
                            <td class="fw-bold text-dark">Rp <?php echo number_format($row['total_harga'],0,',','.'); ?></td>
                            <td>
                                <span class="badge-custom <?php echo $badgeClass; ?> bg-opacity-75">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group gap-2">
                                    <button class="btn btn-sm btn-outline-primary border-0 bg-light rounded-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEdit<?php echo $row['order_id']; ?>" 
                                            title="Update Status">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    
                                    <a href="dashboard.php?hapus=<?php echo $row['order_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger border-0 bg-light rounded-2" 
                                       onclick="return confirm('Yakin hapus data ini?')" 
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
                                        <h6 class="modal-title fw-bold">Update Status #<?php echo $row['order_id']; ?></h6>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                            <div class="mb-3">
                                                <label class="form-label small text-muted">Status Saat Ini</label>
                                                <input type="text" class="form-control" value="<?php echo $row['status_laundry']; ?>" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">Ubah Status Menjadi</label>
                                                <select name="status_laundry" class="form-select">
                                                    <option value="Menunggu Penjemputan" <?php if($st != 'Selesai') echo 'selected'; ?>>Dijemput / Proses</option>
                                                    <option value="Selesai" <?php if($st == 'Selesai') echo 'selected'; ?>>Selesai</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-5 text-muted'>Belum ada pesanan masuk.</td></tr>";
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