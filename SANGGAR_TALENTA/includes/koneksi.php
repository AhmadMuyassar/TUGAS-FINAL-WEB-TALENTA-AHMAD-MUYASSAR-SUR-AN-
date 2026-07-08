<?php
// ================================================================
// includes/koneksi.php — Satu-satunya sumber koneksi database.
// Semua file lain cukup panggil: require_once __DIR__.'/../includes/koneksi.php';
// ================================================================
$dbhost = "localhost";
$dbuser = "root";
$dbpass = ""; // Kosongkan jika password default Laragon/XAMPP tidak ada
$dbname = "db_talenta";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
