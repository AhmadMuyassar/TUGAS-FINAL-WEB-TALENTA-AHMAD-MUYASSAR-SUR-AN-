<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// Ambil nama gambar untuk dihapus dari folder
$stmt = mysqli_prepare($conn, "SELECT gambar FROM inventaris WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($data && $data['gambar'] && file_exists(__DIR__ . '/../image/' . $data['gambar'])) {
    unlink(__DIR__ . '/../image/' . $data['gambar']);
}

// Hapus data (prepared statement)
$del = mysqli_prepare($conn, "DELETE FROM inventaris WHERE id = ?");
mysqli_stmt_bind_param($del, "i", $id);
mysqli_stmt_execute($del);

header("location:inventaris_admin.php");
exit();
