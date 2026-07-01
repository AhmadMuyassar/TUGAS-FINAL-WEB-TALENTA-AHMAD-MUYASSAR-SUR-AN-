<?php
// =====================================================================
// admin/edit_kostum.php — Edit Kostum (sisi admin)
// Layout memakai .sewa-layout / .sewa-preview-box (sama dengan
// sewa.php) supaya gambar kostum tampil jelas di samping formulir,
// sejajar dengan tinggi card formulirnya.
// =====================================================================
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// ===== 1. AMBIL DATA KOSTUM YANG AKAN DIEDIT =====
$stmtGet = mysqli_prepare($conn, "SELECT * FROM inventaris WHERE id = ?");
mysqli_stmt_bind_param($stmtGet, "i", $id);
mysqli_stmt_execute($stmtGet);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));

if (!$data) {
    header("location:inventaris_admin.php");
    exit();
}

// ===== 2. PROSES SIMPAN PERUBAHAN =====
if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $harga = (int) $_POST['harga'];

    if (!empty($_FILES['gambar']['name'])) {
        // Gambar baru diunggah -> ganti nama file & update kolom gambar
        $gambar = time() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], __DIR__ . '/../image/' . $gambar);

        $stmt = mysqli_prepare($conn, "UPDATE inventaris SET nama_kostum=?, kategori=?, stok=?, harga=?, gambar=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssiisi", $nama, $kategori, $stok, $harga, $gambar, $id);
    } else {
        // Tidak ganti gambar -> gambar lama tetap dipakai
        $stmt = mysqli_prepare($conn, "UPDATE inventaris SET nama_kostum=?, kategori=?, stok=?, harga=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssiii", $nama, $kategori, $stok, $harga, $id);
    }
    mysqli_stmt_execute($stmt);

    header("location:inventaris_admin.php");
    exit();
}

$root = '../';
$page_title = 'Edit Kostum';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container">
        <h2>Edit Kostum</h2>

        <!-- ===== LAYOUT: FORM EDIT (KIRI) + PREVIEW GAMBAR (KANAN) =====
             Sama seperti halaman Sewa Kostum pengguna, supaya gambar
             kostum terlihat jelas dan sejajar dengan card formulir. -->
        <div class="sewa-layout">

            <!-- ===== KOLOM KIRI: FORM EDIT ===== -->
            <div class="form-card">
                <form method="POST" enctype="multipart/form-data">
                    <label for="nama">Nama Kostum</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($data['nama_kostum']); ?>" required>

                    <label for="kategori">Kategori</label>
                    <input type="text" id="kategori" name="kategori" value="<?php echo htmlspecialchars($data['kategori']); ?>" required>

                    <label for="stok">Stok</label>
                    <input type="number" id="stok" name="stok" min="0" value="<?php echo (int) $data['stok']; ?>" required>

                    <label for="harga">Harga Sewa / Hari (Rp)</label>
                    <input type="number" id="harga" name="harga" min="0" value="<?php echo (int) $data['harga']; ?>" required>

                    <label for="gambar">Ganti Gambar (opsional)</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">

                    <button type="submit" name="update" class="btn" style="width:100%; margin-top:24px; padding:14px;">Update Data</button>
                </form>
            </div>

            <!-- ===== KOLOM KANAN: PREVIEW GAMBAR KOSTUM =====
                 Gambar diperbarui otomatis via script.js saat admin
                 memilih file baru pada input#gambar (lihat bagian 5
                 di assets/script.js). -->
            <div class="sewa-preview-box">
                <img id="preview-gambar"
                     src="../image/<?php echo htmlspecialchars($data['gambar']); ?>"
                     alt="Preview <?php echo htmlspecialchars($data['nama_kostum']); ?>">
                <p><?php echo htmlspecialchars($data['nama_kostum']); ?></p>
                <p class="sewa-preview-harga">Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?>/hari</p>
            </div>

        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
