<?php
session_start();

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
// --- PROSES TAMBAH KASIR ---
if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); 
    $no_wa    = mysqli_real_escape_string($conn, $_POST['no_wa']); 
    $level    = 'kasir';

    // Cek Username
    $cek = mysqli_query($conn, "SELECT * FROM tbl_user WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Gagal! Username sudah digunakan.'); window.location.href='tambah_kasir.php';</script>";
        exit;
    }

    // Simpan Data
    $query = "INSERT INTO tbl_user (username, password, level, no_wa) VALUES ('$username', '$password', '$level', '$no_wa')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Alhamdulillah! Kasir baru berhasil ditambahkan.'); window.location.href='tambah_kasir.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kasir - Laundry Putri</title>
    
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
        .main-content { margin-left: 260px; padding: 40px; display: flex; align-items: center; justify-content: center; min-height: 90vh; }
        
        /* Card Form */
        .form-box { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); width: 100%; max-width: 500px; }

        .form-control { padding: 12px; border-radius: 8px; border: 1px solid #e0e0e0; background-color: #fcfcfc; }
        .form-control:focus { border-color: var(--primary-blue); background-color: #fff; box-shadow: none; }

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
            <a href="list.php" class="nav-link"><i class="fa-solid fa-list-check"></i> Semua Pesanan</a> 
            <a href="tambah_kasir.php" class="nav-link active"><i class="fa-solid fa-user-plus"></i> Tambah Kasir</a>
            <div class="px-4 mt-5"><small class="text-uppercase opacity-50" style="font-size: 10px; letter-spacing: 1px;">Pengaturan</small></div>  
            <a href="logout.php" class="nav-link text-danger mt-4"><i class="fa-solid fa-power-off"></i> Keluar</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="form-box">
            <h4 class="fw-bold text-dark mb-1 text-center">Tambah Akun Kasir</h4>
            <p class="text-muted text-center small mb-4">Buat akun login baru untuk staff laundry</p>
            
            <form action="" method="POST">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" class="form-control border-start-0" placeholder="Contoh: kasir01" required autocomplete="off">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">No. WhatsApp</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-brands fa-whatsapp"></i></span>
                        <input type="number" name="no_wa" class="form-control border-start-0" placeholder="08xxxxxxxx" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="Buat password..." required>
                    </div>
                </div>

                <button type="submit" name="tambah" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> SIMPAN KASIR
                </button>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

```