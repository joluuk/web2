<?php
session_start();
ob_start(); 

// PENTING: Karena file ini sekarang ada di folder 'kasir',
// dan koneksi ada di folder 'api', maka kita harus mundur satu langkah lalu masuk ke api.
// Pastikan path ini benar sesuai lokasi koneksi.php antum.
if (file_exists(__DIR__ . '/../api/koneksi.php')) {
    include __DIR__ . '/../api/koneksi.php';
} else {
    // Jaga-jaga kalau koneksi.php antum ada di folder root (luar)
    include __DIR__ . '/../koneksi.php';
}

if(isset($_POST['simpan_transaksi'])){
    
    // 1. Ambil Data
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $wa       = mysqli_real_escape_string($conn, $_POST['wa']);
    $layanan  = $_POST['layanan']; 
    $durasi   = $_POST['durasi']; 
    $berat    = $_POST['berat'];
    $opsi     = $_POST['bayar']; 

    $order_id = "ORD-" . date('ymd') . "-" . rand(100, 999);

    // 2. Logika Harga (Tetap 6, 4, 2)
    $harga_satuan = 0;
    if ($layanan == 'Lipat') {
        if ($durasi == '1') { $harga_satuan = 6; }      
        elseif ($durasi == '2') { $harga_satuan = 4; }  
        else { $harga_satuan = 2; }                     
    } 
    elseif ($layanan == 'Gosok') {
        if ($durasi == '1') { $harga_satuan = 3; }      
        elseif ($durasi == '2') { $harga_satuan = 2; }  
        else { $harga_satuan = 1; }                     
    } 
    elseif ($layanan == 'Karpet') {
        $harga_satuan = 10; 
        $durasi = 3; 
    }    

    $total_harga = $harga_satuan * $berat;
    $status_bayar = 'pending';
    $status_laundry = 'Proses'; 
    $alamat_default = "- (Pelanggan Datang ke Outlet)";

    // 3. Simpan Database
    $query = "INSERT INTO transaksi 
              (order_id, nama_pelanggan, no_wa, alamat_jemput, jenis_layanan, durasi_layanan, berat_qty, total_harga, status_laundry, status_bayar, tgl_transaksi)
              VALUES 
              ('$order_id', '$nama', '$wa', '$alamat_default', '$layanan', '$durasi', '$berat', '$total_harga', '$status_laundry', '$status_bayar', NOW())";

    if(mysqli_query($conn, $query)){
        
        // 4. Redirect (Jauh lebih simpel karena satu folder)
        if ($opsi == 'sekarang') {
            // Langsung panggil bayar.php (karena tetanggaan)
            header("location:bayar.php?order_id=$order_id");
            exit;
        } 
        else {
            // Langsung panggil kasir.php (karena tetanggaan)
            header("location:kasir.php?pesan=simpan_pending");
            exit;
        }

    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}
?>