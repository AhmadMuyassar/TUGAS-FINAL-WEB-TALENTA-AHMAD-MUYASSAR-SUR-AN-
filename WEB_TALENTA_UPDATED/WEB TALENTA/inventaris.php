<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}
require_once __DIR__ . '/koneksi.php';

$root = '';
$page_title = 'Daftar Kostum';
require_once __DIR__ . '/includes/header.php';

$data = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY id ASC");
?>

    <div class="container">
        <h2>Daftar Kostum Tersedia</h2>
        <p style="color: #888; font-size: 0.88rem; margin-bottom: 10px;">Klik "Pilih" untuk langsung ke halaman sewa</p>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Gambar</th>
                        <th>Nama Kostum</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga Sewa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($data) === 0): ?>
                    <tr><td colspan="7">Belum ada data kostum. Jalankan buat_db.php terlebih dahulu.</td></tr>
                    <?php endif; ?>
                    <?php while ($k = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><span class="badge">K<?php echo str_pad($k['id'], 3, '0', STR_PAD_LEFT); ?></span></td>
                        <td>
                            <?php if (!empty($k['gambar'])): ?>
                                <img src="image/<?php echo htmlspecialchars($k['gambar']); ?>" alt="<?php echo htmlspecialchars($k['nama_kostum']); ?>" class="thumb">
                            <?php else: ?>
                                <span style="color:#aaa; font-size:0.75rem;">Tidak ada gambar</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($k['nama_kostum']); ?></td>
                        <td><?php echo htmlspecialchars($k['kategori']); ?></td>
                        <td>
                            <?php
                                $stok = (int) $k['stok'];
                                $kelas = $stok <= 3 ? 'low' : ($stok <= 8 ? 'medium' : 'high');
                            ?>
                            <span class="badge-stock <?php echo $kelas; ?>"><?php echo $stok; ?> unit</span>
                        </td>
                        <td class="price">Rp <?php echo number_format($k['harga'], 0, ',', '.'); ?>/hari</td>
                        <td><a href="sewa.php?id=<?php echo $k['id']; ?>" class="action-link">Pilih</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
