<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "01_uts_erp"; // Sesuaikan dengan nama database Anda

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>