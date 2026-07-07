<?php
// $root       = '' jika file di root, '../' jika di dalam pengguna/ atau admin/
// $page_title = judul halaman (opsional)
if (!isset($root)) { $root = ''; }
$judul = isset($page_title) ? $page_title . ' | Talenta Project' : 'Talenta Project';
$is_admin_page = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $root; ?>assets/style.css">
    <title><?php echo htmlspecialchars($judul); ?></title>
    <link rel="icon" type="image/png" href="<?php echo $root; ?>gambar/LOGO TALENTA.png">
</head>
<body>
    <header>
        <div class="header-brand">
            <a href="<?php echo $root; ?><?php echo $is_admin_page ? 'admin/dashboard.php' : 'pengguna/dashboard.php'; ?>">
                <img src="<?php echo $root; ?>gambar/LOGO TALENTA.png" alt="Logo Talenta" class="header-logo">
            </a>
            <h1>
                <a href="<?php echo $root; ?><?php echo $is_admin_page ? 'admin/dashboard.php' : 'pengguna/dashboard.php'; ?>">
                    TALENTA PROJECT
                </a>
            </h1>
        </div>
        <nav>
            <?php if ($is_admin_page): ?>
                <!-- ===== NAV ADMIN ===== -->
                <a href="<?php echo $root; ?>admin/dashboard.php">Dashboard</a>
                <a href="<?php echo $root; ?>admin/kelola_kostum.php">Kostum</a>
                <a href="<?php echo $root; ?>admin/kelola_sewa.php">Penyewaan</a>
                <a href="<?php echo $root; ?>admin/kelola_konten.php">Konten</a>
                <a href="<?php echo $root; ?>pengguna/logout.php" class="nav-logout">
                    Logout <span class="nav-user">(<?php echo htmlspecialchars($_SESSION['username']); ?>)</span>
                </a>
            <?php else: ?>
                <!-- ===== NAV PENGGUNA ===== -->
                <a href="<?php echo $root; ?>pengguna/dashboard.php">Beranda</a>
                <a href="<?php echo $root; ?>pengguna/kostum.php">Daftar Kostum</a>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="<?php echo $root; ?>pengguna/riwayat.php">Riwayat Sewa</a>
                    <a href="<?php echo $root; ?>pengguna/profil.php">Profil</a>
                    <a href="<?php echo $root; ?>pengguna/logout.php" class="nav-logout">
                        Logout <span class="nav-user">(<?php echo htmlspecialchars($_SESSION['username']); ?>)</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo $root; ?>pengguna/login.php">Login</a>
                    <a href="<?php echo $root; ?>pengguna/daftar.php">Daftar</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
    </header>