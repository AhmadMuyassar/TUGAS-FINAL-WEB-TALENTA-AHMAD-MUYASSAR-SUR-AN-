<?php
session_start();
if (isset($_SESSION['username'])) {
    header("location:dashboard.php");
    exit();
}
$root = '../';
$page_title = 'Daftar Akun';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container" style="max-width:600px;">
        <div class="login-logo-wrap">
            <img src="../gambar/LOGO TALENTA.png" alt="Logo Talenta" class="login-logo-img">
            <p class="login-logo">TALENTA PROJECT</p>
            <p class="login-subtitle">Daftar Akun Penyewa</p>
        </div>

        <div class="form-card">
            <h2 style="text-align:center; margin-bottom:4px;">Buat Akun Baru</h2>
            <p style="text-align:center; color:#888; font-size:0.82rem; margin-bottom:20px;">
                Lengkapi biodata dengan benar. Data ini digunakan admin untuk verifikasi identitas penyewa.
            </p>

            <?php if (isset($_GET['gagal'])): ?>
                <p style="color:#c62828; margin-bottom:16px; font-size:0.85rem; background:#ffebee; padding:10px 14px; border-radius:6px;">
                    <?php
                    $pesan = [
                        'ada'    => 'Username sudah dipakai, silakan pilih username lain.',
                        'kosong' => 'Semua kolom wajib diisi.',
                        'email'  => 'Format email tidak valid.',
                        'upload' => htmlspecialchars($_GET['msg'] ?? 'Gagal upload foto.'),
                    ];
                    echo $pesan[$_GET['gagal']] ?? 'Terjadi kesalahan, coba lagi.';
                    ?>
                </p>
            <?php endif; ?>

            <form id="form-daftar" action="../proses/proses_daftar.php" method="POST" enctype="multipart/form-data">

                <!-- ===== FOTO PROFIL ===== -->
                <div class="foto-upload-wrap">
                    <div class="foto-preview" id="preview-profil-wrap">
                        <img id="preview-profil" src="../gambar/profil/default.svg" alt="Foto Profil">
                    </div>
                    <div>
                        <label for="foto_profil" style="margin-top:0;">Foto Profil</label>
                        <input type="file" id="foto_profil" name="foto_profil" accept="image/*" data-preview="preview-profil">
                        <p style="font-size:0.75rem; color:#999; margin-top:4px;">JPG/PNG, maks 2MB. Tampil di profil akun.</p>
                    </div>
                </div>

                <!-- ===== DATA LOGIN ===== -->
                <div class="form-section-title">Data Login</div>

                <label for="username">Username <span style="color:#c62828;">*</span></label>
                <input type="text" id="username" name="username" placeholder="Contoh: budi123" required>

                <label for="password">Password <span style="color:#c62828;">*</span></label>
                <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>

                <!-- ===== BIODATA DIRI ===== -->
                <div class="form-section-title">Biodata Diri</div>

                <label for="nama_lengkap">Nama Lengkap (sesuai KTP) <span style="color:#c62828;">*</span></label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama sesuai identitas resmi" required>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label for="email">Email <span style="color:#c62828;">*</span></label>
                        <input type="email" id="email" name="email" placeholder="email@contoh.com" required>
                    </div>
                    <div>
                        <label for="no_hp">No. HP / WhatsApp <span style="color:#c62828;">*</span></label>
                        <input type="text" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" required>
                    </div>
                </div>

                <label for="alamat">Alamat Lengkap <span style="color:#c62828;">*</span></label>
                <textarea id="alamat" name="alamat" rows="3" placeholder="Jl. ..., Kelurahan, Kecamatan, Kota" required></textarea>

                <!-- ===== IDENTITAS ===== -->
                <div class="form-section-title">Identitas (untuk verifikasi penyewaan)</div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label for="jenis_identitas">Jenis Identitas <span style="color:#c62828;">*</span></label>
                        <select id="jenis_identitas" name="jenis_identitas" required>
                            <option value="">-- Pilih --</option>
                            <option value="KTP">KTP</option>
                            <option value="SIM">SIM</option>
                            <option value="KTM">KTM (Kartu Mahasiswa)</option>
                            <option value="Paspor">Paspor</option>
                        </select>
                    </div>
                    <div>
                        <label for="nomor_identitas">Nomor Identitas <span style="color:#c62828;">*</span></label>
                        <input type="text" id="nomor_identitas" name="nomor_identitas" placeholder="16 digit NIK / nomor kartu" required>
                    </div>
                </div>

                <label for="foto_identitas">Foto / Scan Identitas <span style="color:#c62828;">*</span></label>
                <input type="file" id="foto_identitas" name="foto_identitas" accept="image/*" required>
                <p style="font-size:0.75rem; color:#999; margin-top:4px;">Upload foto KTP/SIM/KTM yang jelas. JPG/PNG, maks 5MB. Hanya dilihat admin untuk verifikasi.</p>
                <div id="preview-identitas-wrap" style="display:none; margin-top:8px;">
                    <img id="preview-identitas" src="" alt="Preview Identitas" style="max-width:100%; border-radius:6px; border:1px solid #e5dcc8;">
                </div>

                <button type="submit" class="btn" style="width:100%; margin-top:28px; padding:14px;">Buat Akun</button>
            </form>

            <p class="login-footer-text">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </p>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
