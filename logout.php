<?php
// Memulai session agar bisa mengakses data yang ingin dihapus
session_start();

// Menghapus semua variabel session
$_SESSION = array();

// Menghancurkan session sepenuhnya
session_destroy();

// Mengarahkan kembali ke halaman login (index.php)
header("Location: index.php");
exit();
?>