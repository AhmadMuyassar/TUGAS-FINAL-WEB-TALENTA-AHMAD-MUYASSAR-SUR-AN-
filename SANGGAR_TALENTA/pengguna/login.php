<?php
session_start();
if (isset($_SESSION['username'])) {
    header("location:" . ($_SESSION['role'] === 'admin' ? '../admin/dashboard.php' : 'dashboard.php'));
    exit();
}
// Simpan halaman tujuan (kalau user diarahkan kesini saat mau sewa kostum)
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../pengguna/dashboard.php';

$root = '../';
$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container" style="max-width:420px;">
        <div class="login-logo-wrap">
            <img src="../gambar/LOGO TALENTA.png" alt="Logo Talenta Project" class="login-logo-img">
            <p class="login-logo">TALENTA PROJECT</p>
            <p class="login-subtitle">Sanggar Seni &amp; Penyewaan Kostum</p>
        </div>
        <div class="form-card">
            <h2 style="text-align:center;">Masuk Akun</h2>

            <?php if (isset($_GET['gagal'])): ?>
                <p style="color:#c62828; margin-bottom:16px; font-size:0.85rem;">Username atau password salah.</p>
            <?php endif; ?>
            <?php if (isset($_GET['daftar_sukses'])): ?>
                <p style="color:#2e7d32; margin-bottom:16px; font-size:0.85rem;">Registrasi berhasil! Silakan login.</p>
            <?php endif; ?>
            <?php if (isset($_GET['perlu_login'])): ?>
                <p style="color:#c62828; margin-bottom:16px; font-size:0.85rem;">Silakan login terlebih dahulu untuk melakukan penyewaan.</p>
            <?php endif; ?>

            <form id="form-login" action="../proses/proses_login.php" method="POST">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>

                <button type="submit" class="btn" style="width: 100%; margin-top: 24px; padding: 14px;">MASUK</button>
            </form>

            <p class="login-footer-text">
                Belum punya akun? <a href="daftar.php">Daftar Sekarang</a>
            </p>
        </div><!-- /.form-card -->
    </div><!-- /.container -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
