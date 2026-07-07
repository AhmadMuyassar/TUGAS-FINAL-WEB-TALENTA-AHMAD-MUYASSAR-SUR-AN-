<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';

$id_kostum     = (int) ($_POST['id_kostum'] ?? 0);

// Wajib login. Jika belum, arahkan balik ke halaman sewa kostum ini.
if (!isset($_SESSION['username'])) {
    header("location:../pengguna/login.php?redirect=" . urlencode("pengguna/sewa.php?id=$id_kostum"));
    exit();
}

$id_user       = (int) $_SESSION['id_user'];
$jumlah        = (int) ($_POST['jumlah'] ?? 0);
$lama          = (int) ($_POST['lama'] ?? 0);
$catatan       = trim($_POST['catatan'] ?? '');
$tanggal_pakai = $_POST['tanggal_pakai'] ?? date('Y-m-d');

if ($id_kostum <= 0 || $jumlah <= 0 || $lama <= 0) {
    header("location:../pengguna/sewa.php?id=$id_kostum&error=1");
    exit();
}

// Cek stok tersedia
$stok_stmt = mysqli_prepare($conn, "SELECT stok FROM inventaris WHERE id = ?");
mysqli_stmt_bind_param($stok_stmt, "i", $id_kostum);
mysqli_stmt_execute($stok_stmt);
$stok_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stok_stmt));

if (!$stok_data || $jumlah > (int) $stok_data['stok']) {
    header("location:../pengguna/sewa.php?id=$id_kostum&error=stok");
    exit();
}

// Simpan data sewa baru
$stmt = mysqli_prepare($conn, "INSERT INTO sewa (id_user, id_kostum, jumlah, tanggal_sewa, lama_sewa, catatan)
                                VALUES (?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iiisis", $id_user, $id_kostum, $jumlah, $tanggal_pakai, $lama, $catatan);
mysqli_stmt_execute($stmt);
$id_sewa = mysqli_insert_id($conn);

// ===== KIRIM NOTIFIKASI EMAIL KE ADMIN =====
$kostumData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_kostum FROM inventaris WHERE id = $id_kostum"));
$adminData  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM users WHERE role='admin' LIMIT 1"));

if ($adminData) {
    $link = "http://localhost/SANGGAR_TALENTA/admin/kelola_sewa.php?aksi=konfirmasi&id=$id_sewa";
    kirim_notifikasi_sewa_baru(
        $adminData['email'],
        $_SESSION['username'],
        $kostumData['nama_kostum'] ?? '-',
        $jumlah,
        $lama,
        $link
    );
}

header("location:../pengguna/sewa.php?id=$id_kostum&sukses=1");
exit();
