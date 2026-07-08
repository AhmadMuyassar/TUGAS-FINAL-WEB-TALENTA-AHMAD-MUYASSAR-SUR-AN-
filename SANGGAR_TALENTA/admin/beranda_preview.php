<?php
// File ini di-include dari admin/dashboard.php sebagai preview beranda.
// Semua variabel ($slides, $favorit, $pelatihan, $karya_rows, $event_rows)
// sudah disiapkan oleh pemanggil. $root = '../' juga sudah diset.
if (!defined('ADMIN_PREVIEW')) { http_response_code(403); exit('Direct access not allowed.'); }

// Tambahkan wrapper border agar terlihat sebagai "preview"
?>
<div class="admin-preview-wrap">
    <!-- Label edit cepat -->
    <div class="admin-preview-editbar">
        <a href="kelola_konten.php?tab=slide">âœ Edit Slide</a>
        <a href="kelola_kostum.php">âœ Edit Kostum Favorit</a>
        <a href="kelola_konten.php?tab=pelatihan">âœ Edit Pelatihan</a>
        <a href="kelola_konten.php?tab=karya">âœ Edit Karya</a>
        <a href="kelola_konten.php?tab=event">âœ Edit Event</a>
    </div>

<!-- HERO SLIDE PREVIEW -->
<section class="hero-slide-section" style="pointer-events:none;">
    <div class="hero-slider" id="heroSliderPreview">
        <?php if (empty($slides)): ?>
        <?php
        $ilustrasi = [
            ['judul'=>'Baju Bodo Modern','sub'=>'Keanggunan tradisi Sulawesi','warna'=>'#2a1a05'],
            ['judul'=>'Tari Paduppa','sub'=>'Kostum penyambutan khas Sulawesi','warna'=>'#0d2a1a'],
            ['judul'=>'Kreasi Baru','sub'=>'Eksplorasi warna dan gerak','warna'=>'#1a0d2a'],
        ];
        foreach ($ilustrasi as $i => $il):
        ?>
        <div class="hero-slide <?php echo $i===0?'active':''; ?>" style="background:<?php echo $il['warna']; ?>;">
            <div class="hero-slide-content">
                <p class="hero-slide-tag">âœ¦ Belum ada slide aktif âœ¦</p>
                <h2 class="hero-slide-title"><?php echo $il['judul']; ?></h2>
                <p class="hero-slide-sub"><?php echo $il['sub']; ?></p>
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
                <p class="hero-slide-tag">âœ¦ Talenta Project âœ¦</p>
                <h2 class="hero-slide-title"><?php echo htmlspecialchars($sl['judul']); ?></h2>
                <?php if (!empty($sl['subjudul'])): ?>
                <p class="hero-slide-sub"><?php echo htmlspecialchars($sl['subjudul']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="hero-dots" id="heroDotsPreview">
        <?php $n = empty($slides) ? 3 : count($slides); ?>
        <?php for ($i=0;$i<$n;$i++): ?>
        <button class="hero-dot <?php echo $i===0?'active':''; ?>" data-slider="preview" data-index="<?php echo $i; ?>"></button>
        <?php endfor; ?>
    </div>
</section>

<!-- KOSTUM FAVORIT PREVIEW -->
<section class="home-section bg-white">
    <div class="home-container">
        <div class="section-header-center">
            <p class="section-eyebrow">Pilihan Terbaik</p>
            <h2 class="section-heading">Kostum <span>Favorit</span></h2>
        </div>
        <div class="kostum-home-grid">
            <?php mysqli_data_seek($favorit, 0); while ($k = mysqli_fetch_assoc($favorit)): ?>
            <div class="kostum-home-card" style="pointer-events:none;">
                <div class="kostum-home-img-wrap">
                    <img src="../gambar/kostum/<?php echo htmlspecialchars($k['gambar']); ?>" alt="">
                    <span class="kostum-home-badge"><?php echo htmlspecialchars($k['kategori']); ?></span>
                </div>
                <div class="kostum-home-info">
                    <p class="kostum-home-name"><?php echo htmlspecialchars($k['nama_kostum']); ?></p>
                    <p class="kostum-home-price"><?php echo format_rupiah($k['harga']); ?><span>/hari</span></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- PELATIHAN PREVIEW -->
<section class="home-section bg-dark">
    <div class="home-container">
        <div class="section-header-center">
            <h2 class="section-heading" style="color:#fff;">Jadwal <span>Pelatihan</span></h2>
        </div>
        <div class="pelatihan-grid">
            <?php if (mysqli_num_rows($pelatihan) === 0): ?>
            <div class="pelatihan-card"><div class="pelatihan-icon">ðŸŽ­</div><h3>Belum ada pelatihan</h3><p>Tambahkan dari Kelola Konten.</p></div>
            <?php else: mysqli_data_seek($pelatihan,0); while ($p = mysqli_fetch_assoc($pelatihan)): ?>
            <div class="pelatihan-card">
                <?php if (!empty($p['gambar'])): ?>
                <img src="../gambar/pelatihan/<?php echo htmlspecialchars($p['gambar']); ?>" class="pelatihan-card-img">
                <?php else: ?><div class="pelatihan-icon">ðŸŽ­</div><?php endif; ?>
                <h3><?php echo htmlspecialchars($p['nama_pelatihan']); ?></h3>
                <?php if (!empty($p['jadwal'])): ?><p class="pelatihan-jadwal">ðŸ• <?php echo htmlspecialchars($p['jadwal']); ?></p><?php endif; ?>
                <p><?php echo htmlspecialchars(mb_substr($p['deskripsi'],0,80)); ?></p>
            </div>
            <?php endwhile; endif; ?>
        </div>
    </div>
</section>

<!-- KARYA PREVIEW -->
<section class="home-section bg-cream">
    <div class="home-container">
        <div class="section-header-center">
            <h2 class="section-heading">Hasil Karya <span>Sanggar</span></h2>
        </div>
        <?php if (!empty($karya_rows)): ?>
        <div class="karya-layout">
            <div class="karya-featured">
                <div class="karya-ilust-big">
                    <?php if (!empty($karya_rows[0]['gambar'])): ?>
                    <img src="../gambar/karya/<?php echo htmlspecialchars($karya_rows[0]['gambar']); ?>" alt="">
                    <?php else: ?><div class="karya-no-img">â™¦</div><?php endif; ?>
                </div>
                <div class="karya-featured-info">
                    <span class="karya-tag">Karya Unggulan</span>
                    <h3><?php echo htmlspecialchars($karya_rows[0]['judul']); ?></h3>
                    <p><?php echo htmlspecialchars(mb_substr($karya_rows[0]['deskripsi'],0,120)); ?></p>
                </div>
            </div>
            <div class="karya-small-grid">
                <?php for ($i=1;$i<count($karya_rows);$i++): $h=$karya_rows[$i]; ?>
                <div class="karya-small-card">
                    <div class="karya-small-img">
                        <?php if (!empty($h['gambar'])): ?>
                        <img src="../gambar/karya/<?php echo htmlspecialchars($h['gambar']); ?>" alt="">
                        <?php else: ?><div class="karya-no-img">â™¦</div><?php endif; ?>
                    </div>
                    <p><?php echo htmlspecialchars($h['judul']); ?></p>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php else: ?>
        <p style="text-align:center;color:#888;">Belum ada karya. Tambahkan dari Kelola Konten â†’ Hasil Karya.</p>
        <?php endif; ?>
    </div>
</section>

<!-- EVENT PREVIEW -->
<section class="home-section bg-white">
    <div class="home-container">
        <div class="section-header-center">
            <h2 class="section-heading">Event &amp; <span>Prestasi</span></h2>
        </div>
        <?php if (!empty($event_rows)): ?>
        <div class="event-layout">
            <div class="event-featured">
                <div class="event-featured-img">
                    <?php if (!empty($event_rows[0]['gambar'])): ?>
                    <img src="../gambar/event/<?php echo htmlspecialchars($event_rows[0]['gambar']); ?>" alt="">
                    <?php else: ?><div class="event-no-img"></div><?php endif; ?>
                </div>
                <div class="event-featured-info">
                    <h3><?php echo htmlspecialchars($event_rows[0]['judul']); ?></h3>
                    <p><?php echo htmlspecialchars(mb_substr($event_rows[0]['deskripsi'],0,120)); ?></p>
                </div>
            </div>
            <div class="event-side-list">
                <?php for ($i=1;$i<count($event_rows);$i++): $ev=$event_rows[$i]; ?>
                <div class="event-side-item">
                    <span class="event-side-icon"></span>
                    <div>
                        <p class="event-side-title"><?php echo htmlspecialchars($ev['judul']); ?></p>
                        <?php if (!empty($ev['tanggal'])): ?><p class="event-side-date"><?php echo date('d M Y',strtotime($ev['tanggal'])); ?></p><?php endif; ?>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <?php else: ?>
        <p style="text-align:center;color:#888;">Belum ada event. Tambahkan dari Kelola Konten â†’ Event & Prestasi.</p>
        <?php endif; ?>
    </div>
</section>

</div><!-- /.admin-preview-wrap -->

