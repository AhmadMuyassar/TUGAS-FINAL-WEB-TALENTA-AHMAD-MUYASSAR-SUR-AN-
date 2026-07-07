<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';

$nama_lengkap    = trim($_POST['nama_lengkap'] ?? '');
$username        = trim($_POST['username'] ?? '');
$password        = $_POST['password'] ?? '';
$email           = trim($_POST['email'] ?? '');
$no_hp           = trim($_POST['no_hp'] ?? '');
$alamat          = trim($_POST['alamat'] ?? '');
$jenis_identitas = trim($_POST['jenis_identitas'] ?? '');
$nomor_identitas = trim($_POST['nomor_identitas'] ?? '');

// Validasi field wajib
if ($nama_lengkap === '' || $username === '' || $password === '' || $email === ''
    || $no_hp === '' || $alamat === '' || $jenis_identitas === '' || $nomor_identitas === '') {
    header("location:../pengguna/daftar.php?gagal=kosong");
    exit();
}

// Validasi foto identitas wajib ada
if (empty($_FILES['foto_identitas']['name'])) {
    header("location:../pengguna/daftar.php?gagal=kosong");
    exit();
}

// Cek username sudah dipakai atau belum
$cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($cek, "s", $username);
mysqli_stmt_execute($cek);
if (mysqli_fetch_assoc(mysqli_stmt_get_result($cek))) {
    header("location:../pengguna/daftar.php?gagal=ada");
    exit();
}

// Upload foto profil (opsional)
$foto_profil = '';
if (!empty($_FILES['foto_profil']['name'])) {
    $hasil = upload_gambar('foto_profil', 'profil');
    if (is_array($hasil)) {
        header("location:../pengguna/daftar.php?gagal=upload&msg=" . urlencode($hasil['error']));
        exit();
    }
    $foto_profil = $hasil;
}

// Upload foto identitas (wajib)
$hasil_id = upload_gambar('foto_identitas', 'identitas');
if (is_array($hasil_id)) {
    header("location:../pengguna/daftar.php?gagal=upload&msg=" . urlencode($hasil_id['error']));
    exit();
}
$foto_identitas = $hasil_id;

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn,
    "INSERT INTO users (username,password,role,nama_lengkap,email,no_hp,alamat,jenis_identitas,nomor_identitas,foto_profil,foto_identitas)
     VALUES (?,?,'pengguna',?,?,?,?,?,?,?,?)");
mysqli_stmt_bind_param($stmt, "ssssssssss",
    $username, $hash, $nama_lengkap, $email, $no_hp, $alamat,
    $jenis_identitas, $nomor_identitas, $foto_profil, $foto_identitas);
mysqli_stmt_execute($stmt);

header("location:../pengguna/login.php?daftar_sukses=1");
exit();
