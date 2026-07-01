<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$root = '../';
$page_title = 'Kelola Inventaris';
require_once __DIR__ . '/../includes/header.php';

$query = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY id ASC");
?>

    <div class="container">
        <div class="admin-toolbar">
            <h2 style="margin:0; border:none; padding:0;">Kelola Inventaris Kostum</h2>
            <div>
                <a href="tambah_kostum.php" class="btn-sm btn-success">+ Tambah Kostum</a>
                <a href="dashboard.php" class="btn-sm btn-secondary">Dashboard</a>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Nama Kostum</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($query) === 0): ?>
                    <tr><td colspan="6">Belum ada data kostum.</td></tr>
                    <?php endif; ?>
                    <?php while ($d = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= htmlspecialchars($d['nama_kostum']); ?></td>
                        <td><?= htmlspecialchars($d['kategori']); ?></td>
                        <td><?= (int) $d['stok']; ?> unit</td>
                        <td>Rp <?= number_format($d['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <img src="../image/<?= htmlspecialchars($d['gambar']); ?>" class="thumb" alt="<?= htmlspecialchars($d['nama_kostum']); ?>">
                        </td>
                        <td>
                            <a href="edit_kostum.php?id=<?= $d['id']; ?>" class="btn-sm btn-success">Edit</a>
                            <a href="hapus_kostum.php?id=<?= $d['id']; ?>"
                               class="btn-sm btn-danger"
                               data-confirm="Yakin hapus kostum ini?">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
