<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';

if (!sudah_login()) {
    header("location:../pengguna/login.php");
    exit();
}

$id_user         = (int) $_SESSION['id_user'];
$nama_lengkap    = trim($_POST['nama_lengkap'] ?? '');
$email           = trim($_POST['email'] ?? '');
$no_hp           = trim($_POST['no_hp'] ?? '');
$alamat          = trim($_POST['alamat'] ?? '');
$jenis_identitas = trim($_POST['jenis_identitas'] ?? '');
$nomor_identitas = trim($_POST['nomor_identitas'] ?? '');
$password_baru   = $_POST['password_baru'] ?? '';

if ($nama_lengkap === '' || $email === '' || $no_hp === '' || $alamat === '' || $jenis_identitas === '' || $nomor_identitas === '') {
    header("location:../pengguna/profil.php?error=" . urlencode('Semua field wajib diisi.'));
    exit();
}

// Ambil data lama untuk nama file gambar
$lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto_profil, foto_identitas FROM users WHERE id=$id_user"));

// Upload foto profil (opsional)
$foto_profil = $lama['foto_profil'] ?? '';
if (!empty($_FILES['foto_profil']['name'])) {
    $hasil = upload_gambar('foto_profil', 'profil', $foto_profil);
    if (is_array($hasil)) {
        header("location:../pengguna/profil.php?error=" . urlencode($hasil['error']));
        exit();
    }
    $foto_profil = $hasil;
}

// Upload foto identitas (opsional saat edit)
$foto_identitas = $lama['foto_identitas'] ?? '';
if (!empty($_FILES['foto_identitas']['name'])) {
    $hasil = upload_gambar('foto_identitas', 'identitas', $foto_identitas);
    if (is_array($hasil)) {
        header("location:../pengguna/profil.php?error=" . urlencode($hasil['error']));
        exit();
    }
    $foto_identitas = $hasil;
}

// Update data
if ($password_baru !== '') {
    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET nama_lengkap=?, email=?, no_hp=?, alamat=?, jenis_identitas=?, nomor_identitas=?, foto_profil=?, foto_identitas=?, password=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sssssssssi",
        $nama_lengkap, $email, $no_hp, $alamat, $jenis_identitas, $nomor_identitas, $foto_profil, $foto_identitas, $hash, $id_user);
} else {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET nama_lengkap=?, email=?, no_hp=?, alamat=?, jenis_identitas=?, nomor_identitas=?, foto_profil=?, foto_identitas=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssssssssi",
        $nama_lengkap, $email, $no_hp, $alamat, $jenis_identitas, $nomor_identitas, $foto_profil, $foto_identitas, $id_user);
}
mysqli_stmt_execute($stmt);

// Update session nama jika berubah
$_SESSION['nama_lengkap'] = $nama_lengkap;

header("location:../pengguna/profil.php?sukses=1");
exit();
