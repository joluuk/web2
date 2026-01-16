<?php
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/notif.php';

// --- SETTING NOMOR ADMIN DISINI ---
$nomor_admin = "081292283615"; // <--- GANTI JADI NOMOR WA ANTUM/KURIR
// ----------------------------------

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // 1. UPDATE STATUS DI DATABASE
    $query = "UPDATE transaksi 
              SET status_bayar = 'lunas', 
                  status_laundry = 'Sedang Menjemput' 
              WHERE order_id = '$order_id'";
    
    if (mysqli_query($conn, $query)) {

        // 2. AMBIL DATA PESANAN (Buat isi pesan WA)
        $q_cek = mysqli_query($conn, "SELECT * FROM transaksi WHERE order_id='$order_id'");
        $data  = mysqli_fetch_assoc($q_cek);

        $nama_pel = $data['nama_pelanggan'];
        $wa_pel   = $data['no_wa'];
        
        // Cek kolom alamat (jaga-jaga kalau namanya alamat_jemput atau alamat)
        $alamat   = !empty($data['alamat_jemput']) ? $data['alamat_jemput'] : $data['alamat'];
        
        $total    = number_format($data['total_harga'], 0, ',', '.');
        $layanan  = $data['jenis_layanan'];

        // ==========================================
        // A. KIRIM WA KE PELANGGAN (Konfirmasi)
        // ==========================================
        $pesan_pel = "*Pembayaran Lunas!* ✅\n\n";
        $pesan_pel .= "Halo Kak *$nama_pel*, pembayaran Rp $total telah kami terima.\n";
        $pesan_pel .= "🆔 Order ID: $order_id\n\n";
        $pesan_pel .= "Status: *SEDANG MENJEMPUT* 🛵\n";
        $pesan_pel .= "Kurir kami akan segera menuju ke lokasi.\n\n";
        $pesan_pel .= "Terima kasih sudah mempercayakan cucian ke Laundry Putri!"; 

        kirimPesanWA($wa_pel, $pesan_pel);


        // ==========================================
        // B. KIRIM WA KE ADMIN (Laporan Order Masuk)
        // ==========================================
        $pesan_adm = "🔔 *PESANAN BARU (LUNAS)* 💰\n\n";
        $pesan_adm .= "Ada orderan baru nih, Bos!\n";
        $pesan_adm .= "🆔 ID: *$order_id*\n";
        $pesan_adm .= "👤 Nama: $nama_pel\n";
        $pesan_adm .= "🧺 Layanan: $layanan\n";
        $pesan_adm .= "💵 Total: Rp $total\n\n";
        $pesan_adm .= "📍 *Lokasi Jemput:*\n$alamat\n\n";
        $pesan_adm .= "Mohon segera tugaskan untuk meluncur!";

        kirimPesanWA($nomor_admin, $pesan_adm);


        // 3. REDIRECT
        echo "<script>
                alert('Pembayaran Berhasil! Notifikasi sudah dikirim ke Admin & Pelanggan.');
                window.location.href = 'lacak.php?order_id=$order_id'; 
              </script>";
              
    } else {
        echo "Gagal update database: " . mysqli_error($conn);
    }

} else {
    header("Location: index.php");
}
?>