<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}
$root = '';
$page_title = 'Beranda';
require_once __DIR__ . '/includes/header.php';
?>

    <section class="hero">
        <p class="hero-tagline">Selamat Datang di Sanggar Seni</p>
        <h2 class="hero-title">TALENTA PROJECT</h2>
        <p class="hero-desc">
            Talenta Project hadir sebagai pusat penyewaan kostum profesional
            yang memadukan keelokan tradisi dengan sentuhan modernitas melalui koleksi busana pilihan,
            mulai dari tari tradisional autentik hingga desain modifikasi dan modern yang inovatif.
            Kami berkomitmen menyempurnakan setiap penampilan panggung Anda dengan kualitas terbaik dan detail artistik yang memberikan kesan mewah serta tak terlupakan.
        </p>
        <img src="image/Studio.png" alt="Banner Talenta Project" class="hero-banner">
    </section>

    <div class="container">
        <div class="section-title">
            <h2>Kostum Pilihan</h2>
        </div>

        <div class="kostum-grid">
            <div class="kostum-card">
                <img src="image/4etnis.jpeg" alt="Kostum Tari 4 Etnis Modern" class="kostum-img">
                <div class="kostum-info">
                    <p class="kostum-name">Kostum Tari 4 Etnis Modern</p>
                    <p class="kostum-price">Mulai <span>Rp 150.000/set</span></p>
                </div>
            </div>
            <div class="kostum-card">
                <img src="image/paduppahijau.png" alt="Kostum Tari Paduppa" class="kostum-img">
                <div class="kostum-info">
                    <p class="kostum-name">Kostum Tari Paduppa Modern</p>
                    <p class="kostum-price">Mulai <span>Rp 100.000/set</span></p>
                </div>
            </div>
            <div class="kostum-card">
                <img src="image/kreasibaru.png" alt="Kostum Kreasi Baru" class="kostum-img">
                <div class="kostum-info">
                    <p class="kostum-name">Kostum Tari Kreasi Baru</p>
                    <p class="kostum-price">Mulai <span>Rp 120.000/set</span></p>
                </div>
            </div>
            <div class="kostum-card">
                <img src="image/bajubodopinkmodrn.jpeg" alt="Baju Bodo Modern" class="kostum-img">
                <div class="kostum-info">
                    <p class="kostum-name">Kostum / Baju Bodo Modern</p>
                    <p class="kostum-price">Mulai <span>Rp 100.000/set</span></p>
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <a href="sewa.php" class="btn">Mulai Sewa Sekarang</a>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
