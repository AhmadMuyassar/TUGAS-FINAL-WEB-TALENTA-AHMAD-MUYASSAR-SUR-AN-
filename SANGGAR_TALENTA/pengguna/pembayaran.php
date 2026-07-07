<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_login('pengguna/riwayat.php');

$id = (int) ($_GET['id'] ?? 0);
$id_user = (int) $_SESSION['id_user'];

$stmt = mysqli_prepare($conn, "SELECT sewa.*, inventaris.nama_kostum, inventaris.harga
                                FROM sewa JOIN inventaris ON sewa.id_kostum = inventaris.id
                                WHERE sewa.id = ? AND sewa.id_user = ?");
mysqli_stmt_bind_param($stmt, "ii", $id, $id_user);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Hanya bisa bayar jika status sewa "diterima" dan belum bayar
if (!$data || $data['status'] !== 'diterima' || $data['status_bayar'] === 'lunas') {
    header("location:riwayat.php");
    exit();
}

$total = $data['harga'] * $data['jumlah'] * $data['lama_sewa'];

$root = '../';
$page_title = 'Pembayaran';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container" style="max-width:600px;">
        <h2>Pembayaran Sewa Kostum</h2>

        <?php if ($data['status_bayar'] === 'menunggu_verifikasi'): ?>
            <p style="color:#c9a100; margin-bottom:16px;">Bukti transfer Anda sedang menunggu verifikasi admin.</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#c62828; margin-bottom:16px; font-size:0.85rem;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>

        <div class="form-card">
            <p><strong>Kostum:</strong> <?php echo htmlspecialchars($data['nama_kostum']); ?></p>
            <p><strong>Jumlah:</strong> <?php echo (int) $data['jumlah']; ?> set &times; <?php echo (int) $data['lama_sewa']; ?> hari</p>
            <p style="font-size:1.2rem; margin:10px 0;"><strong>Total Bayar: <?php echo format_rupiah($total); ?></strong></p>

            <form id="form-bayar" action="../proses/proses_bayar.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_sewa" value="<?php echo $data['id']; ?>">

                <label for="metode_bayar">Metode Pembayaran</label>
                <select id="metode_bayar" name="metode_bayar" required>
                    <option value="">-- Pilih Metode --</option>
                    <option value="cash">Cash (Bayar di Tempat)</option>
                    <option value="transfer">Transfer / QRIS</option>
                </select>

                <!-- Ditampilkan otomatis oleh JS jika pilih "transfer" -->
                <div id="wrap-transfer" style="display:none; margin-top:16px;">
                    <div style="text-align:center; margin-bottom:16px;">
                        <img src="../gambar/qris/qris_talenta.png" alt="QRIS Talenta Project" style="max-width:220px; border-radius:8px;">
                        <p style="font-size:0.85rem; color:#555; margin-top:8px;">
                            Atau transfer manual ke:<br>
                            <strong>BCA 1234567890 a.n. Talenta Project</strong>
                        </p>
                    </div>

                    <label for="bukti">Upload Bukti Transfer</label>
                    <input type="file" id="bukti" name="bukti" accept="image/*">
                </div>

                <button type="submit" class="btn" style="width:100%; margin-top:24px; padding:14px;">Konfirmasi Pembayaran</button>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
