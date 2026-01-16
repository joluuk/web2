<?php
session_start();
include __DIR__ . '/../koneksi.php';

if(isset($_POST['simpan_transaksi'])){
    
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $wa       = mysqli_real_escape_string($conn, $_POST['wa']);
    $layanan  = $_POST['layanan']; // Values: Lipat, Gosok, Karpet
    $durasi   = $_POST['durasi'];  // Values: 3, 2, 1
    $berat    = $_POST['berat'];
    $st_bayar = $_POST['bayar']; 

    $order_id = "ORD-" . date('ymd') . "-" . rand(100, 999);

    // --- LOGIKA HARGA (SINKRON DENGAN PEMBELI) ---
    $harga_satuan = 0;

    if ($layanan == 'Lipat') {
        if ($durasi == '1') { $harga_satuan = 6000; }      // Kilat
        elseif ($durasi == '2') { $harga_satuan = 5000; }  // Express
        else { $harga_satuan = 4000; }                     // Reguler
    } 
    elseif ($layanan == 'Gosok') {
        if ($durasi == '1') { $harga_satuan = 10000; }     // Kilat
        elseif ($durasi == '2') { $harga_satuan = 8000; }  // Express
        else { $harga_satuan = 6000; }                     // Reguler
    } 
    elseif ($layanan == 'Karpet') {
        $harga_satuan = 20000; 
        $durasi = 3; // Karpet selalu reguler
    }

    $total_harga = $harga_satuan * $berat;
    // ----------------------------------------------

    // Simpan ke Database
    $query = "INSERT INTO transaksi 
              (order_id, nama_pelanggan, no_wa, jenis_layanan, durasi_layanan, berat_qty, total_harga, status_laundry, status_bayar)
              VALUES 
              ('$order_id', '$nama', '$wa', '$layanan', '$durasi', '$berat', '$total_harga', 'Menunggu Penjemputan', '$st_bayar')";

    if(mysqli_query($conn, $query)){
        echo "<script>alert('Transaksi Berhasil Disimpan!'); window.location='kasir/kasir.php';</script>";
    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}
?>