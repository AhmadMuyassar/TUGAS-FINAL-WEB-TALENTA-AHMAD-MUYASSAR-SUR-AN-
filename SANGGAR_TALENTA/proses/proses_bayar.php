<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';

if (!isset($_SESSION['username'])) {
    header("location:../pengguna/login.php");
    exit();
}

$id_sewa = (int) ($_POST['id_sewa'] ?? 0);
$id_user = (int) $_SESSION['id_user'];
$metode  = $_POST['metode_bayar'] ?? '';

// Pastikan sewa ini benar milik user yang login
$cek = mysqli_prepare($conn, "SELECT id FROM sewa WHERE id = ? AND id_user = ?");
mysqli_stmt_bind_param($cek, "ii", $id_sewa, $id_user);
mysqli_stmt_execute($cek);
if (!mysqli_fetch_assoc(mysqli_stmt_get_result($cek))) {
    header("location:../pengguna/riwayat.php");
    exit();
}

if ($metode === 'cash') {
    // Cash: status bayar menunggu konfirmasi lunas saat di tempat (diverifikasi admin manual)
    $stmt = mysqli_prepare($conn, "UPDATE sewa SET metode_bayar='cash', status_bayar='menunggu_verifikasi' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_sewa);
    mysqli_stmt_execute($stmt);

} elseif ($metode === 'transfer') {
    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
        $hasil = upload_gambar('bukti', 'bukti_transfer');
        if (is_array($hasil)) {
            // Gagal validasi — kembalikan ke halaman pembayaran dengan pesan error
            header("location:../pengguna/pembayaran.php?id=$id_sewa&error=" . urlencode($hasil['error']));
            exit();
        }
        $bukti = $hasil;
    }
    $stmt = mysqli_prepare($conn, "UPDATE sewa SET metode_bayar='transfer', status_bayar='menunggu_verifikasi', bukti_transfer=? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $bukti, $id_sewa);
    mysqli_stmt_execute($stmt);
}

header("location:../pengguna/riwayat.php");
exit();
