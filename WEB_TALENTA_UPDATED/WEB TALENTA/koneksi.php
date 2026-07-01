<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = ""; // Jika menggunakan Laragon standar, password biasanya kosong
$dbname = "db_talenta";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>