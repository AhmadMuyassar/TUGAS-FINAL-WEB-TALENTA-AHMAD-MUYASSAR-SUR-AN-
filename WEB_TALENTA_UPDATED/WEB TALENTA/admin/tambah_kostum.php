<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

if (isset($_POST['simpan'])) {
    $nama = $_POST['nama'];
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $harga = (int) $_POST['harga'];
    $gambar = '';

    if (!empty($_FILES['gambar']['name'])) {
        $gambar = time() . '_' . basename($_FILES['gambar']['name']);
        move_uploaded_file($_FILES['gambar']['tmp_name'], __DIR__ . '/../image/' . $gambar);
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO inventaris (nama_kostum, kategori, stok, harga, gambar) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssiis", $nama, $kategori, $stok, $harga, $gambar);
    mysqli_stmt_execute($stmt);

    header("location:inventaris_admin.php");
    exit();
}

$root = '../';
$page_title = 'Tambah Kostum';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container">
        <h2>Tambah Kostum Baru</h2>

        <div class="form-card" style="max-width:500px;">
            <form method="POST" enctype="multipart/form-data">
                <label for="nama">Nama Kostum</label>
                <input type="text" id="nama" name="nama" required>

                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" placeholder="Contoh: Tradisional Sulawesi" required>

                <label for="stok">Stok</label>
                <input type="number" id="stok" name="stok" min="0" required>

                <label for="harga">Harga Sewa / Hari (Rp)</label>
                <input type="number" id="harga" name="harga" min="0" required>

                <label for="gambar">Gambar Kostum</label>
                <input type="file" id="gambar" name="gambar" accept="image/*">
                <img id="preview-gambar" alt="Preview" style="display:none; margin-top:10px; max-width:160px; border-radius:6px;">

                <button type="submit" name="simpan" class="btn" style="width:100%; margin-top:24px; padding:14px;">Simpan Kostum</button>
            </form>
        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
