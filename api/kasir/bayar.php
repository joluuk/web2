<?php
session_start();
// Pastikan jalur koneksi aman untuk Vercel
include __DIR__ . '/../koneksi.php';

// 1. Ambil Order ID dari URL
$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($conn, $_GET['order_id']) : '';

// 2. Ambil data transaksi dari database berdasarkan Order ID
$query = mysqli_query($conn, "SELECT * FROM transaksi WHERE order_id = '$order_id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan, kembalikan ke kasir
if (!$data) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='../../kasir/kasir.php';</script>";
    exit;
}

// 3. Konfigurasi Midtrans PRODUCTION (Sesuai key Antum sebelumnya)
$server_key = 'Mid-server-JeV5Qo3SBTLVWxZmYxQNIOM_'; 
$api_url = 'https://app.midtrans.com/snap/v1/transactions'; 

// 4. Siapkan Data JSON untuk Request Token
$params = [
    'transaction_details' => [
        'order_id'     => $data['order_id'],
        'gross_amount' => (int)$data['total_harga'],
    ],
    'customer_details' => [
        'first_name' => $data['nama_pelanggan'],
        'phone'      => $data['no_wa'],
    ],
];

$json = json_encode($params);

// 5. Minta Snap Token via cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . base64_encode($server_key . ':')
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$snap_token = isset($result['token']) ? $result['token'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Online - Laundry Putri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background-color: #f0f9ff; height: 100vh; display: flex; align-items: center; font-family: 'Segoe UI', sans-serif; }
        .payment-card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-width: 400px; width: 100%; margin: auto; }
        .bg-gradient-primary { background: linear-gradient(135deg, #0077b6, #00b4d8); }
    </style>
</head>
<body>

<div class="container">
    <div class="card payment-card overflow-hidden">
        <div class="card-header bg-gradient-primary text-white text-center py-4">
            <i class="fa-solid fa-qrcode fa-3x mb-2 opacity-75"></i>
            <h5 class="fw-bold mb-0">Scan QRIS / Transfer</h5>
            <p class="small mb-0 opacity-75">Selesaikan pembayaran untuk memproses laundry</p>
        </div>

        <div class="card-body p-4">
            <div class="bg-light p-3 rounded mb-4 border">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Order ID</span>
                    <span class="fw-bold small text-dark"><?php echo $data['order_id']; ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Pelanggan</span>
                    <span class="fw-bold small text-dark"><?php echo $data['nama_pelanggan']; ?></span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted fw-bold small">Total Bayar</span>
                    <h4 class="text-primary fw-bold mb-0">Rp <?php echo number_format($data['total_harga'], 0, ',', '.'); ?></h4>
                </div>
            </div>

            <?php if ($snap_token): ?>
                <button id="pay-button" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                    <i class="fa-solid fa-wallet me-2"></i>BAYAR SEKARANG
                </button>
            <?php else: ?>
                <div class="alert alert-danger small text-center">
                    Token Pembayaran Gagal Dibuat. Cek Server Key!
                </div>
            <?php endif; ?>
            
            <a href="../../kasir/kasir.php" class="btn btn-link w-100 text-decoration-none text-muted mt-3 small">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Kasir
            </a>
        </div>
    </div>
</div>

<script src="https://app.midtrans.com/snap/snap.js" data-client-key="Mid-client-O_NeXjBrodPBzX9m"></script>

<script type="text/javascript">
    const payButton = document.getElementById('pay-button');
    if(payButton) {
        payButton.onclick = function(){
            snap.pay('<?php echo $snap_token; ?>', {
                onSuccess: function(result){
                    // FITUR UPDATE OTOMATIS
                    // Panggil file update_otomatis.php di background agar status jadi 'lunas'
                    fetch('update.php?order_id=<?php echo $order_id; ?>')
                    .then(response => {
                        alert("Pembayaran Berhasil & Lunas."); 
                        window.location.href = '../../kasir/kasir.php?pesan=sukses_lunas';
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Pembayaran sukses, tapi gagal update status otomatis. Silakan cek manual.");
                        window.location.href = '../../kasir/kasir.php';
                    });
                },
                onPending: function(result){
                    alert("Menunggu Pembayaran..."); 
                    window.location.href = '../../kasir/kasir.php?pesan=simpan_pending';
                },
                onError: function(result ){
                    alert("Pembayaran Gagal!");
                },
                onClose: function(){
                    alert('menutup popup sebelum menyelesaikan pembayaran.');
                }
            });
        };
    }
</script>

</body>
</html>