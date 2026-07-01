<?php
session_start();
require_once __DIR__ . '/koneksi.php';

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$user = $_SESSION['username'];
$id_kostum = (int) ($_POST['id_kostum'] ?? 0);
$jumlah = (int) ($_POST['jumlah'] ?? 0);
$lama = (int) ($_POST['lama'] ?? 0);
$catatan = $_POST['catatan'] ?? '';
$tgl = date('Y-m-d');

// Cari ID user berdasarkan username (prepared statement)
$stmtUser = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmtUser, "s", $user);
mysqli_stmt_execute($stmtUser);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUser));
$id_user = $u['id'] ?? null;

if ($id_user && $id_kostum > 0 && $jumlah > 0 && $lama > 0) {
    $stmt = mysqli_prepare($conn, "INSERT INTO sewa (id_user, id_kostum, jumlah, tanggal_sewa, lama_sewa, catatan) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iiisis", $id_user, $id_kostum, $jumlah, $tgl, $lama, $catatan);
    mysqli_stmt_execute($stmt);

    header("location:sewa.php?sukses=1");
    exit();
} else {
    header("location:sewa.php?error=1");
    exit();
}
