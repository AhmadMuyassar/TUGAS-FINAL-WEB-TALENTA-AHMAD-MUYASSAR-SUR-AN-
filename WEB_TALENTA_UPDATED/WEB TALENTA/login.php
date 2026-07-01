<?php
session_start();
if (isset($_SESSION['username'])) {
    // Sudah login, jangan tampilkan form login lagi
    header("location:index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <title>Login | Talenta Project</title>
    <link rel="icon" type="image/png" href="image/LOGO TALENTA.png">
</head>

<body>
    <div class="login-wrapper">
        <div class="login-card">
            <img src="image/LOGO TALENTA.png" alt="Logo Talenta Project" class="login-logo-img">
            <h1 class="login-logo">SANGGAR SENI TALENTA PROJECT</h1>
            <p class="login-subtitle">Member Area</p>

            <?php if (isset($_GET['gagal'])): ?>
                <p style="color:#c62828; margin-bottom:16px; font-size:0.85rem;">Username atau password salah.</p>
            <?php endif; ?>

            <form id="form-login" action="cek_login.php" method="POST">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>

                <button type="submit" class="btn" style="width: 100%; margin-top: 24px; padding: 14px;">MASUK</button>
            </form>

            <p class="login-footer-text">
                Belum punya akun? <a href="#">Daftar Sekarang</a>
            </p>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Talenta Project. All Rights Reserved.</p>
    </footer>

    <script src="assets/script.js"></script>
</body>

</html>
