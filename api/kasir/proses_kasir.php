<?php
session_start();
ob_start(); // Wajib untuk Vercel agar header location jalan lancar
include __DIR__ . '/../koneksi.php';

if(isset($_POST['simpan_transaksi'])){
    
    // 1. Ambil Data dari Form Kasir
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $wa       = mysqli_real_escape_string($conn, $_POST['wa']);
    $layanan  = $_POST['layanan']; // Lipat, Gosok, Karpet
    $durasi   = $_POST['durasi'];  // 3, 2, 1
    $berat    = $_POST['berat'];
    $opsi     = $_POST['bayar'];   // 'nanti' atau 'sekarang'

    // Buat Order ID Unik
    $order_id = "ORD-" . date('ymd') . "-" . rand(100, 999);

    // --- 2. LOGIKA HARGA (TETAP SESUAI PERMINTAAN ANTUM: 6, 4, 2) ---
    $harga_satuan = 0;

    if ($layanan == 'Lipat') {
        if ($durasi == '1') { $harga_satuan = 6; }      // Kilat
        elseif ($durasi == '2') { $harga_satuan = 4; }  // Express
        else { $harga_satuan = 2; }                     // Reguler
    } 
    elseif ($layanan == 'Gosok') {
        if ($durasi == '1') { $harga_satuan = 3; }      // Kilat
        elseif ($durasi == '2') { $harga_satuan = 2; }  // Express
        else { $harga_satuan = 1; }                     // Reguler
    } 
    elseif ($layanan == 'Karpet') {
        $harga_satuan = 10; 
        $durasi = 3; 
    }    

    $total_harga = $harga_satuan * $berat;

    // 3. Status Awal
    $status_bayar = 'pending';
    $status_laundry = 'Proses'; 
    
    // --- PERBAIKAN PENTING AGAR TIDAK ERROR DATABASE ---
    // Kita isi alamat otomatis dengan tanda strip (-) atau keterangan
    // karena kolom alamat_jemput di database Antum wajib diisi (NOT NULL)
    $alamat_default = "- (Pelanggan Datang ke Outlet)";

    // 4. Simpan ke Database (Tambahkan kolom alamat_jemput)
    $query = "INSERT INTO transaksi 
              (order_id, nama_pelanggan, no_wa, alamat_jemput, jenis_layanan, durasi_layanan, berat_qty, total_harga, status_laundry, status_bayar, tgl_transaksi)
              VALUES 
              ('$order_id', '$nama', '$wa', '$alamat_default', '$layanan', '$durasi', '$berat', '$total_harga', '$status_laundry', '$status_bayar', NOW())";

    if(mysqli_query($conn, $query)){
        
        // --- 5. LOGIKA PENGALIHAN ---
        if ($opsi == 'sekarang') {
            // SKENARIO: Bayar Sekarang -> Lempar ke Midtrans
            header("location:bayar.php?order_id=$order_id");
            exit;
        } 
        else {
            // SKENARIO: Bayar Nanti -> Balik ke Kasir
            header("location:../../kasir/kasir.php?pesan=simpan_pending");
            exit;
        }

    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}
?>