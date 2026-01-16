<?php
session_start();
require_once __DIR__ . '/../koneksi.php'; // Pastikan path koneksi benar

if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // Enkripsi MD5 (sesuai standar laprak antum)
    $level    = 'kasir';

    // 1. Cek dulu apakah username sudah ada?
    $cek = mysqli_query($conn, "SELECT * FROM tbl_user WHERE username = '$username'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Gagal! Username sudah digunakan.');
                window.location.href='tambah_kasir.php';
              </script>";
        exit;
    }

    // 2. Kalau belum ada, masukkan data
    $query = "INSERT INTO tbl_user (username, password, level) VALUES ('$username', '$password', '$level')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>
                alert('Berhasil menambah kasir baru!');
                window.location.href='tambah_kasir.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>