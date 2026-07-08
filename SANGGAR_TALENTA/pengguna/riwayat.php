<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_login('pengguna/riwayat.php');

$root = '../';
$page_title = 'Riwayat Penyewaan';
require_once __DIR__ . '/../includes/header.php';

$id_user = (int) $_SESSION['id_user'];
$stmt = mysqli_prepare($conn, "SELECT sewa.*, inventaris.nama_kostum
                                FROM sewa
                                JOIN inventaris ON sewa.id_kostum = inventaris.id
                                WHERE sewa.id_user = ?
                                ORDER BY sewa.id DESC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$riwayat = mysqli_stmt_get_result($stmt);

$labelStatus = ['menunggu' => 'Menunggu Konfirmasi', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];
$labelBayar  = ['belum_bayar' => 'Belum Bayar', 'menunggu_verifikasi' => 'Menunggu Verifikasi', 'lunas' => 'Lunas'];
?>

    <div class="container">
        <h2>Riwayat Penyewaan Saya</h2>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kostum</th><th>Jumlah</th><th>Lama</th><th>Tanggal</th>
                        <th>Status Sewa</th><th>Status Bayar</th><th>Catatan Admin</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($riwayat) === 0): ?>
                    <tr><td colspan="8">Anda belum memiliki riwayat penyewaan.</td></tr>
                    <?php endif; ?>

                    <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
                        <?php $status = $r['status']; $bayar = $r['status_bayar']; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['nama_kostum']); ?></td>
                            <td><?php echo (int) $r['jumlah']; ?></td>
                            <td><?php echo (int) $r['lama_sewa']; ?> hari</td>
                            <td><?php echo htmlspecialchars($r['tanggal_sewa']); ?></td>
                            <td><span class="status-badge <?php echo $status; ?>"><?php echo $labelStatus[$status]; ?></span></td>
                            <td><span class="status-badge <?php echo $bayar === 'lunas' ? 'diterima' : 'menunggu'; ?>"><?php echo $labelBayar[$bayar]; ?></span></td>
                            <td><?php echo ($status === 'ditolak' && !empty($r['catatan_admin'])) ? nl2br(htmlspecialchars($r['catatan_admin'])) : '-'; ?></td>
                            <td>
                                <?php if ($status === 'diterima' && $bayar === 'belum_bayar'): ?>
                                    <a href="pembayaran.php?id=<?php echo $r['id']; ?>" class="action-link">Bayar Sekarang</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
