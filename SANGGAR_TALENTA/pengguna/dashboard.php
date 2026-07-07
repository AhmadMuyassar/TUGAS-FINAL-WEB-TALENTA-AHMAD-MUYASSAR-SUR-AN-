<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_pengguna();

$root = '../';
$page_title = 'Beranda';
require_once __DIR__ . '/../includes/header.php';

$favorit   = mysqli_query($conn, "SELECT * FROM inventaris WHERE favorit = 1 ORDER BY id ASC LIMIT 4");
$pelatihan = mysqli_query($conn, "SELECT * FROM pelatihan ORDER BY id ASC LIMIT 4");
$karya     = mysqli_query($conn, "SELECT * FROM hasil_karya ORDER BY id DESC LIMIT 6");
$event     = mysqli_query($conn, "SELECT * FROM event_prestasi ORDER BY tanggal DESC LIMIT 5");

// Slide hero — dari tabel hero_slide
$slides_q = mysqli_query($conn, "SELECT * FROM hero_slide WHERE aktif=1 ORDER BY urutan ASC, id ASC LIMIT 5");
$slides   = [];
while ($s = mysqli_fetch_assoc($slides_q)) $slides[] = $s;

$karya_rows = [];
while ($k = mysqli_fetch_assoc($karya)) $karya_rows[] = $k;

$event_rows = [];
while ($e = mysqli_fetch_assoc($event)) $event_rows[] = $e;

// Profil sanggar — ambil semua key-value sekaligus
$ps_raw = mysqli_query($conn, "SELECT kunci, nilai, gambar FROM profil_sanggar");
$ps = [];
while ($r = mysqli_fetch_assoc($ps_raw)) $ps[$r['kunci']] = $r;
// Helper agar tidak undefined
function ps($ps, $key, $default = '') {
    return isset($ps[$key]) ? $ps[$key]['nilai'] : $default;
}
function ps_img($ps, $key) {
    return isset($ps[$key]) ? $ps[$key]['gambar'] : '';
}

// Tim sanggar
$tim_q    = mysqli_query($conn, "SELECT * FROM tim_sanggar ORDER BY is_founder DESC, urutan ASC, id ASC");
$tim_rows = [];
while ($t = mysqli_fetch_assoc($tim_q)) $tim_rows[] = $t;
?>

<?php if (isset($_GET['akses']) && $_GET['akses'] === 'ditolak'): ?>
<div style="max-width:960px;margin:12px auto;padding:0 24px;">
    <p style="background:#ffebee;border:1px solid #ffcdd2;color:#c62828;padding:12px 16px;border-radius:6px;font-size:0.88rem;">
        Akses ditolak. Halaman tersebut hanya bisa diakses oleh admin.
    </p>
</div>
<?php endif; ?>

<!-- ================================================================
     SECTION 1 — HERO SLIDESHOW
     ================================================================ -->
<section class="hero-slide-section">
    <div class="hero-slider" id="heroSlider">
        <?php if (empty($slides)): ?>
        <!-- Slide ilustrasi fallback jika belum ada data -->
        <?php
        $ilustrasi = [
            ['judul'=>'Baju Bodo Modern','sub'=>'Keanggunan tradisi Sulawesi dalam sentuhan modern','warna'=>'#2a1a05'],
            ['judul'=>'Tari Paduppa','sub'=>'Kostum penyambutan khas Sulawesi Selatan','warna'=>'#0d2a1a'],
            ['judul'=>'Kreasi Baru','sub'=>'Eksplorasi warna dan gerak tanpa batas','warna'=>'#1a0d2a'],
            ['judul'=>'4 Etnis Modern','sub'=>'Harmoni keberagaman dalam satu panggung','warna'=>'#2a1a1a'],
            ['judul'=>'Sanggar Talenta','sub'=>'Tempat bakat bersemi dan karya lahir','warna'=>'#1a1a2a'],
        ];
        foreach ($ilustrasi as $i => $il):
        ?>
        <div class="hero-slide <?php echo $i===0?'active':''; ?>" style="background:<?php echo $il['warna']; ?>;">
            <div class="hero-slide-ilust">
                <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <radialGradient id="g<?php echo $i;?>" cx="50%" cy="50%" r="50%">
                            <stop offset="0%" stop-color="#d4af37" stop-opacity="0.3"/>
                            <stop offset="100%" stop-color="#d4af37" stop-opacity="0"/>
                        </radialGradient>
                    </defs>
                    <rect width="400" height="300" fill="url(#g<?php echo $i;?>)"/>
                    <ellipse cx="200" cy="120" rx="60" ry="80" fill="#d4af37" opacity="0.15"/>
                    <ellipse cx="200" cy="240" rx="90" ry="50" fill="#d4af37" opacity="0.1"/>
                    <circle cx="200" cy="80" r="35" fill="#d4af37" opacity="0.2"/>
                    <text x="200" y="160" text-anchor="middle" font-family="serif" font-size="14" fill="#d4af37" opacity="0.5">✦ TALENTA ✦</text>
                </svg>
            </div>
            <div class="hero-slide-content">
                <p class="hero-slide-tag">Koleksi Kostum</p>
                <h2 class="hero-slide-title"><?php echo $il['judul']; ?></h2>
                <p class="hero-slide-sub"><?php echo $il['sub']; ?></p>
                <a href="kostum.php" class="hero-slide-btn">Lihat Koleksi</a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <?php foreach ($slides as $i => $sl): ?>
        <div class="hero-slide <?php echo $i===0?'active':''; ?>">
            <?php if (!empty($sl['gambar'])): ?>
            <div class="hero-slide-img" style="background-image:url('../gambar/slide/<?php echo htmlspecialchars($sl['gambar']); ?>')"></div>
            <?php endif; ?>
            <div class="hero-slide-content">
                <p class="hero-slide-tag">✦ Talenta Project ✦</p>
                <h2 class="hero-slide-title"><?php echo htmlspecialchars($sl['judul']); ?></h2>
                <?php if (!empty($sl['subjudul'])): ?>
                <p class="hero-slide-sub"><?php echo htmlspecialchars($sl['subjudul']); ?></p>
                <?php endif; ?>
                <a href="<?php echo htmlspecialchars($sl['link_btn'] ?: 'kostum.php'); ?>" class="hero-slide-btn">
                    <?php echo htmlspecialchars($sl['label_btn'] ?: 'Lihat Koleksi'); ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <!-- Dot navigasi -->
    <div class="hero-dots" id="heroDots">
        <?php $n = empty($slides) ? 5 : count($slides); ?>
        <?php for ($i=0;$i<$n;$i++): ?>
        <button class="hero-dot <?php echo $i===0?'active':''; ?>" data-index="<?php echo $i; ?>"></button>
        <?php endfor; ?>
    </div>
    <!-- Panah -->
    <button class="hero-arrow hero-arrow--prev" id="heroPrev">&#8249;</button>
    <button class="hero-arrow hero-arrow--next" id="heroNext">&#8250;</button>
    <!-- Overlay teks sanggar -->
    <div class="hero-brand-overlay">
        <span>✦ SANGGAR SENI ✦</span>
        <strong>TALENTA PROJECT</strong>
    </div>
</section>

<!-- ================================================================
     SECTION 2 — TENTANG SANGGAR (latar cream)
     ================================================================ -->
<section class="home-section bg-cream">
    <div class="home-container">
        <div class="about-wrap">
            <div class="about-text">
                <p class="section-eyebrow">Tentang Kami</p>
                <h2 class="section-heading"><?php echo htmlspecialchars(ps($ps,'tentang_judul','Sanggar Seni Talenta Project')); ?></h2>
                <p><?php echo nl2br(htmlspecialchars(ps($ps,'tentang_deskripsi'))); ?></p>
                <?php if (ps($ps,'tentang_deskripsi2')): ?>
                <p style="margin-top:12px;"><?php echo nl2br(htmlspecialchars(ps($ps,'tentang_deskripsi2'))); ?></p>
                <?php endif; ?>
                <div class="about-stats">
                    <div class="about-stat">
                        <span class="about-stat-n"><?php echo htmlspecialchars(ps($ps,'stat1_angka','10+')); ?></span>
                        <span><?php echo htmlspecialchars(ps($ps,'stat1_label','Tahun Berdiri')); ?></span>
                    </div>
                    <div class="about-stat">
                        <span class="about-stat-n"><?php echo htmlspecialchars(ps($ps,'stat2_angka','500+')); ?></span>
                        <span><?php echo htmlspecialchars(ps($ps,'stat2_label','Pementasan')); ?></span>
                    </div>
                    <div class="about-stat">
                        <span class="about-stat-n"><?php echo htmlspecialchars(ps($ps,'stat3_angka','200+')); ?></span>
                        <span><?php echo htmlspecialchars(ps($ps,'stat3_label','Koleksi Kostum')); ?></span>
                    </div>
                </div>
            </div>
            <div class="about-visual">
                <?php $about_img = ps_img($ps,'tentang_gambar'); ?>
                <?php if ($about_img): ?>
                <img src="../gambar/profil_sanggar/<?php echo htmlspecialchars($about_img); ?>"
                     alt="Tentang Sanggar" class="about-ilust">
                <?php else: ?>
                <svg viewBox="0 0 320 360" xmlns="http://www.w3.org/2000/svg" class="about-ilust">
                    <rect width="320" height="360" rx="16" fill="#f0e6cc"/>
                    <ellipse cx="160" cy="130" rx="70" ry="90" fill="#d4af37" opacity="0.2"/>
                    <ellipse cx="160" cy="290" rx="110" ry="55" fill="#d4af37" opacity="0.15"/>
                    <circle cx="160" cy="90" r="45" fill="#d4af37" opacity="0.25"/>
                    <text x="160" y="200" text-anchor="middle" font-family="serif" font-size="40" fill="#d4af37" opacity="0.4">♦</text>
                    <text x="160" y="240" text-anchor="middle" font-family="serif" font-size="13" fill="#b8962e">TALENTA PROJECT</text>
                    <text x="160" y="258" text-anchor="middle" font-family="sans-serif" font-size="10" fill="#999">Upload foto via Kelola Konten</text>
                </svg>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================================================================
     SECTION 3 — KOSTUM FAVORIT (latar putih)
     ================================================================ -->
<section class="home-section bg-white">
    <div class="home-container">
        <div class="section-header-center">
            <p class="section-eyebrow">Pilihan Terbaik</p>
            <h2 class="section-heading">Kostum <span>Favorit</span></h2>
            <p class="section-sub">Koleksi paling diminati — siap tampil memukau di panggung Anda</p>
        </div>
        <div class="kostum-home-grid">
            <?php if (mysqli_num_rows($favorit) === 0): ?>
            <p style="color:#888;grid-column:1/-1;text-align:center;padding:40px 0;">Belum ada kostum favorit yang ditandai.</p>
            <?php endif; ?>
            <?php
            // Reset pointer
            mysqli_data_seek($favorit, 0);
            while ($k = mysqli_fetch_assoc($favorit)):
            ?>
            <a href="sewa.php?id=<?php echo $k['id']; ?>" class="kostum-home-card">
                <div class="kostum-home-img-wrap">
                    <img src="../gambar/kostum/<?php echo htmlspecialchars($k['gambar']); ?>"
                         alt="<?php echo htmlspecialchars($k['nama_kostum']); ?>">
                    <span class="kostum-home-badge"><?php echo htmlspecialchars($k['kategori']); ?></span>
                </div>
                <div class="kostum-home-info">
                    <p class="kostum-home-name"><?php echo htmlspecialchars($k['nama_kostum']); ?></p>
                    <p class="kostum-home-price"><?php echo format_rupiah($k['harga']); ?><span>/hari</span></p>
                    <span class="kostum-home-cta">Sewa →</span>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
        <div style="text-align:center;margin-top:32px;">
            <a href="kostum.php" class="btn">Lihat Semua Koleksi</a>
        </div>
    </div>
</section>

<!-- ================================================================
     SECTION 4 — PELATIHAN (latar gelap gold)
     ================================================================ -->
<section class="home-section bg-dark">
    <div class="home-container">
        <div class="section-header-center">
            <p class="section-eyebrow" style="color:#d4af37;">Program Kami</p>
            <h2 class="section-heading" style="color:#fff;">Jadwal <span>Pelatihan</span></h2>
            <p class="section-sub" style="color:#bbb;">Bergabunglah bersama kami dan kembangkan bakat seni tari Anda</p>
        </div>
        <div class="pelatihan-grid">
            <?php if (mysqli_num_rows($pelatihan) === 0): ?>
            <!-- Ilustrasi placeholder -->
            <?php
            $plt_ilust = [
                ['nama'=>'Tari Tradisional Sulawesi','jadwal'=>'Setiap Sabtu 15.00–17.00','icon'=>'🎭','desc'=>'Pelatihan dasar hingga mahir tari khas Sulawesi Selatan.'],
                ['nama'=>'Tari Kreasi Modern','jadwal'=>'Setiap Minggu 09.00–11.00','icon'=>'💃','desc'=>'Koreografi kreasi modern untuk pertunjukan panggung.'],
                ['nama'=>'Tari Anak & Remaja','jadwal'=>'Setiap Jumat 15.00–16.30','icon'=>'⭐','desc'=>'Program khusus untuk anak usia 7–17 tahun.'],
                ['nama'=>'Workshop Intensif','jadwal'=>'Setiap bulan — 3 hari','icon'=>'🏆','desc'=>'Workshop intensif untuk persiapan pentas atau kompetisi.'],
            ];
            foreach ($plt_ilust as $pl):
            ?>
            <div class="pelatihan-card">
                <div class="pelatihan-icon"><?php echo $pl['icon']; ?></div>
                <h3><?php echo $pl['nama']; ?></h3>
                <p class="pelatihan-jadwal">🕐 <?php echo $pl['jadwal']; ?></p>
                <p><?php echo $pl['desc']; ?></p>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <?php mysqli_data_seek($pelatihan, 0); while ($p = mysqli_fetch_assoc($pelatihan)): ?>
            <div class="pelatihan-card">
                <?php if (!empty($p['gambar'])): ?>
                <img src="../gambar/pelatihan/<?php echo htmlspecialchars($p['gambar']); ?>"
                     alt="<?php echo htmlspecialchars($p['nama_pelatihan']); ?>" class="pelatihan-card-img">
                <?php else: ?>
                <div class="pelatihan-icon">🎭</div>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($p['nama_pelatihan']); ?></h3>
                <?php if (!empty($p['jadwal'])): ?>
                <p class="pelatihan-jadwal">🕐 <?php echo htmlspecialchars($p['jadwal']); ?></p>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($p['deskripsi']); ?></p>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>

<!-- ================================================================
     SECTION 5 — HASIL KARYA (latar cream, layout asimetris)
     ================================================================ -->
<section class="home-section bg-cream">
    <div class="home-container">
        <div class="section-header-center">
            <p class="section-eyebrow">Galeri</p>
            <h2 class="section-heading">Hasil Karya <span>Sanggar</span></h2>
            <p class="section-sub">Dokumentasi perjalanan karya dan kreativitas Talenta Project</p>
        </div>

        <?php if (empty($karya_rows)): ?>
        <!-- Placeholder layout galeri -->
        <div class="karya-layout">
            <div class="karya-featured">
                <div class="karya-ilust-big">
                    <svg viewBox="0 0 500 380" xmlns="http://www.w3.org/2000/svg" width="100%">
                        <rect width="500" height="380" fill="#e8dcc8"/>
                        <ellipse cx="250" cy="160" rx="100" ry="120" fill="#d4af37" opacity="0.2"/>
                        <circle cx="250" cy="120" r="60" fill="#d4af37" opacity="0.15"/>
                        <text x="250" y="230" text-anchor="middle" font-family="serif" font-size="18" fill="#b8962e" opacity="0.7">✦ Karya Terbaik ✦</text>
                        <text x="250" y="255" text-anchor="middle" font-family="sans-serif" font-size="12" fill="#999">Ganti dengan foto karya sanggar</text>
                    </svg>
                </div>
                <div class="karya-featured-info">
                    <span class="karya-tag">Karya Utama</span>
                    <h3>Pentas Budaya Nusantara 2025</h3>
                    <p>Penampilan kolosal memadukan tari tradisional dari 4 suku Sulawesi dalam satu panggung megah — dihadiri lebih dari 2.000 penonton.</p>
                </div>
            </div>
            <div class="karya-small-grid">
                <?php
                $karya_placeholder = [
                    ['j'=>'Tari Padduppa Festival','w'=>'#ddd0b8'],
                    ['j'=>'Kreasi Tari Modern','w'=>'#d8e8cc'],
                    ['j'=>'Pentas Anak Berbakat','w'=>'#ccd8e8'],
                    ['j'=>'Workshop Koreografi','w'=>'#e8cccc'],
                    ['j'=>'Pentas Akhir Tahun','w'=>'#e8e0cc'],
                ];
                foreach ($karya_placeholder as $kp):
                ?>
                <div class="karya-small-card">
                    <div class="karya-small-img" style="background:<?php echo $kp['w']; ?>;">
                        <svg viewBox="0 0 200 160" xmlns="http://www.w3.org/2000/svg" width="100%">
                            <rect width="200" height="160" fill="<?php echo $kp['w']; ?>"/>
                            <text x="100" y="88" text-anchor="middle" font-size="28" fill="#b8962e" opacity="0.4">♦</text>
                        </svg>
                    </div>
                    <p><?php echo $kp['j']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="karya-layout">
            <!-- Karya featured = item pertama -->
            <div class="karya-featured">
                <div class="karya-ilust-big">
                    <?php if (!empty($karya_rows[0]['gambar'])): ?>
                    <img src="../gambar/karya/<?php echo htmlspecialchars($karya_rows[0]['gambar']); ?>"
                         alt="<?php echo htmlspecialchars($karya_rows[0]['judul']); ?>">
                    <?php else: ?>
                    <div class="karya-no-img">♦</div>
                    <?php endif; ?>
                </div>
                <div class="karya-featured-info">
                    <span class="karya-tag">Karya Unggulan</span>
                    <h3><?php echo htmlspecialchars($karya_rows[0]['judul']); ?></h3>
                    <p><?php echo htmlspecialchars($karya_rows[0]['deskripsi']); ?></p>
                </div>
            </div>
            <!-- Sisa karya = grid kecil -->
            <div class="karya-small-grid">
                <?php for ($i=1; $i<count($karya_rows); $i++): $h=$karya_rows[$i]; ?>
                <div class="karya-small-card">
                    <div class="karya-small-img">
                        <?php if (!empty($h['gambar'])): ?>
                        <img src="../gambar/karya/<?php echo htmlspecialchars($h['gambar']); ?>"
                             alt="<?php echo htmlspecialchars($h['judul']); ?>">
                        <?php else: ?>
                        <div class="karya-no-img">♦</div>
                        <?php endif; ?>
                    </div>
                    <p><?php echo htmlspecialchars($h['judul']); ?></p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================================================================
     SECTION 6 — EVENT & PRESTASI (latar putih, layout majalah)
     ================================================================ -->
<section class="home-section bg-white">
    <div class="home-container">
        <div class="section-header-center">
            <p class="section-eyebrow">Jejak Prestasi</p>
            <h2 class="section-heading">Event &amp; <span>Prestasi</span></h2>
            <p class="section-sub">Rekam jejak keberhasilan dan momen bersejarah Talenta Project</p>
        </div>

        <?php if (empty($event_rows)): ?>
        <div class="event-layout">
            <div class="event-featured">
                <div class="event-featured-img">
                    <svg viewBox="0 0 560 340" xmlns="http://www.w3.org/2000/svg" width="100%">
                        <rect width="560" height="340" fill="#2a1a05"/>
                        <circle cx="280" cy="140" r="90" fill="#d4af37" opacity="0.1"/>
                        <text x="280" y="130" text-anchor="middle" font-family="serif" font-size="48" fill="#d4af37" opacity="0.3">🏆</text>
                        <text x="280" y="175" text-anchor="middle" font-family="serif" font-size="18" fill="#d4af37" opacity="0.6">Event Unggulan</text>
                        <text x="280" y="198" text-anchor="middle" font-family="sans-serif" font-size="11" fill="#888">Ganti dengan foto event</text>
                    </svg>
                </div>
                <div class="event-featured-info">
                    <span class="event-date-badge">2025</span>
                    <h3>Festival Budaya Nusantara — Juara I</h3>
                    <p>Talenta Project meraih Juara I kategori Tari Tradisional Modifikasi pada Festival Budaya Nusantara yang diikuti 38 sanggar dari seluruh Indonesia.</p>
                    <div class="event-meta">🏆 Penghargaan Nasional &nbsp;·&nbsp; 📍 Jakarta</div>
                </div>
            </div>
            <div class="event-side-list">
                <?php
                $ev_pl = [
                    ['j'=>'Pentas HUT Kemerdekaan','t'=>'17 Agustus 2024','icon'=>'🎉'],
                    ['j'=>'Juara II Tari Kreasi Daerah','t'=>'Maret 2024','icon'=>'🥈'],
                    ['j'=>'Kolaborasi Dinas Budaya','t'=>'Oktober 2023','icon'=>'🤝'],
                    ['j'=>'Festival Seni Pelajar','t'=>'Juli 2023','icon'=>'🎭'],
                ];
                foreach ($ev_pl as $ep):
                ?>
                <div class="event-side-item">
                    <span class="event-side-icon"><?php echo $ep['icon']; ?></span>
                    <div>
                        <p class="event-side-title"><?php echo $ep['j']; ?></p>
                        <p class="event-side-date"><?php echo $ep['t']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="event-layout">
            <div class="event-featured">
                <div class="event-featured-img">
                    <?php if (!empty($event_rows[0]['gambar'])): ?>
                    <img src="../gambar/event/<?php echo htmlspecialchars($event_rows[0]['gambar']); ?>"
                         alt="<?php echo htmlspecialchars($event_rows[0]['judul']); ?>">
                    <?php else: ?>
                    <div class="event-no-img">🏆</div>
                    <?php endif; ?>
                </div>
                <div class="event-featured-info">
                    <?php if (!empty($event_rows[0]['tanggal'])): ?>
                    <span class="event-date-badge"><?php echo date('Y', strtotime($event_rows[0]['tanggal'])); ?></span>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($event_rows[0]['judul']); ?></h3>
                    <p><?php echo htmlspecialchars($event_rows[0]['deskripsi']); ?></p>
                    <?php if (!empty($event_rows[0]['tanggal'])): ?>
                    <div class="event-meta">📅 <?php echo date('d F Y', strtotime($event_rows[0]['tanggal'])); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="event-side-list">
                <?php for ($i=1; $i<count($event_rows); $i++): $ev=$event_rows[$i]; ?>
                <div class="event-side-item">
                    <span class="event-side-icon">🏆</span>
                    <div>
                        <p class="event-side-title"><?php echo htmlspecialchars($ev['judul']); ?></p>
                        <?php if (!empty($ev['tanggal'])): ?>
                        <p class="event-side-date"><?php echo date('d M Y', strtotime($ev['tanggal'])); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ================================================================
     SECTION 7 — PROFIL TIM / PENDIRI (latar cream gelap)
     ================================================================ -->
<section class="home-section bg-dark-cream">
    <div class="home-container">
        <div class="section-header-center">
            <p class="section-eyebrow" style="color:#d4af37;">Keluarga Kami</p>
            <h2 class="section-heading" style="color:#fff;">Tim &amp; <span>Pendiri</span></h2>
            <p class="section-sub" style="color:#bbb;">Orang-orang di balik setiap penampilan memukau Talenta Project</p>
        </div>
        <div class="tim-grid">
            <?php if (empty($tim_rows)): ?>
            <!-- Placeholder jika belum ada data tim -->
            <div class="tim-card tim-card--founder" style="grid-column:1/-1;">
                <div class="tim-avatar tim-avatar--founder">
                    <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="60" cy="60" r="60" fill="#2a1a05"/>
                        <circle cx="60" cy="48" r="22" fill="#d4af37" opacity="0.4"/>
                        <ellipse cx="60" cy="95" rx="32" ry="20" fill="#d4af37" opacity="0.3"/>
                        <text x="60" y="54" text-anchor="middle" font-size="22" fill="#d4af37" opacity="0.5">👤</text>
                    </svg>
                </div>
                <div class="tim-info">
                    <span class="tim-role">Pendiri &amp; Ketua Sanggar</span>
                    <h3>Nama Pendiri</h3>
                    <p>Tambahkan profil tim dari menu <strong>Kelola Konten → Tim &amp; Pendiri</strong> di dashboard admin.</p>
                </div>
            </div>
            <?php else: ?>
            <?php foreach ($tim_rows as $t): ?>
            <?php if ($t['is_founder']): ?>
            <!-- Pendiri — card full width -->
            <div class="tim-card tim-card--founder">
                <div class="tim-avatar tim-avatar--founder">
                    <?php if (!empty($t['foto'])): ?>
                    <img src="../gambar/tim/<?php echo htmlspecialchars($t['foto']); ?>"
                         alt="<?php echo htmlspecialchars($t['nama']); ?>"
                         style="width:100%;height:100%;object-fit:cover;display:block;">
                    <?php else: ?>
                    <svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="60" cy="60" r="60" fill="#2a1a05"/>
                        <circle cx="60" cy="48" r="22" fill="#d4af37" opacity="0.5"/>
                        <ellipse cx="60" cy="95" rx="32" ry="20" fill="#d4af37" opacity="0.4"/>
                        <text x="60" y="54" text-anchor="middle" font-size="22" fill="#d4af37" opacity="0.6">👤</text>
                    </svg>
                    <?php endif; ?>
                </div>
                <div class="tim-info">
                    <span class="tim-role"><?php echo htmlspecialchars($t['peran']); ?></span>
                    <h3><?php echo htmlspecialchars($t['nama']); ?></h3>
                    <?php if (!empty($t['bio'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($t['bio'])); ?></p>
                    <?php endif; ?>
                    <div class="tim-sosmed">
                        <?php if (!empty($t['link_wa'])): ?>
                        <a href="<?php echo htmlspecialchars($t['link_wa']); ?>" target="_blank" class="tim-sosmed-btn tim-wa">WhatsApp</a>
                        <?php endif; ?>
                        <?php if (!empty($t['link_ig'])): ?>
                        <a href="<?php echo htmlspecialchars($t['link_ig']); ?>" target="_blank" class="tim-sosmed-btn tim-ig">Instagram</a>
                        <?php endif; ?>
                        <?php if (!empty($t['link_tiktok'])): ?>
                        <a href="<?php echo htmlspecialchars($t['link_tiktok']); ?>" target="_blank" class="tim-sosmed-btn footer-tiktok">TikTok</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Anggota tim biasa -->
            <div class="tim-card">
                <div class="tim-avatar">
                    <?php if (!empty($t['foto'])): ?>
                    <img src="../gambar/tim/<?php echo htmlspecialchars($t['foto']); ?>"
                         alt="<?php echo htmlspecialchars($t['nama']); ?>"
                         style="width:100%;height:100%;object-fit:cover;display:block;">
                    <?php else: ?>
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="50" fill="#1a1a1a"/>
                        <circle cx="50" cy="38" r="18" fill="#d4af37" opacity="0.4"/>
                        <ellipse cx="50" cy="80" rx="28" ry="16" fill="#d4af37" opacity="0.3"/>
                        <text x="50" y="44" text-anchor="middle" font-size="20" fill="#d4af37" opacity="0.5">👤</text>
                    </svg>
                    <?php endif; ?>
                </div>
                <span class="tim-role"><?php echo htmlspecialchars($t['peran']); ?></span>
                <h3><?php echo htmlspecialchars($t['nama']); ?></h3>
                <?php if (!empty($t['bio'])): ?>
                <p><?php echo nl2br(htmlspecialchars(mb_substr($t['bio'],0,100))); ?></p>
                <?php endif; ?>
                <?php if (!empty($t['link_ig']) || !empty($t['link_wa'])): ?>
                <div class="tim-sosmed" style="justify-content:center;margin-top:12px;">
                    <?php if (!empty($t['link_wa'])): ?>
                    <a href="<?php echo htmlspecialchars($t['link_wa']); ?>" target="_blank" class="tim-sosmed-btn tim-wa" style="font-size:0.62rem;padding:5px 10px;">WA</a>
                    <?php endif; ?>
                    <?php if (!empty($t['link_ig'])): ?>
                    <a href="<?php echo htmlspecialchars($t['link_ig']); ?>" target="_blank" class="tim-sosmed-btn tim-ig" style="font-size:0.62rem;padding:5px 10px;">IG</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ================================================================
     SECTION 8 — CTA SEWA (banner full)
     ================================================================ -->
<section class="home-cta-banner">
    <div class="home-cta-inner">
        <p class="section-eyebrow" style="color:#d4af37;">Siap Tampil?</p>
        <h2 style="color:#fff;font-family:'Cinzel',serif;font-size:clamp(1.4rem,3vw,2.2rem);letter-spacing:3px;margin:8px 0 16px;">
            <?php echo htmlspecialchars(ps($ps,'cta_judul','Temukan Kostum Impian Anda')); ?>
        </h2>
        <p style="color:#ccc;max-width:520px;margin:0 auto 28px;font-size:0.95rem;line-height:1.8;">
            <?php echo nl2br(htmlspecialchars(ps($ps,'cta_deskripsi','Lebih dari 200 koleksi kostum siap menemani penampilan terbaik Anda.'))); ?>
        </p>
        <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;">
            <a href="kostum.php" class="btn">Lihat Semua Kostum</a>
            <a href="<?php echo isset($_SESSION['username']) ? 'riwayat.php' : 'login.php'; ?>"
               class="btn btn-outline-white">
                <?php echo isset($_SESSION['username']) ? 'Riwayat Sewa Saya' : 'Login untuk Menyewa'; ?>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
