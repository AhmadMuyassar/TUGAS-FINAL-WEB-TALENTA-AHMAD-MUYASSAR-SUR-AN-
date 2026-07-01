<?php
session_start();
require_once __DIR__ . '/koneksi.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Prepared statement supaya aman dari SQL Injection
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

// password_verify cocok dengan hash yang dibuat password_hash() di buat_db.php
if ($data && password_verify($password, $data['password'])) {
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];

    if ($data['role'] == "admin") {
        header("location:admin/dashboard.php");
    } else {
        header("location:index.php");
    }
    exit();
} else {
    header("location:login.php?gagal=1");
    exit();
}
