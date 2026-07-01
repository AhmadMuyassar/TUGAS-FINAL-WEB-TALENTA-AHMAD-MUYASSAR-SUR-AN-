<?php
// =====================================================================
// sewa.php — Formulir Sewa Kostum (khusus pengguna login)
// =====================================================================
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}
require_once __DIR__ . '/koneksi.php';

$root = '';
$page_title = 'Sewa Kostum';
require_once __DIR__ . '/includes/header.php';

// ===== 1. AMBIL DATA KOSTUM UNTUK DROPDOWN PILIHAN =====
$kostum = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY nama_kostum ASC");
$preselect = isset($_GET['id']) ? (int) $_GET['id'] : 0;
?>

    <div class="container">
        <h2>Formulir Sewa Kostum</h2>

        <?php if (isset($_GET['sukses'])): ?>
            <p style="color:#2e7d32; margin-bottom:16px;">Penyewaan berhasil dikirim! Terima kasih.</p>
        <?php endif; ?>

        <div class="sewa-layout">

            <!-- ===== KOLOM KIRI: FORM PENYEWAAN ===== -->
            <div class="form-card">
                <form id="form-sewa" action="proses_sewa.php" method="POST">
                    <label for="id_kostum">Pilih Kostum</label>
                    <select id="id_kostum" name="id_kostum" required>
                        <?php while ($k = mysqli_fetch_assoc($kostum)): ?>
                            <option value="<?php echo $k['id']; ?>"
                                data-gambar="<?php echo htmlspecialchars($k['gambar']); ?>"
                                data-nama="<?php echo htmlspecialchars($k['nama_kostum']); ?>"
                                data-harga="Rp <?php echo number_format($k['harga'], 0, ',', '.'); ?>/hari"
                                <?php echo ($k['id'] == $preselect) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($k['nama_kostum']); ?> (Rp <?php echo number_format($k['harga'], 0, ',', '.'); ?>/hari)
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <label for="jumlah">Jumlah</label>
                    <input type="number" id="jumlah" name="jumlah" min="1" value="1" required>

                    <label for="lama">Lama Sewa (Hari)</label>
                    <input type="number" id="lama" name="lama" min="1" value="1" required>

                    <label for="catatan">Catatan (tanggal pemakaian, warna, dll.)</label>
                    <textarea id="catatan" name="catatan" rows="3" placeholder="Contoh: dipakai tgl 20 Juli 2026, warna hijau, ukuran M."></textarea>

                    <button type="submit" name="kirim" class="btn" style="width:100%; margin-top:24px; padding:14px;">Kirim Sewa</button>
                </form>
            </div>

            <!-- ===== KOLOM KANAN: PREVIEW GAMBAR + KARTU CATATAN PENYEWAAN =====
                 Kolom ini (.sewa-side) menumpuk 2 kartu secara vertikal
                 sehingga tingginya sejajar & rapi dengan form-card di kiri. -->
            <div class="sewa-side">

                <!-- Preview gambar kostum yang sedang dipilih (di-update via JS) -->
                <div class="sewa-preview-box">
                    <img id="sewa-preview-img" src="" alt="Preview kostum">
                    <p id="sewa-preview-nama">Pilih kostum untuk melihat gambar</p>
                    <p class="sewa-preview-harga" id="sewa-preview-harga"></p>
                </div>

                <!-- Kartu catatan/aturan penyewaan -->
                <div class="sewa-note-card">
                    <p class="sewa-note-title">Catatan Penyewaan</p>
                    <ul>
                        <li>Penyewaan dilakukan minimal <strong>H-1</strong> sebelum hari pemakaian (hari-H).</li>
                        <li>Setiap penyewaan akan <strong>dikonfirmasi oleh admin</strong> (diterima/ditolak).</li>
                        <li>Sertakan <strong>tanggal penyewaan / hari pakai</strong> pada kolom catatan.</li>
                        <li>Sertakan <strong>warna kostum</strong> yang diinginkan pada kolom catatan.</li>
                        <li>Jumlah kostum dihitung <strong>1 set lengkap dengan aksesoris</strong> sesuai gambar.</li>
                    </ul>
                </div>

            </div>

        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
