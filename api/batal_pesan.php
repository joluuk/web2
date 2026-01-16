<?php
require_once __DIR__ . '/koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Opsi A: HAPUS permanen dari database (Biar bersih)
    $query = "DELETE FROM transaksi WHERE order_id = '$id'";
    
    // Opsi B: Ubah status jadi 'Dibatalkan' (Kalau mau disimpan buat riwayat)
    // $query = "UPDATE transaksi SET status_bayar='Batal', status_laundry='Dibatalkan' WHERE order_id='$id'";

    mysqli_query($conn, $query);
    
    // Kembalikan ke halaman utama
    header("Location: pembeli.php");
}
?>