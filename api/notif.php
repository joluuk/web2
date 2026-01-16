<?php
function kirimPesanWA($nomor, $pesan) {
    // ------------------------------------------------------------------
    // KONFIGURASI TOKEN FONNTE
    // Ganti kode di bawah ini dengan Token asli dari dashboard Fonnte Antum
    // ------------------------------------------------------------------
    $token = "wJzrFd2GrNYz3o2DuYfP"; 

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.fonnte.com/send',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'target' => $nomor,
        'message' => $pesan,
        'countryCode' => '62', // Otomatis ubah 08xx jadi 628xx
      ),
      CURLOPT_HTTPHEADER => array(
        "Authorization: $token"
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    
    return $response;
}
?>