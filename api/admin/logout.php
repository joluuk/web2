<?php
// Hapus semua cookie login
setcookie("user_login", "", time() - 3600, "/");
setcookie("user_level", "", time() - 3600, "/");
setcookie("user_status", "", time() - 3600, "/");

header("location:index.php?pesan=logout");
exit;
?>