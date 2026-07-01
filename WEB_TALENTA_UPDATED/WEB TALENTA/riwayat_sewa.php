<?php
// =====================================================================
// riwayat_sewa.php — Riwayat Penyewaan milik pengguna yang sedang login
// Menampilkan status konfirmasi dari admin (Menunggu/Diterima/Ditolak)
// beserta catatan admin jika penyewaan ditolak.
// =====================================================================
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}
require_once __DIR__ . '/koneksi.php';

$root = '';
$page_title = 'Riwayat Penyewaan';
require_once __DIR__ . '/includes/header.php';

// ===== 1. CARI ID USER YANG SEDANG LOGIN =====
$username = $_SESSION['username'];
$stmtUser = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmtUser, "s", $username);
mysqli_stmt_execute($stmtUser);
$u = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtUser));
$id_user = $u['id'] ?? 0;

// ===== 2. AMBIL SEMUA PENYEWAAN MILIK USER INI SAJA =====
$stmt = mysqli_prepare($conn, "SELECT sewa.*, inventaris.nama_kostum
                                FROM sewa
                                JOIN inventaris ON sewa.id_kostum = inventaris.id
                                WHERE sewa.id_user = ?
                                ORDER BY sewa.id DESC");
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
$riwayat = mysqli_stmt_get_result($stmt);

// Label tampilan untuk tiap nilai status di database
$labelStatus = [
    'menunggu' => 'Menunggu Konfirmasi',
    'diterima' => 'Diterima',
    'ditolak'  => 'Ditolak',
];
?>

    <div class="container">
        <h2>Riwayat Penyewaan Saya</h2>
        <p style="color:#888; font-size:0.88rem; margin-bottom:10px;">
            Pantau status konfirmasi penyewaan Anda dari admin di halaman ini.
        </p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Kostum</th>
                        <th>Jumlah</th>
                        <th>Lama (Hari)</th>
                        <th>Tanggal Sewa</th>
                        <th>Status</th>
                        <th>Catatan Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($riwayat) === 0): ?>
                    <tr><td colspan="6">Anda belum memiliki riwayat penyewaan.</td></tr>
                    <?php endif; ?>

                    <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
                        <?php $status = $r['status'] ?? 'menunggu'; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r['nama_kostum']); ?></td>
                            <td><?php echo (int) $r['jumlah']; ?></td>
                            <td><?php echo (int) $r['lama_sewa']; ?></td>
                            <td><?php echo htmlspecialchars($r['tanggal_sewa']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status; ?>">
                                    <?php echo $labelStatus[$status] ?? 'Menunggu Konfirmasi'; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo ($status === 'ditolak' && !empty($r['catatan_admin']))
                                    ? nl2br(htmlspecialchars($r['catatan_admin']))
                                    : '-'; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
