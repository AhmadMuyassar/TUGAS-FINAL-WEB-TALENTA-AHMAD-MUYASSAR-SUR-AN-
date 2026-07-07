<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_login('pengguna/profil.php');

$id_user = (int) $_SESSION['id_user'];

// Ambil data profil terbaru dari DB
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$profil = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$root = '../';
$page_title = 'Profil Saya';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width:640px;">
    <h2>Profil Saya</h2>

    <?php if (isset($_GET['sukses'])): ?>
        <p style="background:#e8f5e9; border:1px solid #c8e6c9; color:#2e7d32; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:0.88rem;">
            Profil berhasil diperbarui.
        </p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p style="background:#ffebee; border:1px solid #ffcdd2; color:#c62828; padding:12px 16px; border-radius:6px; margin-bottom:20px; font-size:0.88rem;">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </p>
    <?php endif; ?>

    <div class="form-card">
        <form id="form-profil" action="../proses/proses_profil.php" method="POST" enctype="multipart/form-data">

            <!-- ===== FOTO PROFIL ===== -->
            <div class="foto-upload-wrap">
                <div class="foto-preview">
                    <img id="preview-profil"
                         src="<?php echo !empty($profil['foto_profil'])
                             ? '../gambar/profil/' . htmlspecialchars($profil['foto_profil'])
                             : '../gambar/profil/default.svg'; ?>"
                         alt="Foto Profil">
                </div>
                <div>
                    <label for="foto_profil" style="margin-top:0;">Foto Profil</label>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*" data-preview="preview-profil">
                    <p style="font-size:0.75rem; color:#999; margin-top:4px;">Kosongkan jika tidak ingin mengubah. JPG/PNG, maks 2MB.</p>
                </div>
            </div>

            <!-- ===== DATA LOGIN ===== -->
            <div class="form-section-title">Data Login</div>

            <label for="username">Username</label>
            <input type="text" value="<?php echo htmlspecialchars($profil['username']); ?>" disabled style="background:#f0ece0; color:#999; cursor:not-allowed;">
            <p style="font-size:0.75rem; color:#999; margin-top:4px;">Username tidak bisa diubah.</p>

            <label for="password_baru">Password Baru</label>
            <input type="password" id="password_baru" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah">

            <!-- ===== BIODATA ===== -->
            <div class="form-section-title">Biodata Diri</div>

            <label for="nama_lengkap">Nama Lengkap <span style="color:#c62828;">*</span></label>
            <input type="text" id="nama_lengkap" name="nama_lengkap"
                   value="<?php echo htmlspecialchars($profil['nama_lengkap']); ?>" required>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label for="email">Email <span style="color:#c62828;">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($profil['email']); ?>" required>
                </div>
                <div>
                    <label for="no_hp">No. HP / WhatsApp <span style="color:#c62828;">*</span></label>
                    <input type="text" id="no_hp" name="no_hp"
                           value="<?php echo htmlspecialchars($profil['no_hp']); ?>" required>
                </div>
            </div>

            <label for="alamat">Alamat Lengkap <span style="color:#c62828;">*</span></label>
            <textarea id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($profil['alamat']); ?></textarea>

            <!-- ===== IDENTITAS ===== -->
            <div class="form-section-title">Identitas</div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                <div>
                    <label for="jenis_identitas">Jenis Identitas <span style="color:#c62828;">*</span></label>
                    <select id="jenis_identitas" name="jenis_identitas" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach (['KTP','SIM','KTM','Paspor'] as $jenis): ?>
                            <option value="<?php echo $jenis; ?>"
                                <?php echo ($profil['jenis_identitas'] === $jenis) ? 'selected' : ''; ?>>
                                <?php echo $jenis; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="nomor_identitas">Nomor Identitas <span style="color:#c62828;">*</span></label>
                    <input type="text" id="nomor_identitas" name="nomor_identitas"
                           value="<?php echo htmlspecialchars($profil['nomor_identitas']); ?>" required>
                </div>
            </div>

            <label>Foto Identitas Saat Ini</label>
            <?php if (!empty($profil['foto_identitas'])): ?>
                <img src="../gambar/identitas/<?php echo htmlspecialchars($profil['foto_identitas']); ?>"
                     alt="Foto Identitas" style="max-width:100%; border-radius:6px; border:1px solid #e5dcc8; margin-bottom:10px;">
            <?php else: ?>
                <p style="color:#888; font-size:0.85rem; margin-bottom:10px;">Belum ada foto identitas.</p>
            <?php endif; ?>

            <label for="foto_identitas">Ganti Foto Identitas</label>
            <input type="file" id="foto_identitas" name="foto_identitas" accept="image/*">
            <p style="font-size:0.75rem; color:#999; margin-top:4px;">Kosongkan jika tidak ingin mengubah. JPG/PNG, maks 5MB.</p>
            <div id="preview-identitas-wrap" style="display:none; margin-top:8px;">
                <img id="preview-identitas" src="" alt="Preview" style="max-width:100%; border-radius:6px; border:1px solid #e5dcc8;">
            </div>

            <button type="submit" class="btn" style="width:100%; margin-top:28px; padding:14px;">Simpan Perubahan</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
