<?php
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_pengguna(); // admin diarahkan ke dashboard admin

$root = '../';
$page_title = 'Daftar Kostum';
require_once __DIR__ . '/../includes/header.php';

// ===== 1. AMBIL PARAMETER PENCARIAN & KATEGORI =====
$cari     = trim($_GET['cari'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');

// Daftar kategori unik untuk dropdown filter
$listKategori = mysqli_query($conn, "SELECT DISTINCT kategori FROM inventaris ORDER BY kategori ASC");

// ===== 2. QUERY DINAMIS (prepared statement, aman dari SQL Injection) =====
$sql = "SELECT * FROM inventaris WHERE nama_kostum LIKE ?";
$params = ["%$cari%"];
$types = "s";

if ($kategori !== '') {
    $sql .= " AND kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}
$sql .= " ORDER BY id ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);
?>

    <div class="container">
        <h2>Daftar Kostum Tersedia</h2>

        <!-- ===== FORM PENCARIAN & FILTER KATEGORI ===== -->
        <form method="GET" class="form-card" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:20px;">
            <div style="flex:2; min-width:200px;">
                <label for="cari">Cari Nama Kostum</label>
                <input type="text" id="cari" name="cari" value="<?php echo htmlspecialchars($cari); ?>" placeholder="Contoh: Baju Bodo">
            </div>
            <div style="flex:1; min-width:160px;">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php while ($kt = mysqli_fetch_assoc($listKategori)): ?>
                        <option value="<?php echo htmlspecialchars($kt['kategori']); ?>" <?php echo ($kategori === $kt['kategori']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kt['kategori']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn" style="padding:12px 24px;">Cari</button>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Gambar</th><th>Nama Kostum</th><th>Kategori</th><th>Stok</th><th>Harga Sewa</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($data) === 0): ?>
                    <tr><td colspan="7">Tidak ada kostum yang cocok dengan pencarian.</td></tr>
                    <?php endif; ?>
                    <?php while ($k = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td><span class="badge">K<?php echo str_pad($k['id'], 3, '0', STR_PAD_LEFT); ?></span></td>
                        <td><img src="../gambar/kostum/<?php echo htmlspecialchars($k['gambar']); ?>" alt="<?php echo htmlspecialchars($k['nama_kostum']); ?>" class="thumb"></td>
                        <td><?php echo htmlspecialchars($k['nama_kostum']); ?></td>
                        <td><?php echo htmlspecialchars($k['kategori']); ?></td>
                        <td><?php echo (int) $k['stok']; ?> unit</td>
                        <td class="price"><?php echo format_rupiah($k['harga']); ?>/hari</td>
                        <td><a href="sewa.php?id=<?php echo $k['id']; ?>" class="action-link">Pilih</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
