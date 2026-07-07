<?php
// =====================================================================
// admin/kelola_konten.php — Kelola semua konten beranda:
// Hero Slide, Profil Sanggar, Tim & Pendiri, Pelatihan, Hasil Karya, Event/Prestasi
// =====================================================================
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_admin();

// Pastikan semua tabel ada
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hero_slide (id INT(11) PRIMARY KEY AUTO_INCREMENT,judul VARCHAR(100) NOT NULL,subjudul VARCHAR(200),gambar VARCHAR(255),link_btn VARCHAR(255) DEFAULT '',label_btn VARCHAR(50) DEFAULT 'Lihat Koleksi',urutan INT(11) DEFAULT 0,aktif TINYINT(1) DEFAULT 1)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS profil_sanggar (id INT(11) PRIMARY KEY AUTO_INCREMENT,kunci VARCHAR(80) NOT NULL UNIQUE,nilai TEXT,gambar VARCHAR(255))");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tim_sanggar (id INT(11) PRIMARY KEY AUTO_INCREMENT,nama VARCHAR(100) NOT NULL,peran VARCHAR(100) NOT NULL,bio TEXT,foto VARCHAR(255),link_wa VARCHAR(255),link_ig VARCHAR(255),link_tiktok VARCHAR(255),urutan INT(11) DEFAULT 0,is_founder TINYINT(1) DEFAULT 0)");
if (!is_dir(__DIR__ . '/../gambar/tim'))             mkdir(__DIR__ . '/../gambar/tim', 0755, true);
if (!is_dir(__DIR__ . '/../gambar/profil_sanggar'))  mkdir(__DIR__ . '/../gambar/profil_sanggar', 0755, true);

$tab  = $_GET['tab']  ?? 'slide';
$aksi = $_GET['aksi'] ?? '';

// ================================================================
// Konfigurasi konten biasa (non-slide)
// ================================================================
$config = [
    'pelatihan' => ['tabel'=>'pelatihan',      'folder'=>'pelatihan', 'label'=>'Pelatihan'],
    'karya'     => ['tabel'=>'hasil_karya',    'folder'=>'karya',     'label'=>'Hasil Karya'],
    'event'     => ['tabel'=>'event_prestasi', 'folder'=>'event',     'label'=>'Event & Prestasi'],
];

// ================================================================
// PROSES — HERO SLIDE
// ================================================================
if ($tab === 'slide') {

    // Simpan slide baru / edit
    if ($aksi === 'simpan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id        = (int)($_POST['id'] ?? 0);
        $judul     = trim($_POST['judul'] ?? '');
        $subjudul  = trim($_POST['subjudul'] ?? '');
        $link_btn  = trim($_POST['link_btn'] ?? '');
        $label_btn = trim($_POST['label_btn'] ?? 'Lihat Koleksi');
        $urutan    = (int)($_POST['urutan'] ?? 0);
        $aktif     = isset($_POST['aktif']) ? 1 : 0;
        $gambar_lama = trim($_POST['gambar_lama'] ?? '');

        $gambar = $gambar_lama;
        if (!empty($_FILES['gambar']['name'])) {
            $hasil = upload_gambar('gambar', 'slide', $gambar_lama);
            if (is_array($hasil)) {
                header("location:kelola_konten.php?tab=slide&error=" . urlencode($hasil['error']));
                exit();
            }
            $gambar = $hasil;
        }

        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE hero_slide SET judul=?,subjudul=?,gambar=?,link_btn=?,label_btn=?,urutan=?,aktif=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssssiii", $judul,$subjudul,$gambar,$link_btn,$label_btn,$urutan,$aktif,$id);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO hero_slide (judul,subjudul,gambar,link_btn,label_btn,urutan,aktif) VALUES (?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sssssii", $judul,$subjudul,$gambar,$link_btn,$label_btn,$urutan,$aktif);
        }
        mysqli_stmt_execute($stmt);
        header("location:kelola_konten.php?tab=slide&ok=1");
        exit();
    }

    // Toggle aktif/nonaktif
    if ($aksi === 'toggle') {
        $id = (int)($_GET['id'] ?? 0);
        mysqli_query($conn, "UPDATE hero_slide SET aktif = 1 - aktif WHERE id=$id");
        header("location:kelola_konten.php?tab=slide");
        exit();
    }

    // Hapus slide
    if ($aksi === 'hapus') {
        $id = (int)($_GET['id'] ?? 0);
        $row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar FROM hero_slide WHERE id=$id"));
        if ($row && $row['gambar']) {
            $f = __DIR__ . '/../gambar/slide/' . $row['gambar'];
            if (file_exists($f)) unlink($f);
        }
        $stmt = mysqli_prepare($conn, "DELETE FROM hero_slide WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        header("location:kelola_konten.php?tab=slide");
        exit();
    }
}

// ================================================================
// PROSES — KONTEN BIASA (pelatihan / karya / event)
// ================================================================
if (in_array($tab, ['pelatihan','karya','event'])) {
    $tabel       = $config[$tab]['tabel'];
    $folderGambar= $config[$tab]['folder'];

    // Simpan baru / edit
    if ($aksi === 'simpan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id          = (int)($_POST['id'] ?? 0);
        $judul       = trim($_POST['judul'] ?? '');
        $deskripsi   = trim($_POST['deskripsi'] ?? '');
        $gambar_lama = trim($_POST['gambar_lama'] ?? '');

        $gambar = $gambar_lama;
        if (!empty($_FILES['gambar']['name'])) {
            $hasil = upload_gambar('gambar', $folderGambar, $gambar_lama);
            if (is_array($hasil)) {
                header("location:kelola_konten.php?tab=$tab&error=" . urlencode($hasil['error']));
                exit();
            }
            $gambar = $hasil;
        }

        if ($tab === 'pelatihan') {
            $jadwal = trim($_POST['jadwal'] ?? '');
            if ($id > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE pelatihan SET nama_pelatihan=?,deskripsi=?,jadwal=?,gambar=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "ssssi", $judul,$deskripsi,$jadwal,$gambar,$id);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO pelatihan (nama_pelatihan,deskripsi,jadwal,gambar) VALUES (?,?,?,?)");
                mysqli_stmt_bind_param($stmt, "ssss", $judul,$deskripsi,$jadwal,$gambar);
            }
        } elseif ($tab === 'karya') {
            if ($id > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE hasil_karya SET judul=?,deskripsi=?,gambar=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "sssi", $judul,$deskripsi,$gambar,$id);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO hasil_karya (judul,deskripsi,gambar) VALUES (?,?,?)");
                mysqli_stmt_bind_param($stmt, "sss", $judul,$deskripsi,$gambar);
            }
        } else { // event
            $tanggal = trim($_POST['tanggal'] ?? '');
            if ($id > 0) {
                $stmt = mysqli_prepare($conn, "UPDATE event_prestasi SET judul=?,deskripsi=?,tanggal=?,gambar=? WHERE id=?");
                mysqli_stmt_bind_param($stmt, "ssssi", $judul,$deskripsi,$tanggal,$gambar,$id);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO event_prestasi (judul,deskripsi,tanggal,gambar) VALUES (?,?,?,?)");
                mysqli_stmt_bind_param($stmt, "ssss", $judul,$deskripsi,$tanggal,$gambar);
            }
        }
        mysqli_stmt_execute($stmt);
        header("location:kelola_konten.php?tab=$tab&ok=1");
        exit();
    }

    // Hapus
    if ($aksi === 'hapus') {
        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "DELETE FROM $tabel WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        header("location:kelola_konten.php?tab=$tab");
        exit();
    }
}

// ================================================================
// PROSES — PROFIL SANGGAR (key-value update)
// ================================================================
if ($tab === 'profil' && $aksi === 'simpan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['tentang_judul','tentang_deskripsi','tentang_deskripsi2',
               'stat1_angka','stat1_label','stat2_angka','stat2_label','stat3_angka','stat3_label',
               'cta_judul','cta_deskripsi'];
    foreach ($fields as $k) {
        $v = trim($_POST[$k] ?? '');
        $stmt = mysqli_prepare($conn, "INSERT INTO profil_sanggar (kunci,nilai,gambar) VALUES (?,?,'')
                                       ON DUPLICATE KEY UPDATE nilai=?");
        mysqli_stmt_bind_param($stmt, "sss", $k, $v, $v);
        mysqli_stmt_execute($stmt);
    }
    // Upload gambar tentang sanggar
    if (!empty($_FILES['tentang_gambar']['name'])) {
        $lama_img = trim($_POST['tentang_gambar_lama'] ?? '');
        $h = upload_gambar('tentang_gambar', 'profil_sanggar', $lama_img);
        if (!is_array($h)) {
            $stmt2 = mysqli_prepare($conn, "INSERT INTO profil_sanggar (kunci,nilai,gambar) VALUES ('tentang_gambar','',?)
                                            ON DUPLICATE KEY UPDATE gambar=?");
            mysqli_stmt_bind_param($stmt2, "ss", $h, $h);
            mysqli_stmt_execute($stmt2);
        }
    }
    header("location:kelola_konten.php?tab=profil&ok=1");
    exit();
}

// ================================================================
// PROSES — TIM SANGGAR
// ================================================================
if ($tab === 'tim') {
    if ($aksi === 'simpan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id         = (int)($_POST['id'] ?? 0);
        $nama       = trim($_POST['nama'] ?? '');
        $peran      = trim($_POST['peran'] ?? '');
        $bio        = trim($_POST['bio'] ?? '');
        $link_wa    = trim($_POST['link_wa'] ?? '');
        $link_ig    = trim($_POST['link_ig'] ?? '');
        $link_tiktok= trim($_POST['link_tiktok'] ?? '');
        $urutan     = (int)($_POST['urutan'] ?? 0);
        $is_founder = isset($_POST['is_founder']) ? 1 : 0;
        $foto_lama  = trim($_POST['foto_lama'] ?? '');

        $foto = $foto_lama;
        if (!empty($_FILES['foto']['name'])) {
            $h = upload_gambar('foto', 'tim', $foto_lama);
            if (is_array($h)) { header("location:kelola_konten.php?tab=tim&error=".urlencode($h['error'])); exit(); }
            $foto = $h;
        }
        if ($id > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE tim_sanggar SET nama=?,peran=?,bio=?,foto=?,link_wa=?,link_ig=?,link_tiktok=?,urutan=?,is_founder=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssssssiii", $nama,$peran,$bio,$foto,$link_wa,$link_ig,$link_tiktok,$urutan,$is_founder,$id);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO tim_sanggar (nama,peran,bio,foto,link_wa,link_ig,link_tiktok,urutan,is_founder) VALUES (?,?,?,?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, "sssssssii", $nama,$peran,$bio,$foto,$link_wa,$link_ig,$link_tiktok,$urutan,$is_founder);
        }
        mysqli_stmt_execute($stmt);
        header("location:kelola_konten.php?tab=tim&ok=1");
        exit();
    }
    if ($aksi === 'hapus') {
        $id = (int)($_GET['id'] ?? 0);
        $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT foto FROM tim_sanggar WHERE id=$id"));
        if ($r && $r['foto'] && file_exists(__DIR__.'/../gambar/tim/'.$r['foto'])) unlink(__DIR__.'/../gambar/tim/'.$r['foto']);
        $stmt = mysqli_prepare($conn, "DELETE FROM tim_sanggar WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        header("location:kelola_konten.php?tab=tim");
        exit();
    }
}

// ================================================================
// DATA UNTUK TAMPIL
// ================================================================
$dataEdit = null;
if ($aksi === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($tab === 'slide') {
        $dataEdit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hero_slide WHERE id=$id"));
    } elseif ($tab === 'tim') {
        $dataEdit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tim_sanggar WHERE id=$id"));
    } else {
        $t = $config[$tab]['tabel'];
        $dataEdit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $t WHERE id=$id"));
    }
}

// Data profil sanggar (key-value)
$ps_raw = mysqli_query($conn, "SELECT kunci,nilai,gambar FROM profil_sanggar");
$ps = [];
while ($r = mysqli_fetch_assoc($ps_raw)) $ps[$r['kunci']] = $r;
function ps_val($ps, $k, $d='') { return isset($ps[$k]) ? $ps[$k]['nilai'] : $d; }
function ps_img_val($ps, $k) { return isset($ps[$k]) ? $ps[$k]['gambar'] : ''; }

$root = '../';
$page_title = 'Kelola Konten';
require_once __DIR__ . '/../includes/header.php';

// Query daftar data
if ($tab === 'slide') {
    $daftar = mysqli_query($conn, "SELECT * FROM hero_slide ORDER BY urutan ASC, id ASC");
} elseif ($tab === 'tim') {
    $daftar = mysqli_query($conn, "SELECT * FROM tim_sanggar ORDER BY is_founder DESC, urutan ASC, id ASC");
} elseif (isset($config[$tab])) {
    $t = $config[$tab]['tabel'];
    $daftar = mysqli_query($conn, "SELECT * FROM $t ORDER BY id DESC");
} else {
    $daftar = null;
}
?>

<div class="container">
    <div class="admin-toolbar">
        <h2 style="margin:0;border:none;padding:0;">Kelola Konten Beranda</h2>
        <a href="<?php echo $root; ?>pengguna/dashboard.php" target="_blank" class="btn-sm btn-secondary">
            👁 Preview Beranda
        </a>
    </div>

    <!-- ===== TAB NAVIGASI ===== -->
    <div class="konten-tabs">
        <?php
        $tabs = [
            'slide'     => '🎞 Hero Slide',
            'profil'    => '🏠 Profil Sanggar',
            'tim'       => '👥 Tim & Pendiri',
            'pelatihan' => '🎭 Pelatihan',
            'karya'     => '🖼 Hasil Karya',
            'event'     => '🏆 Event & Prestasi',
        ];
        foreach ($tabs as $k=>$v):
        ?>
        <a href="kelola_konten.php?tab=<?php echo $k; ?>"
           class="konten-tab <?php echo $tab===$k?'konten-tab--active':''; ?>">
            <?php echo $v; ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_GET['ok'])): ?>
    <p class="alert-ok">✅ Data berhasil disimpan.</p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
    <p class="alert-err">⚠ <?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <div class="konten-layout">

        <!-- ===== KOLOM KIRI: FORM TAMBAH / EDIT ===== -->
        <div class="konten-form-col">
            <div class="form-card">
                <h3><?php echo $dataEdit ? 'Edit Data' : 'Tambah Baru'; ?></h3>

                <?php if ($tab === 'profil'): ?>
                <!-- FORM PROFIL SANGGAR -->
                <form method="POST" action="kelola_konten.php?tab=profil&aksi=simpan" enctype="multipart/form-data">
                    <input type="hidden" name="tentang_gambar_lama" value="<?php echo htmlspecialchars(ps_img_val($ps,'tentang_gambar')); ?>">

                    <div class="form-section-title">Tentang Sanggar</div>
                    <label>Judul</label>
                    <input type="text" name="tentang_judul" value="<?php echo htmlspecialchars(ps_val($ps,'tentang_judul')); ?>" required>
                    <label>Deskripsi Paragraf 1</label>
                    <textarea name="tentang_deskripsi" rows="4"><?php echo htmlspecialchars(ps_val($ps,'tentang_deskripsi')); ?></textarea>
                    <label>Deskripsi Paragraf 2</label>
                    <textarea name="tentang_deskripsi2" rows="3"><?php echo htmlspecialchars(ps_val($ps,'tentang_deskripsi2')); ?></textarea>
                    <label>Foto / Ilustrasi Sanggar (opsional)</label>
                    <input type="file" name="tentang_gambar" accept="image/*">
                    <?php $tg = ps_img_val($ps,'tentang_gambar'); if ($tg): ?>
                    <img src="../gambar/profil_sanggar/<?php echo htmlspecialchars($tg); ?>" style="max-width:100%;border-radius:6px;margin-top:8px;border:1px solid #e5dcc8;">
                    <?php endif; ?>

                    <div class="form-section-title">Statistik</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <div><label>Angka 1</label><input type="text" name="stat1_angka" value="<?php echo htmlspecialchars(ps_val($ps,'stat1_angka','10+')); ?>"></div>
                        <div><label>Label 1</label><input type="text" name="stat1_label" value="<?php echo htmlspecialchars(ps_val($ps,'stat1_label','Tahun Berdiri')); ?>"></div>
                        <div><label>Angka 2</label><input type="text" name="stat2_angka" value="<?php echo htmlspecialchars(ps_val($ps,'stat2_angka','500+')); ?>"></div>
                        <div><label>Label 2</label><input type="text" name="stat2_label" value="<?php echo htmlspecialchars(ps_val($ps,'stat2_label','Pementasan')); ?>"></div>
                        <div><label>Angka 3</label><input type="text" name="stat3_angka" value="<?php echo htmlspecialchars(ps_val($ps,'stat3_angka','200+')); ?>"></div>
                        <div><label>Label 3</label><input type="text" name="stat3_label" value="<?php echo htmlspecialchars(ps_val($ps,'stat3_label','Koleksi Kostum')); ?>"></div>
                    </div>

                    <div class="form-section-title">Banner CTA</div>
                    <label>Judul CTA</label>
                    <input type="text" name="cta_judul" value="<?php echo htmlspecialchars(ps_val($ps,'cta_judul','Temukan Kostum Impian Anda')); ?>">
                    <label>Deskripsi CTA</label>
                    <textarea name="cta_deskripsi" rows="3"><?php echo htmlspecialchars(ps_val($ps,'cta_deskripsi')); ?></textarea>

                    <button type="submit" class="btn" style="width:100%;margin-top:20px;padding:13px;">Simpan Semua</button>
                </form>

                <?php elseif ($tab === 'tim'): ?>
                <!-- FORM TIM & PENDIRI -->
                <form method="POST" action="kelola_konten.php?tab=tim&aksi=simpan" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $dataEdit['id'] ?? 0; ?>">
                    <input type="hidden" name="foto_lama" value="<?php echo htmlspecialchars($dataEdit['foto'] ?? ''); ?>">

                    <label>Nama *</label>
                    <input type="text" name="nama" required value="<?php echo htmlspecialchars($dataEdit['nama'] ?? ''); ?>" placeholder="Nama lengkap">
                    <label>Peran / Jabatan *</label>
                    <input type="text" name="peran" required value="<?php echo htmlspecialchars($dataEdit['peran'] ?? ''); ?>" placeholder="Contoh: Pelatih Tari Tradisional">
                    <label>Bio Singkat</label>
                    <textarea name="bio" rows="3"><?php echo htmlspecialchars($dataEdit['bio'] ?? ''); ?></textarea>

                    <label>Foto</label>
                    <input type="file" name="foto" accept="image/*">
                    <?php if (!empty($dataEdit['foto'])): ?>
                    <img src="../gambar/tim/<?php echo htmlspecialchars($dataEdit['foto']); ?>" style="max-width:100px;border-radius:50%;margin-top:8px;border:2px solid #d4af37;">
                    <?php endif; ?>
                    <p style="font-size:0.72rem;color:#999;margin-top:4px;">JPG/PNG, maks 2MB. Ditampilkan bulat.</p>

                    <div class="form-section-title">Link Sosial Media</div>
                    <label>WhatsApp (format: https://wa.me/628xxx)</label>
                    <input type="text" name="link_wa" value="<?php echo htmlspecialchars($dataEdit['link_wa'] ?? ''); ?>" placeholder="https://wa.me/628...">
                    <label>Instagram</label>
                    <input type="text" name="link_ig" value="<?php echo htmlspecialchars($dataEdit['link_ig'] ?? ''); ?>" placeholder="https://instagram.com/...">
                    <label>TikTok</label>
                    <input type="text" name="link_tiktok" value="<?php echo htmlspecialchars($dataEdit['link_tiktok'] ?? ''); ?>" placeholder="https://tiktok.com/@...">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:18px;">
                        <div><label>Urutan</label><input type="number" name="urutan" min="0" value="<?php echo (int)($dataEdit['urutan'] ?? 0); ?>"></div>
                        <div style="display:flex;align-items:flex-end;padding-bottom:2px;">
                            <label style="display:flex;align-items:center;gap:8px;margin:0;text-transform:none;letter-spacing:0;font-size:0.85rem;">
                                <input type="checkbox" name="is_founder" style="width:auto;" <?php echo !empty($dataEdit['is_founder'])?'checked':''; ?>>
                                Ini Pendiri / Ketua
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="width:100%;margin-top:20px;padding:13px;">
                        <?php echo $dataEdit ? 'Simpan Perubahan' : 'Tambah Anggota Tim'; ?>
                    </button>
                    <?php if ($dataEdit): ?>
                    <a href="kelola_konten.php?tab=tim" class="btn-sm btn-secondary" style="display:block;text-align:center;margin-top:10px;padding:10px;">Batal Edit</a>
                    <?php endif; ?>
                </form>

                <?php elseif ($tab === 'slide'): ?>                <!-- FORM HERO SLIDE -->
                <form method="POST" action="kelola_konten.php?tab=slide&aksi=simpan" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $dataEdit['id'] ?? 0; ?>">
                    <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($dataEdit['gambar'] ?? ''); ?>">

                    <label>Judul Slide *</label>
                    <input type="text" name="judul" value="<?php echo htmlspecialchars($dataEdit['judul'] ?? ''); ?>" required placeholder="Contoh: Baju Bodo Modern">

                    <label>Subjudul / Deskripsi Singkat</label>
                    <textarea name="subjudul" rows="2" placeholder="Kalimat pendek di bawah judul"><?php echo htmlspecialchars($dataEdit['subjudul'] ?? ''); ?></textarea>

                    <label>Gambar Slide</label>
                    <input type="file" name="gambar" accept="image/*">
                    <?php if (!empty($dataEdit['gambar'])): ?>
                    <img src="../gambar/slide/<?php echo htmlspecialchars($dataEdit['gambar']); ?>"
                         style="max-width:100%;border-radius:6px;margin-top:8px;border:1px solid #e5dcc8;">
                    <?php endif; ?>
                    <p style="font-size:0.72rem;color:#999;margin-top:4px;">JPG/PNG, maks 5MB. Ukuran ideal: 1280×600px.</p>

                    <label>URL Tombol (link_btn)</label>
                    <input type="text" name="link_btn" value="<?php echo htmlspecialchars($dataEdit['link_btn'] ?? ''); ?>" placeholder="Contoh: kostum.php atau sewa.php?id=1">

                    <label>Label Tombol</label>
                    <input type="text" name="label_btn" value="<?php echo htmlspecialchars($dataEdit['label_btn'] ?? 'Lihat Koleksi'); ?>" placeholder="Lihat Koleksi">

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div>
                            <label>Urutan</label>
                            <input type="number" name="urutan" value="<?php echo (int)($dataEdit['urutan'] ?? 0); ?>" min="0">
                        </div>
                        <div style="display:flex;align-items:flex-end;padding-bottom:2px;">
                            <label style="display:flex;align-items:center;gap:8px;margin:0;text-transform:none;letter-spacing:0;font-size:0.85rem;">
                                <input type="checkbox" name="aktif" style="width:auto;"
                                    <?php echo (!isset($dataEdit) || !empty($dataEdit['aktif'])) ? 'checked' : ''; ?>>
                                Aktif di beranda
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn" style="width:100%;margin-top:20px;padding:13px;">
                        <?php echo $dataEdit ? 'Simpan Perubahan' : 'Tambah Slide'; ?>
                    </button>
                    <?php if ($dataEdit): ?>
                    <a href="kelola_konten.php?tab=slide" class="btn-sm btn-secondary" style="display:block;text-align:center;margin-top:10px;padding:10px;">Batal Edit</a>
                    <?php endif; ?>
                </form>

                <?php else: /* FORM KONTEN BIASA */ ?>
                <?php
                $lbl = $config[$tab]['label'];
                $fo  = $config[$tab]['folder'];
                ?>
                <form method="POST" action="kelola_konten.php?tab=<?php echo $tab; ?>&aksi=simpan" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $dataEdit['id'] ?? 0; ?>">
                    <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($dataEdit['gambar'] ?? ''); ?>">

                    <label>Judul / Nama *</label>
                    <input type="text" name="judul" required
                           value="<?php echo htmlspecialchars($dataEdit['judul'] ?? $dataEdit['nama_pelatihan'] ?? ''); ?>"
                           placeholder="Judul <?php echo $lbl; ?>">

                    <label>Deskripsi</label>
                    <textarea name="deskripsi" rows="3"><?php echo htmlspecialchars($dataEdit['deskripsi'] ?? ''); ?></textarea>

                    <?php if ($tab === 'pelatihan'): ?>
                    <label>Jadwal</label>
                    <input type="text" name="jadwal" value="<?php echo htmlspecialchars($dataEdit['jadwal'] ?? ''); ?>"
                           placeholder="Contoh: Setiap Sabtu, 15.00–17.00">
                    <?php elseif ($tab === 'event'): ?>
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?php echo htmlspecialchars($dataEdit['tanggal'] ?? ''); ?>">
                    <?php endif; ?>

                    <label>Gambar <?php echo $dataEdit ? '(kosongkan jika tidak ganti)' : ''; ?></label>
                    <input type="file" name="gambar" accept="image/*">
                    <?php if (!empty($dataEdit['gambar'])): ?>
                    <img src="../gambar/<?php echo $fo; ?>/<?php echo htmlspecialchars($dataEdit['gambar']); ?>"
                         style="max-width:100%;border-radius:6px;margin-top:8px;border:1px solid #e5dcc8;">
                    <?php endif; ?>

                    <button type="submit" class="btn" style="width:100%;margin-top:20px;padding:13px;">
                        <?php echo $dataEdit ? 'Simpan Perubahan' : 'Tambah ' . $lbl; ?>
                    </button>
                    <?php if ($dataEdit): ?>
                    <a href="kelola_konten.php?tab=<?php echo $tab; ?>" class="btn-sm btn-secondary" style="display:block;text-align:center;margin-top:10px;padding:10px;">Batal Edit</a>
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===== KOLOM KANAN: DAFTAR DATA ===== -->
        <div class="konten-list-col">
            <h3 style="margin-bottom:16px;font-size:0.9rem;letter-spacing:2px;color:#b8962e;text-transform:uppercase;">
                <?php
                $judul_kanan = [
                    'slide'     => 'Daftar Slide Hero',
                    'profil'    => 'Preview & Info',
                    'tim'       => 'Daftar Tim & Pendiri',
                    'pelatihan' => 'Daftar Pelatihan',
                    'karya'     => 'Daftar Hasil Karya',
                    'event'     => 'Daftar Event & Prestasi',
                ];
                echo $judul_kanan[$tab] ?? 'Daftar Data';
                ?>
            </h3>

            <?php if ($tab === 'profil'): ?>
            <!-- PROFIL: tampilkan preview data saat ini -->
            <div class="form-card" style="background:#fffcf5;">
                <p style="font-size:0.8rem;color:#888;margin-bottom:16px;">
                    Data yang tampil di bawah ini adalah yang sekarang muncul di beranda pengguna.
                    Edit dari form kiri lalu klik <strong>Simpan Semua</strong>.
                </p>
                <p class="form-section-title" style="margin-top:0;">Tentang Sanggar</p>
                <p style="font-size:0.88rem;font-weight:600;color:#333;margin-bottom:4px;">
                    <?php echo htmlspecialchars(ps_val($ps,'tentang_judul','(belum diisi)')); ?>
                </p>
                <p style="font-size:0.82rem;color:#555;line-height:1.6;">
                    <?php echo nl2br(htmlspecialchars(mb_substr(ps_val($ps,'tentang_deskripsi'),0,200))); ?>...
                </p>
                <?php $tg = ps_img_val($ps,'tentang_gambar'); if ($tg): ?>
                <img src="../gambar/profil_sanggar/<?php echo htmlspecialchars($tg); ?>"
                     style="max-width:100%;border-radius:8px;margin-top:12px;border:1px solid #e5dcc8;">
                <?php endif; ?>

                <p class="form-section-title">Statistik</p>
                <div style="display:flex;gap:20px;flex-wrap:wrap;">
                    <?php foreach ([['stat1_angka','stat1_label'],['stat2_angka','stat2_label'],['stat3_angka','stat3_label']] as [$an,$lb]): ?>
                    <div style="text-align:center;border-left:3px solid #d4af37;padding-left:12px;">
                        <span style="display:block;font-family:'Cinzel',serif;font-size:1.4rem;color:#d4af37;">
                            <?php echo htmlspecialchars(ps_val($ps,$an,'-')); ?>
                        </span>
                        <span style="font-size:0.72rem;color:#888;text-transform:uppercase;letter-spacing:1px;">
                            <?php echo htmlspecialchars(ps_val($ps,$lb,'-')); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <p class="form-section-title">Banner CTA</p>
                <p style="font-size:0.88rem;font-weight:600;color:#333;margin-bottom:4px;">
                    <?php echo htmlspecialchars(ps_val($ps,'cta_judul','(belum diisi)')); ?>
                </p>
                <p style="font-size:0.82rem;color:#555;">
                    <?php echo htmlspecialchars(mb_substr(ps_val($ps,'cta_deskripsi'),0,120)); ?>
                </p>

                <div style="margin-top:20px;">
                    <a href="<?php echo $root; ?>pengguna/dashboard.php" target="_blank" class="btn-sm btn-secondary">
                        👁 Lihat di Beranda
                    </a>
                </div>
            </div>

            <?php elseif ($tab === 'tim'): ?>
            <!-- TIM: daftar anggota -->
            <?php if (!$daftar || mysqli_num_rows($daftar) === 0): ?>
            <p style="color:#888;font-size:0.85rem;padding:16px 0;">
                Belum ada anggota tim. Tambahkan dari form kiri.
            </p>
            <?php else: ?>
            <div class="slide-list">
            <?php while ($d = mysqli_fetch_assoc($daftar)): ?>
            <div class="slide-list-item">
                <div class="slide-thumb" style="border-radius:50%;overflow:hidden;border:2px solid #d4af37;">
                    <?php if (!empty($d['foto'])): ?>
                    <img src="../gambar/tim/<?php echo htmlspecialchars($d['foto']); ?>" alt=""
                         style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                    <div class="slide-thumb-empty" style="background:#2a1a05;">👤</div>
                    <?php endif; ?>
                </div>
                <div class="slide-list-info">
                    <p class="slide-list-title">
                        <?php echo htmlspecialchars($d['nama']); ?>
                        <?php if ($d['is_founder']): ?>
                        <span style="background:#d4af37;color:#fff;font-size:0.58rem;padding:2px 7px;border-radius:10px;margin-left:6px;vertical-align:middle;">PENDIRI</span>
                        <?php endif; ?>
                    </p>
                    <p class="slide-list-sub"><?php echo htmlspecialchars($d['peran']); ?></p>
                    <div class="slide-list-meta">
                        Urutan: <?php echo $d['urutan']; ?>
                        <?php if (!empty($d['link_wa'])): ?>&nbsp;·&nbsp;🟢 WA<?php endif; ?>
                        <?php if (!empty($d['link_ig'])): ?>&nbsp;·&nbsp;📸 IG<?php endif; ?>
                        <?php if (!empty($d['link_tiktok'])): ?>&nbsp;·&nbsp;🎵 TikTok<?php endif; ?>
                    </div>
                </div>
                <div class="slide-list-actions">
                    <a href="kelola_konten.php?tab=tim&aksi=edit&id=<?php echo $d['id']; ?>"
                       class="btn-sm btn-success">Edit</a>
                    <a href="kelola_konten.php?tab=tim&aksi=hapus&id=<?php echo $d['id']; ?>"
                       class="btn-sm btn-danger" data-confirm="Hapus anggota tim ini?">Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
            </div>
            <?php endif; ?>

            <?php elseif ($tab === 'slide'): ?>
            <!-- DAFTAR SLIDE -->
            <?php if (mysqli_num_rows($daftar) === 0): ?>
            <p style="color:#888;font-size:0.85rem;">Belum ada slide. Tambahkan dari form kiri.</p>
            <?php endif; ?>
            <div class="slide-list">
            <?php while ($d = mysqli_fetch_assoc($daftar)): ?>
            <div class="slide-list-item <?php echo $d['aktif'] ? '' : 'slide-nonaktif'; ?>">
                <div class="slide-thumb">
                    <?php if (!empty($d['gambar'])): ?>
                    <img src="../gambar/slide/<?php echo htmlspecialchars($d['gambar']); ?>" alt="">
                    <?php else: ?>
                    <div class="slide-thumb-empty">🖼</div>
                    <?php endif; ?>
                </div>
                <div class="slide-list-info">
                    <p class="slide-list-title"><?php echo htmlspecialchars($d['judul']); ?></p>
                    <p class="slide-list-sub"><?php echo htmlspecialchars(mb_substr($d['subjudul'],0,60)); ?></p>
                    <div class="slide-list-meta">
                        Urutan: <?php echo $d['urutan']; ?> &nbsp;|&nbsp;
                        <span style="color:<?php echo $d['aktif']?'#2e7d32':'#c62828'; ?>;">
                            <?php echo $d['aktif']?'Aktif':'Nonaktif'; ?>
                        </span>
                    </div>
                </div>
                <div class="slide-list-actions">
                    <a href="kelola_konten.php?tab=slide&aksi=edit&id=<?php echo $d['id']; ?>" class="btn-sm btn-success">Edit</a>
                    <a href="kelola_konten.php?tab=slide&aksi=toggle&id=<?php echo $d['id']; ?>"
                       class="btn-sm <?php echo $d['aktif']?'btn-secondary':'btn-success'; ?>">
                        <?php echo $d['aktif']?'Nonaktifkan':'Aktifkan'; ?>
                    </a>
                    <a href="kelola_konten.php?tab=slide&aksi=hapus&id=<?php echo $d['id']; ?>"
                       class="btn-sm btn-danger" data-confirm="Hapus slide ini?">Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
            </div>

            <?php else: ?>
            <!-- DAFTAR KONTEN BIASA (pelatihan/karya/event) -->
            <div class="table-wrapper" style="margin-top:0;">
                <table>
                    <thead><tr><th>Gambar</th><th>Judul</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php if (!$daftar || mysqli_num_rows($daftar) === 0): ?>
                    <tr><td colspan="4" style="text-align:center;color:#888;">Belum ada data.</td></tr>
                    <?php endif; ?>
                    <?php if ($daftar): while ($d = mysqli_fetch_assoc($daftar)):
                          $fo2 = $config[$tab]['folder'] ?? ''; ?>
                    <tr>
                        <td>
                            <?php if (!empty($d['gambar'])): ?>
                            <img src="../gambar/<?php echo $fo2; ?>/<?php echo htmlspecialchars($d['gambar']); ?>" class="thumb">
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($d['judul'] ?? $d['nama_pelatihan'] ?? ''); ?></td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            <?php echo htmlspecialchars(mb_substr($d['deskripsi'] ?? '',0,60)); ?>
                        </td>
                        <td style="white-space:nowrap;">
                            <a href="kelola_konten.php?tab=<?php echo $tab; ?>&aksi=edit&id=<?php echo $d['id']; ?>"
                               class="btn-sm btn-success">Edit</a>
                            <a href="kelola_konten.php?tab=<?php echo $tab; ?>&aksi=hapus&id=<?php echo $d['id']; ?>"
                               class="btn-sm btn-danger" data-confirm="Hapus data ini?">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /.konten-layout -->
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>