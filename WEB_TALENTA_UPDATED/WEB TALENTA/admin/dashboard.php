<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$root = '../';
$page_title = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';

$totalKostum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM inventaris"))['n'];
$totalSewa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM sewa"))['n'];
$totalUser = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM users"))['n'];
?>

    <div class="container">
        <h2>Selamat Datang, Admin</h2>
        <p>Halo <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>, kelola data Talenta Project di sini.</p>

        <div class="kostum-grid" style="margin-top:30px;">
            <div class="kostum-card"><div class="kostum-info">
                <p class="kostum-name">Total Kostum</p>
                <p class="kostum-price"><span><?php echo $totalKostum; ?></span></p>
            </div></div>
            <div class="kostum-card"><div class="kostum-info">
                <p class="kostum-name">Total Penyewaan</p>
                <p class="kostum-price"><span><?php echo $totalSewa; ?></span></p>
            </div></div>
            <div class="kostum-card"><div class="kostum-info">
                <p class="kostum-name">Total Pengguna</p>
                <p class="kostum-price"><span><?php echo $totalUser; ?></span></p>
            </div></div>
        </div>

        <div style="margin-top:40px; display:flex; gap:16px; flex-wrap:wrap;">
            <a href="data_sewa.php" class="btn">Lihat Data Penyewa</a>
            <a href="inventaris_admin.php" class="btn">Kelola Kostum</a>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
