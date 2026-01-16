<?php
session_start();
require_once __DIR__ . '/koneksi.php';
// require_once 'helper_notif.php'; <-- Hapus atau biarkan saja, tidak terpakai di sini

// Panggil Library Midtrans
require_once __DIR__ . '/midtrans-php/Midtrans.php';

if (isset($_POST['kirim_pesanan'])) {
        
    // --- 1. KONFIGURASI MIDTRANS (PRODUCTION) ---
    \Midtrans\Config::$serverKey = 'Mid-server-JeV5Qo3SBTLVWxZmYxQNIOM_'; 
    \Midtrans\Config::$isProduction = true; 
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;  

    // --- 2. AMBIL DATA DARI FORM ---
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_pelanggan']);
    $wa      = mysqli_real_escape_string($conn, $_POST['no_wa']);
    $alamat  = mysqli_real_escape_string($conn, $_POST['alamat_jemput']);
    $layanan = $_POST['jenis_layanan']; 
    $durasi  = $_POST['durasi_layanan']; 
    $berat   = (float) $_POST['berat_qty'];

    // Buat Order ID Unik
    $order_id = "LND-" . date('ymd') . "-" . rand(100, 999);

    // --- 3. LOGIKA HARGA (Sinkron dengan Javascript Frontend) ---
    $harga_satuan = 0;

    if ($layanan == 'Lipat') {
        if ($durasi == '1') { $harga_satuan = 3; }      // Kilat
        elseif ($durasi == '2') { $harga_satuan = 2; }  // Express
        else { $harga_satuan = 1; }                     // Reguler
    } 
    elseif ($layanan == 'Gosok') {
        if ($durasi == '1') { $harga_satuan = 6; }      // Kilat 
        elseif ($durasi == '2') { $harga_satuan = 4; }  // Express
        else { $harga_satuan = 2; }                     // Reguler
    } 
    elseif ($layanan == 'Karpet') {
        $harga_satuan = 10;
        $durasi = 3; 
    }

    $total_harga = $harga_satuan * $berat;

    // --- 4. SIAPKAN DATA MIDTRANS ---
    $transaction_details = array(
        'order_id' => $order_id,
        'gross_amount' => (int) $total_harga, 
    );

    $customer_details = array(
        'first_name'    => $nama,
        'phone'         => $wa,
        'billing_address' => array('address' => $alamat),
    );

    $item_details = array(
        array(
            'id' => 'SRV-01',
            'price' => (int) $harga_satuan,
            'quantity' => $berat,
            'name' => "$layanan ($durasi Hari)"
        )
    );

    $params = array(
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details,
        'item_details' => $item_details,
    );

    try {
        // Minta Token Midtrans
        $snapToken = \Midtrans\Snap::getSnapToken($params);

        // --- 5. SIMPAN KE DATABASE ---
        $query = "INSERT INTO transaksi 
                  (order_id, nama_pelanggan, no_wa, alamat_jemput, jenis_layanan, durasi_layanan, berat_qty, total_harga, status_laundry, status_bayar, snap_token)
                  VALUES 
                  ('$order_id', '$nama', '$wa', '$alamat', '$layanan', '$durasi', '$berat', '$total_harga', 'Menunggu Penjemputan', 'pending', '$snapToken')";

        if (mysqli_query($conn, $query)) {
            
            // --- BAGIAN NOTIFIKASI DIHAPUS ---
            // Supaya tidak ada WA masuk sebelum bayar
            // ---------------------------------

            // Redirect ke halaman pembayaran
            header("Location: nota.php?id=$order_id");
            exit();
        } else {
            echo "Database Error: " . mysqli_error($conn);
        }

    } catch (Exception $e) {
        echo "Midtrans Error: " . $e->getMessage();
    }
}
?>