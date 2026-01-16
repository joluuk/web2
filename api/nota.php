<?php
require_once __DIR__ . '/koneksi.php';

// Tangkap ID dari URL
$id = $_GET['id'];

// Ambil data pesanan dari database
$query = mysqli_query($conn, "SELECT * FROM transaksi WHERE order_id='$id'");
$data = mysqli_fetch_assoc($query);

if(!$data) { header("location:index.php"); exit; }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - <?php echo $data['order_id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <script type="text/javascript"
      src="https://app.midtrans.com/snap/snap.js"
      data-client-key="Mid-client-O_NeXjBrodPBzX9m"> </script>
      
    <style>
        body { background: #f0f9ff; font-family: sans-serif; }
        .nota-card { background: white; border-radius: 15px; max-width: 500px; margin: 50px auto; box-shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; }
        .nota-header { background: #0077b6; color: white; padding: 25px; text-align: center; }
        .nota-body { padding: 30px; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="nota-card">
        <div class="nota-header">
            <h4 class="fw-bold m-0">Selesaikan Pembayaran</h4>
            <small>Order ID: <?php echo $data['order_id']; ?></small>
        </div>

        <div class="nota-body">
            <div class="alert alert-info text-center small mb-4">
                Mohon selesaikan pembayaran agar pesanan otomatis diproses.
            </div>

            <div class="details-row text-muted">
                <span>Nama Pelanggan</span>
                <span class="fw-bold text-dark"><?php echo $data['nama_pelanggan']; ?></span>
            </div>
            <div class="details-row text-muted">
                <span>Layanan</span>
                <span class="fw-bold text-dark"><?php echo $data['jenis_layanan']; ?></span>
            </div>
            <div class="details-row text-muted">
                <span>Berat/Qty</span>
                <span class="fw-bold text-dark"><?php echo $data['berat_qty']; ?> Kg</span>
            </div>
            
            <hr>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="fw-bold">Total Tagihan</span>
                <span class="fw-bold text-primary fs-4">Rp <?php echo number_format($data['total_harga'], 0, ',', '.'); ?></span>
            </div>

            <button id="pay-button" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                BAYAR SEKARANG
            </button>
            
            <div class="text-center mt-3">
                <a href="batal_pesan.php?id=<?php echo $data['order_id']; ?>" class="text-decoration-none small text-muted" onclick="return confirm('Yakin batal? Pesanan akan dihapus.')">Batalkan & Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Ambil tombol bayar
    var payButton = document.getElementById('pay-button');
    
    // Saat tombol diklik
    payButton.addEventListener('click', function () {
      
      // MUNCULKAN POPUP MIDTRANS
      window.snap.pay('<?php echo $data['snap_token']; ?>', {
        
        // 1. Kalau SUKSES Bayar
        onSuccess: function(result){
          var orderId = result.order_id;
          // Redirect ke update status (Lunas)
          window.location.href = "update_status.php?order_id=" + orderId;
        },
        
        // 2. Kalau PENDING (Belum dibayar tapi popup ditutup / Internet putus)
        onPending: function(result){
          alert("Menunggu pembayaran Anda! Silakan bayar sebelum batas waktu habis.");
          location.reload(); 
        },
        
        // 3. Kalau ERROR
        onError: function(result){
          alert("Pembayaran gagal!");
          location.reload();
        },
        
        // 4. Kalau POPUP DITUTUP (Tombol Silang / Close)
        onClose: function(){
          alert('Anda membatalkan pembayaran. Pesanan akan dihapus.');
          window.location.href = "batal_pesan.php?id=<?php echo $data['order_id']; ?>";
        }
      });
    });
</script>

</body>
</html>