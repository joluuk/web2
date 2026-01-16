<?php
// 1. Sertakan koneksi (Gunakan __DIR__ agar aman di Vercel)
include __DIR__ . '/../koneksi.php';

// 2. Cek apakah ada Order ID yang dikirim
if(isset($_GET['order_id'])){
    
    // Amankan input dari karakter aneh
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

    // 3. Update status menjadi LUNAS
    $query = "UPDATE transaksi SET status_bayar = 'lunas' WHERE order_id = '$order_id'";

    if(mysqli_query($conn, $query)){
        // Kirim respon sukses ke browser
        http_response_code(200);
        echo "Status berhasil diupdate jadi Lunas.";
    } else {
        // Kirim respon gagal
        http_response_code(500);
        echo "Gagal update database: " . mysqli_error($conn);
    }

} else {
    echo "Tidak ada Order ID.";
}
?>