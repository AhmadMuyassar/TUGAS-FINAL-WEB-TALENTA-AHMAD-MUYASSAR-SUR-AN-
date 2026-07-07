<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_admin();

$root = '../';
$page_title = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';

$totalKostum = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM inventaris"))['n'];
$totalSewa   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM sewa"))['n'];
$totalUser   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM users WHERE role='pengguna'"))['n'];
$menunggu    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM sewa WHERE status='menunggu'"))['n'];
$bayarMasuk  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM sewa WHERE status_bayar='menunggu_verifikasi'"))['n'];
?>

<div class="container">
    <div class="admin-welcome">
        <div>
            <h2 style="margin-bottom:4px;border:none;padding:0;">Dashboard Admin</h2>
            <p style="color:#888;font-size:0.88rem;">
                Halo <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> —
                kelola semua data Talenta Project di sini.
            </p>
        </div>
        <a href="<?php echo $root; ?>pengguna/dashboard.php" target="_blank" class="btn-sm btn-secondary">
            👁 Lihat Beranda Publik
        </a>
    </div>

    <!-- ===== STAT CARDS — horizontal ===== -->
    <div class="stat-row">
        <a href="kelola_kostum.php" class="stat-card">
            <div class="stat-icon">👗</div>
            <div class="stat-info">
                <p class="stat-label">Total Kostum</p>
                <p class="stat-number"><?php echo $totalKostum; ?></p>
            </div>
        </a>
        <a href="kelola_sewa.php" class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-info">
                <p class="stat-label">Total Penyewaan</p>
                <p class="stat-number"><?php echo $totalSewa; ?></p>
            </div>
        </a>
        <a href="kelola_sewa.php" class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <p class="stat-label">Total Pengguna</p>
                <p class="stat-number"><?php echo $totalUser; ?></p>
            </div>
        </a>
        <a href="kelola_sewa.php" class="stat-card stat-card--warn">
            <div class="stat-icon">⏳</div>
            <div class="stat-info">
                <p class="stat-label">Menunggu Konfirmasi</p>
                <p class="stat-number"><?php echo $menunggu; ?></p>
            </div>
        </a>
        <a href="kelola_sewa.php" class="stat-card stat-card--warn">
            <div class="stat-icon">💳</div>
            <div class="stat-info">
                <p class="stat-label">Perlu Verifikasi Bayar</p>
                <p class="stat-number"><?php echo $bayarMasuk; ?></p>
            </div>
        </a>
    </div>

    <!-- ===== MENU KELOLA ===== -->
    <h3 class="admin-section-label">Menu Pengelolaan</h3>
    <div class="admin-menu-row">
        <a href="kelola_sewa.php" class="admin-menu-card">
            <span class="admin-menu-icon">📋</span>
            <span class="admin-menu-label">Kelola Penyewaan</span>
            <span class="admin-menu-desc">Konfirmasi & verifikasi pembayaran</span>
        </a>
        <a href="kelola_kostum.php" class="admin-menu-card">
            <span class="admin-menu-icon">👗</span>
            <span class="admin-menu-label">Kelola Kostum</span>
            <span class="admin-menu-desc">Tambah, edit, hapus inventaris</span>
        </a>
        <a href="kelola_konten.php?tab=slide" class="admin-menu-card">
            <span class="admin-menu-icon">🎞</span>
            <span class="admin-menu-label">Hero Slide</span>
            <span class="admin-menu-desc">Kelola slide beranda utama</span>
        </a>
        <a href="kelola_konten.php?tab=pelatihan" class="admin-menu-card">
            <span class="admin-menu-icon">🎓</span>
            <span class="admin-menu-label">Kelola Pelatihan</span>
            <span class="admin-menu-desc">Jadwal & info pelatihan tari</span>
        </a>
        <a href="kelola_konten.php?tab=karya" class="admin-menu-card">
            <span class="admin-menu-icon">🖼️</span>
            <span class="admin-menu-label">Hasil Karya</span>
            <span class="admin-menu-desc">Galeri karya sanggar</span>
        </a>
        <a href="kelola_konten.php?tab=event" class="admin-menu-card">
            <span class="admin-menu-icon">🏆</span>
            <span class="admin-menu-label">Event & Prestasi</span>
            <span class="admin-menu-desc">Dokumentasi event & penghargaan</span>
        </a>
    </div>
</div>

<!-- ================================================================
     PREVIEW BERANDA — admin lihat langsung tampilan pengguna
     ================================================================ -->
<div class="admin-preview-banner">
    <div class="container">
        <p class="section-eyebrow" style="color:#d4af37;">Preview Langsung</p>
        <h3 style="color:#fff;font-family:'Cinzel',serif;letter-spacing:2px;border:none;padding:0;margin-bottom:6px;">
            Tampilan Beranda Pengguna
        </h3>
        <p style="color:#aaa;font-size:0.85rem;margin-bottom:0;">
            Perubahan konten yang Anda simpan akan langsung terlihat di bawah ini.
        </p>
    </div>
</div>

<?php
// Sertakan beranda pengguna langsung di halaman admin
// Override root agar path gambar tetap benar dari folder admin/
$root = '../';
// Matikan wajib_pengguna agar tidak loop redirect
define('ADMIN_PREVIEW', true);

// Query ulang data beranda
$favorit   = mysqli_query($conn, "SELECT * FROM inventaris WHERE favorit = 1 ORDER BY id ASC LIMIT 4");
$pelatihan = mysqli_query($conn, "SELECT * FROM pelatihan ORDER BY id ASC LIMIT 4");
$karya_q   = mysqli_query($conn, "SELECT * FROM hasil_karya ORDER BY id DESC LIMIT 6");
$event_q   = mysqli_query($conn, "SELECT * FROM event_prestasi ORDER BY tanggal DESC LIMIT 5");
$slides_q  = mysqli_query($conn, "SELECT * FROM hero_slide WHERE aktif=1 ORDER BY urutan ASC, id ASC LIMIT 5");

$slides     = [];
$karya_rows = [];
$event_rows = [];
while ($s = mysqli_fetch_assoc($slides_q))  $slides[]     = $s;
while ($k = mysqli_fetch_assoc($karya_q))   $karya_rows[] = $k;
while ($e = mysqli_fetch_assoc($event_q))   $event_rows[] = $e;

// Sertakan konten beranda (tanpa header/footer — hanya body content)
include __DIR__ . '/beranda_preview.php';
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
