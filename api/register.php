<?php
// register.php
include __DIR__ . '/koneksi.php';
require_once __DIR__ . '/notif.php'; // Helper Notifikasi WA

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $no_wa    = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $password_raw = $_POST['password']; 
    $password = md5($password_raw);
    $level    = 'pembeli';

    // Cek Username
    $cek_user = mysqli_query($conn, "SELECT * FROM tbl_user WHERE username = '$username'");
    if(mysqli_num_rows($cek_user) > 0){
        echo "<script>alert('Username sudah digunakan!'); window.location='register.php';</script>";
        exit;
    }

    // Insert Data
    $query = "INSERT INTO tbl_user (username, password, level, no_wa) VALUES ('$username', '$password', '$level', '$no_wa')";
    
    if (mysqli_query($conn, $query)) {
        
        // --- KIRIM WA ---
        $pesan = "*Ahlan wa Sahlan, $username!* 👋\n\n";
        $pesan .= "Akun Antum di *Laundry Putri* berhasil dibuat.\n";
        $pesan .= "Username: $username\n";
        $pesan .= "Password: $password_raw\n\n";
        $pesan .= "Silakan login sekarang!";
        
        kirimPesanWA($no_wa, $pesan);
        // ----------------

        echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='login/login.php';</script>";
    } else {
        echo "<script>alert('Gagal Mendaftar!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Member - Laundry Putri</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f0f9ff; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 25px rgba(0, 119, 182, 0.1);
            border: 1px solid rgba(0, 180, 216, 0.1);
        }

        .brand-icon {
            width: 80px; height: 80px;
            background: #e0f2fe; color: #00b4d8;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%; margin: 0 auto 20px;
            font-size: 2.5rem;
        }

        .form-control {
            border-radius: 10px; padding: 12px 15px;
            border: 2px solid #f1f5f9; background-color: #f8fafc;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #00b4d8; background-color: #fff; box-shadow: none;
        }

        .btn-login {
            background: #0077b6; border: none; border-radius: 10px;
            padding: 12px; font-weight: 700; transition: 0.3s; letter-spacing: 1px;
        }

        .btn-login:hover {
            background: #023e8a; transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(2, 62, 138, 0.2);
        }

        .input-group-text {
            border: 2px solid #f1f5f9; background-color: #f8fafc;
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <div class="brand-icon">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <h3 class="fw-bold text-dark mb-1">Daftar Akun</h3>
        <p class="text-muted small mb-4">Gabung Laundry Putri</p>

        <form action="" method="POST">
            
            <div class="mb-3 text-start">
                <label class="form-label small fw-bold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="fa-solid fa-user text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Buat username" required>
                </div>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label small fw-bold text-secondary">WhatsApp</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="fa-brands fa-whatsapp text-muted"></i></span>
                    <input type="number" name="no_wa" class="form-control border-start-0 ps-0" placeholder="0812xxxx" required>
                </div>
                <div class="form-text text-end" style="font-size: 11px; margin-top: 2px;">*Pastikan nomor aktif</div>
            </div>

            <div class="mb-4 text-start">
                <label class="form-label small fw-bold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Buat password" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn btn-primary btn-login w-100 mb-3 text-white">
                DAFTAR SEKARANG
            </button>
            
            <div class="text-center small text-muted">
                Sudah punya akun? <a href="login/login.php" class="text-decoration-none fw-bold" style="color: #0077b6;">Login Disini</a>
            </div>
        </form>
    </div>

</body>
</html>