<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi redirect: hanya boleh path relatif internal (tidak boleh ke domain lain)
$redirect_raw = $_POST['redirect'] ?? '';
// Tolak jika mengandung skema URL (http/https/ftp/dll) atau path mutlak ke server lain
if (preg_match('#^https?://#i', $redirect_raw) || str_starts_with($redirect_raw, '//')) {
    $redirect_raw = '../pengguna/dashboard.php';
}
$redirect = $redirect_raw !== '' ? $redirect_raw : '../pengguna/dashboard.php';

$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($data && password_verify($password, $data['password'])) {
    // Regenerate session ID untuk mencegah session fixation attack
    session_regenerate_id(true);

    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];
    $_SESSION['id_user'] = $data['id'];
    $_SESSION['email'] = $data['email'];

    if ($data['role'] === 'admin') {
        header("location:../admin/dashboard.php");
    } else {
        // Kembalikan pengguna ke halaman yang tadi mau dituju (mis. sewa.php)
        header("location:" . $redirect);
    }
    exit();
} else {
    header("location:../pengguna/login.php?gagal=1");
    exit();
}
