<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "DELETE FROM sewa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("location:data_sewa.php");
exit();
