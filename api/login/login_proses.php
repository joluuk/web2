<?php
session_start();
include __DIR__ . '/../koneksi.php'; 

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = md5($_POST['password']); 

    $query = mysqli_query($conn, "SELECT * FROM tbl_user WHERE username='$username' AND password='$password_input'");

    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        
        // --- TETAP SET SESSION (Untuk Localhost) ---
        $_SESSION['username']     = $row['username'];
        $_SESSION['level']        = $row['level'];
        $_SESSION['nama_lengkap'] = $row['nama_lengkap']; 
        $_SESSION['status']       = "login";

        // --- SET COOKIE (Solusi Ampuh untuk Vercel) ---
        // Simpan username dan level agar bisa dibaca di halaman lain
        setcookie("user_name", $row['username'], time() + 3600, "/"); 
        setcookie("user_level", $row['level'], time() + 3600, "/"); 
        setcookie("user_status", "login", time() + 3600, "/"); 
        
        if ($row['level'] == "admin") {
            header("location:../admin/dashboard.php");
        } else if ($row['level'] == "kasir") {
            header("location:../kasir/kasir.php");
        } else {
            header("location:../pembeli.php");
        }
        exit();
    } else {
        header("location:login.php?pesan=gagal");
        exit();
    }
} else {
    header("location:login.php");
    exit();
}
?>