<?php
// =====================================================================
// admin/data_sewa.php — Daftar Penyewaan (sisi admin)
// Admin hanya mengonfirmasi (diterima/ditolak) lewat edit_sewa.php,
// tidak lagi mengubah jumlah/lama/catatan penyewa secara bebas.
// =====================================================================
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$root = '../';
$page_title = 'Data Penyewa';
require_once __DIR__ . '/../includes/header.php';

// ===== AMBIL SEMUA PENYEWAAN + JOIN USER & KOSTUM =====
$query = mysqli_query($conn, "SELECT sewa.*, users.username, inventaris.nama_kostum
                              FROM sewa
                              JOIN users ON sewa.id_user = users.id
                              JOIN inventaris ON sewa.id_kostum = inventaris.id
                              ORDER BY sewa.id DESC");

// Label tampilan untuk tiap nilai status di database
$labelStatus = [
    'menunggu' => 'Menunggu',
    'diterima' => 'Diterima',
    'ditolak'  => 'Ditolak',
];
?>

    <div class="container">
        <h2>Data Penyewa</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Penyewa</th>
                        <th>Kostum</th>
                        <th>Jumlah</th>
                        <th>Lama (Hari)</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) === 0): ?>
                    <tr><td colspan="7">Belum ada data penyewaan.</td></tr>
                    <?php endif; ?>

                    <?php while ($d = mysqli_fetch_assoc($query)): ?>
                        <?php $status = $d['status'] ?? 'menunggu'; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($d['username']); ?></td>
                            <td><?php echo htmlspecialchars($d['nama_kostum']); ?></td>
                            <td><?php echo (int) $d['jumlah']; ?></td>
                            <td><?php echo (int) $d['lama_sewa']; ?></td>
                            <td><?php echo htmlspecialchars($d['tanggal_sewa']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status; ?>">
                                    <?php echo $labelStatus[$status] ?? 'Menunggu'; ?>
                                </span>
                            </td>
                            <td>
                                <!-- Konfirmasi = terima/tolak penyewaan (lihat edit_sewa.php) -->
                                <a href="edit_sewa.php?id=<?php echo $d['id']; ?>" class="btn-sm btn-success">Konfirmasi</a>
                                <a href="hapus_sewa.php?id=<?php echo $d['id']; ?>" class="btn-sm btn-danger"
                                   data-confirm="Yakin hapus data penyewaan ini?">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
