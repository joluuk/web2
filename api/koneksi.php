<?php
// Masukkan data dari Aiven (yang tadi dipakai di HeidiSQL)
$host = "mysql-laundry-frozeaxe123-32e8.e.aivencloud.com"; // Hostname
$user = "avnadmin";                           // Username
$pass = "AVNS_QoBhiPht13GhMt1iZa3";              // Password
$db   = "defaultdb";                          // Database Name
$port = "23086";                              // Port (PENTING! Cek di Aiven/HeidiSQL)

// Koneksi Khusus Cloud (Wajib ada parameter Port di urutan ke-5)
$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>