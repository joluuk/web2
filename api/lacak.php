<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$data = null;
$error = "";

if (isset($_GET['order_id'])) {
    $oid = mysqli_real_escape_string($conn, $_GET['order_id']);
    $query = mysqli_query($conn, "SELECT * FROM transaksi WHERE order_id='$oid'");
    
    if(mysqli_num_rows($query) > 0){
        $data = mysqli_fetch_assoc($query);
        
        // Cek Pembayaran
        if ($data['status_bayar'] != 'Lunas' && $data['status_bayar'] != 'lunas') {
            $error = "Pesanan belum dibayar. Silakan bayar dulu.";
            $data = null; 
        }
    } else {
        $error = "Order ID tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #f0f9ff; font-family: sans-serif; }
        .card-status { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .bg-gradient-primary { background: linear-gradient(135deg, #00b4d8, #0077b6); }
        .status-icon { font-size: 4rem; color: #0077b6; margin-bottom: 15px; }
        
        /* Animasi Motor Jalan */
        .motor-jalan { animation: drive 2s infinite linear; display: inline-block; }
        @keyframes drive {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

<div class="container py-5 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-5">
        
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary"><i class="fa-solid fa-map-location-dot"></i> Lacak </h3>
            <p class="text-muted">Pantau status penjemputanmu di sini</p>
        </div>

        <form action="" method="GET" class="mb-4">
            <div class="input-group shadow-sm">
                <input type="text" name="order_id" class="form-control border-0 p-3" placeholder="Masukkan Order ID..." value="<?php echo isset($_GET['order_id']) ? $_GET['order_id'] : ''; ?>" required>
                <button class="btn btn-primary px-4"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-warning text-center shadow-sm border-0 rounded-4">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?php echo $error; ?>
                <?php if(strpos($error, 'bayar') !== false): ?>
                    <br><a href="nota.php?id=<?php echo $_GET['order_id']; ?>" class="btn btn-sm btn-dark mt-2 rounded-pill px-3">Bayar Sekarang</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($data): ?>
            <div class="card card-status">
                <div class="bg-gradient-primary p-4 text-center text-white">
                    <h5 class="mb-0 fw-bold">Order ID: <?php echo $data['order_id']; ?></h5>
                    <small class="opacity-75"><?php echo $data['nama_pelanggan']; ?></small>
                </div>

                <div class="card-body p-5 text-center bg-white">
                    
                    <div class="mb-3">
                        <?php if($data['status_laundry'] == 'Sedang Menjemput'): ?>
                            <i class="fa-solid fa-motorcycle status-icon motor-jalan"></i>
                        <?php elseif($data['status_laundry'] == 'Selesai'): ?>
                            <i class="fa-solid fa-circle-check status-icon text-success"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-jug-detergent status-icon"></i>
                        <?php endif; ?>
                    </div>

                    <h2 class="fw-bold text-dark text-uppercase mb-2">
                        <?php echo $data['status_laundry']; ?>
                    </h2>
                    
                    <p class="text-muted mb-4">
                        <?php 
                        if($data['status_laundry'] == 'Sedang Menjemput') {
                            echo "Kami sedang dalam perjalanan ke alamat.";
                        } elseif($data['status_laundry'] == 'Selesai') {
                            echo "Laundry sudah selesai dan diterima.";
                        } else {
                            echo "Pesanan sedang diproses.";
                        }
                        ?>
                    </p>

                    <div class="alert alert-info py-2 small rounded-3 border-0">
                        <i class="fa-brands fa-whatsapp me-1"></i> Untuk update proses selanjutnya akan dikirim ke WA.
                    </div>
                    <hr class="my-4">

                    <div class="row text-start small text-muted">
                        <div class="col-6">
                            <span>Layanan:</span><br>
                            <strong class="text-dark"><?php echo $data['jenis_layanan']; ?></strong>
                        </div>
                        <div class="col-6 text-end">
                            <span>Status Bayar:</span><br>
                            <span class="badge bg-success rounded-pill px-3">LUNAS</span>
                        </div>
                    </div>

                </div>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4">
            <a href="pembeli.php" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Menu Utama</a>
        </div>

    </div>
</div>

</body>
</html>