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