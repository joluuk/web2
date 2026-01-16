<?php
session_start();
session_destroy(); // Menghapus semua session di server

// 1. Hapus semua cookie login (Nama harus sama persis dengan login_proses.php)
setcookie("user_name", "", time() - 3600, "/");   // Tadi di login_proses namanya user_name
setcookie("user_level", "", time() - 3600, "/");
setcookie("user_status", "", time() - 3600, "/");

// 2. Keluar satu folder (../) untuk menemukan index.php (halaman login)
header("location:../index.php?pesan=logout");
exit;
?>