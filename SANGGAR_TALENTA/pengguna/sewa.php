<?php
// =====================================================================
// pengguna/sewa.php — Bisa DILIHAT tanpa login (deskripsi & S&K),
// tapi form HANYA BISA DIKIRIM jika sudah login (dicek di proses_sewa.php
// & juga dicegah lewat JS supaya user diarahkan ke login dulu).
// =====================================================================
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_pengguna(); // admin diarahkan ke dashboard admin

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($conn, "SELECT * FROM inventaris WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$kostum = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$kostum) {
    header("location:kostum.php");
    exit();
}

$root = '../';
$page_title = 'Sewa ' . $kostum['nama_kostum'];
require_once __DIR__ . '/../includes/header.php';

$halaman_ini = 'pengguna/sewa.php?id=' . $id;
?>

    <div class="container">
        <h2>Formulir Sewa Kostum</h2>

        <?php if (isset($_GET['sukses'])): ?>
            <p style="color:#2e7d32; margin-bottom:16px;">Penyewaan berhasil dikirim! Admin akan segera mengonfirmasi.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] === 'stok'): ?>
                <p style="color:#c62828; margin-bottom:16px;">Jumlah yang diminta melebihi stok yang tersedia.</p>
            <?php else: ?>
                <p style="color:#c62828; margin-bottom:16px;">Terjadi kesalahan. Pastikan semua field terisi dengan benar.</p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="sewa-layout">

            <!-- ===== KOLOM KIRI: FORM PENYEWAAN ===== -->
            <div class="form-card">
                <form id="form-sewa" action="../proses/proses_sewa.php" method="POST">
                    <input type="hidden" name="id_kostum" value="<?php echo $kostum['id']; ?>">

                    <label for="jumlah">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" max="<?php echo (int) $kostum['stok']; ?>" value="1" required>

                    <label for="tanggal_pakai">Tanggal Pemakaian</label>
                    <input type="date" id="tanggal_pakai" name="tanggal_pakai" required>

                    <label for="lama">Lama Sewa (Hari)</label>
                    <input type="number" id="lama" name="lama" min="1" value="1" required>

                    <label for="catatan">Catatan (warna, ukuran, dll.)</label>
                    <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: warna hijau, ukuran M."></textarea>

                    <?php if (!sudah_login()): ?>
                        <p style="font-size:0.8rem; color:#c62828; margin-top:12px;">
                            Anda harus <a href="login.php?redirect=<?php echo urlencode($halaman_ini); ?>">login</a>
                            (atau <a href="daftar.php">daftar</a> jika belum punya akun) sebelum mengirim formulir ini.
                        </p>
                    <?php endif; ?>

                    <button type="submit" name="kirim" class="btn" style="width:100%; margin-top:24px; padding:14px;">Kirim Sewa</button>
                </form>
            </div>

            <!-- ===== KOLOM KANAN: DESKRIPSI + GAMBAR KOSTUM ===== -->
            <div class="sewa-side">
                <div class="sewa-preview-box">
                    <img src="../gambar/kostum/<?php echo htmlspecialchars($kostum['gambar']); ?>" alt="<?php echo htmlspecialchars($kostum['nama_kostum']); ?>">
                    <p style="font-weight:600;"><?php echo htmlspecialchars($kostum['nama_kostum']); ?></p>
                    <p class="sewa-preview-harga"><?php echo format_rupiah($kostum['harga']); ?>/hari</p>
                    <p style="font-size:0.85rem; color:#555; margin-top:10px;">
                        <?php echo nl2br(htmlspecialchars($kostum['deskripsi'])); ?>
                    </p>
                    <p style="font-size:0.8rem; color:#888;">Stok tersedia: <?php echo (int) $kostum['stok']; ?> unit</p>
                </div>
            </div>

        </div>

        <!-- ===== SYARAT & KETENTUAN — full width di bawah form + deskripsi ===== -->
        <div class="sewa-note-card" style="margin-top:24px;">
            <p class="sewa-note-title">Syarat &amp; Ketentuan Penyewaan</p>
            <ul>
                <li>Penyewaan dilakukan minimal <strong>H-1</strong> sebelum hari pemakaian.</li>
                <li>Setiap penyewaan akan <strong>dikonfirmasi oleh admin</strong> (diterima/ditolak) melalui halaman Riwayat Sewa.</li>
                <li>Setelah penyewaan <strong>diterima</strong>, Anda akan diarahkan untuk melakukan pembayaran (Cash atau Transfer/QRIS).</li>
                <li>Jumlah kostum dihitung 1 set lengkap dengan aksesoris sesuai gambar.</li>
                <li>Kerusakan/kehilangan kostum menjadi tanggung jawab penyewa sesuai kesepakatan sanggar.</li>
            </ul>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
