<?php
// Jalankan file ini SATU KALI saja di browser untuk membuat database & tabel awal.
$dbhost = "localhost";
$dbuser = "root";
$dbpass = ""; // Kosongkan jika password default Laragon tidak ada
$dbname = "db_talenta";

// 1. Koneksi ke MySQL
$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// 2. Buat Database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    echo "Database berhasil dibuat / sudah ada.<br>";
} else {
    echo "Error membuat database: " . mysqli_error($conn) . "<br>";
}

// 3. Seleksi Database
mysqli_select_db($conn, $dbname);

// 4. Buat Tabel Users (Untuk login)
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'mahasiswasi') NOT NULL
)";
mysqli_query($conn, $sql_users);

// Input akun default (password di-hash, TIDAK disimpan polos)
$cek = mysqli_query($conn, "SELECT id FROM users WHERE username='admin123'");
if (mysqli_num_rows($cek) == 0) {
    $hashAdmin = password_hash("admin123", PASSWORD_DEFAULT);
    $hashUser  = password_hash("mahasiswasi", PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('admin123', '$hashAdmin', 'admin')");
    mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('mahasiswasi', '$hashUser', 'mahasiswasi')");
}

// 5. Buat Tabel Inventaris (Kostum)
$sql_inventaris = "CREATE TABLE IF NOT EXISTS inventaris (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_kostum VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    stok INT(11),
    harga INT(11),
    gambar VARCHAR(255)
)";
mysqli_query($conn, $sql_inventaris);

// Seed data kostum awal supaya inventaris.php & sewa.php punya data nyata dari database
$cekInv = mysqli_query($conn, "SELECT id FROM inventaris");
if (mysqli_num_rows($cekInv) == 0) {
    $seed = [
        ["Baju Bodo Modern", "Tradisional Sulawesi", 5, 150000, "bajubodopinkmodrn.jpeg"],
        ["Kostum Tari Paduppa", "Tari Tradisional", 12, 100000, "paduppahijau.png"],
        ["Kostum Kreasi Baru", "Tari Modern", 8, 120000, "kreasibaru.png"],
        ["Kostum Tari 4 Etnis Modern", "Tradisional Sulawesi", 3, 150000, "4etnis.jpeg"],
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO inventaris (nama_kostum, kategori, stok, harga, gambar) VALUES (?, ?, ?, ?, ?)");
    foreach ($seed as $s) {
        mysqli_stmt_bind_param($stmt, "ssiis", $s[0], $s[1], $s[2], $s[3], $s[4]);
        mysqli_stmt_execute($stmt);
    }
}

// 6. Buat Tabel Sewa
// Kolom "status" & "catatan_admin" dipakai untuk fitur konfirmasi
// admin (diterima/ditolak) di admin/edit_sewa.php & riwayat_sewa.php.
$sql_sewa = "CREATE TABLE IF NOT EXISTS sewa (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_user INT(11),
    id_kostum INT(11),
    jumlah INT(11),
    tanggal_sewa DATE,
    lama_sewa INT(11),
    catatan TEXT,
    status ENUM('menunggu', 'diterima', 'ditolak') NOT NULL DEFAULT 'menunggu',
    catatan_admin TEXT NULL
)";
mysqli_query($conn, $sql_sewa);

// 7. Migrasi kolom untuk database lama (yang sudah dibuat sebelum
//    fitur konfirmasi admin ditambahkan). Aman dijalankan berulang.
$cekStatus = mysqli_query($conn, "SHOW COLUMNS FROM sewa LIKE 'status'");
if (mysqli_num_rows($cekStatus) == 0) {
    mysqli_query($conn, "ALTER TABLE sewa ADD COLUMN status ENUM('menunggu', 'diterima', 'ditolak') NOT NULL DEFAULT 'menunggu' AFTER lama_sewa");
}
$cekCatatanAdmin = mysqli_query($conn, "SHOW COLUMNS FROM sewa LIKE 'catatan_admin'");
if (mysqli_num_rows($cekCatatanAdmin) == 0) {
    mysqli_query($conn, "ALTER TABLE sewa ADD COLUMN catatan_admin TEXT NULL AFTER catatan");
}

echo "Tabel & data awal berhasil dibuat! Akun default: admin123/admin123 (admin), mahasiswasi/mahasiswasi (member).";
mysqli_close($conn);
