<?php
// =====================================================================
// admin/kelola_kostum.php — Satu file untuk List + Tambah + Edit + Hapus
// kostum, dibedakan lewat parameter ?aksi=
//   (kosong)   -> daftar kostum
//   aksi=form  -> tampilkan form tambah (atau edit jika ada &id=)
//   aksi=simpan-> proses simpan (POST) untuk tambah maupun edit
//   aksi=hapus -> hapus data (&id=)
// =====================================================================
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_admin();

$aksi = $_GET['aksi'] ?? '';

// ----- PROSES SIMPAN (tambah / update) -----
if ($aksi === 'simpan' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int) ($_POST['id'] ?? 0);
    $nama     = trim($_POST['nama']);
    $kategori = trim($_POST['kategori']);
    $deskripsi= trim($_POST['deskripsi']);
    $stok     = (int) $_POST['stok'];
    $harga    = (int) $_POST['harga'];
    $favorit  = isset($_POST['favorit']) ? 1 : 0;

    $gambar = $_POST['gambar_lama'] ?? '';
    if (!empty($_FILES['gambar']['name'])) {
        $hasil = upload_gambar('gambar', 'kostum', $gambar);
        if (is_array($hasil)) {
            $error_msg = urlencode($hasil['error']);
            $redirect = $id > 0 ? "kelola_kostum.php?aksi=form&id=$id&error=$error_msg" : "kelola_kostum.php?aksi=form&error=$error_msg";
            header("location:$redirect");
            exit();
        }
        $gambar = $hasil;
    }

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE inventaris SET nama_kostum=?, kategori=?, deskripsi=?, stok=?, harga=?, gambar=?, favorit=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssiisii", $nama, $kategori, $deskripsi, $stok, $harga, $gambar, $favorit, $id);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO inventaris (nama_kostum,kategori,deskripsi,stok,harga,gambar,favorit) VALUES (?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssiisi", $nama, $kategori, $deskripsi, $stok, $harga, $gambar, $favorit);
    }
    mysqli_stmt_execute($stmt);
    header("location:kelola_kostum.php");
    exit();
}

// ----- PROSES HAPUS -----
if ($aksi === 'hapus') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "SELECT gambar FROM inventaris WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $d = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($d && $d['gambar'] && file_exists(__DIR__ . '/../gambar/kostum/' . $d['gambar'])) {
        unlink(__DIR__ . '/../gambar/kostum/' . $d['gambar']);
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM inventaris WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("location:kelola_kostum.php");
    exit();
}

// ----- DATA UNTUK FORM EDIT (jika ada) -----
$dataEdit = null;
if ($aksi === 'form' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $dataEdit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM inventaris WHERE id=$id"));
}

$root = '../';
$page_title = 'Kelola Kostum';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container">

    <?php if ($aksi === 'form'): ?>
        <!-- ===== FORM TAMBAH / EDIT ===== -->
        <h2><?php echo $dataEdit ? 'Edit Kostum' : 'Tambah Kostum Baru'; ?></h2>
        <div class="form-card" style="max-width:500px;">
            <?php if (isset($_GET['error'])): ?>
                <p style="color:#c62828; margin-bottom:12px; font-size:0.85rem;"><?php echo htmlspecialchars($_GET['error']); ?></p>
            <?php endif; ?>
            <form method="POST" action="kelola_kostum.php?aksi=simpan" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $dataEdit['id'] ?? 0; ?>">
                <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($dataEdit['gambar'] ?? ''); ?>">

                <label for="nama">Nama Kostum</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($dataEdit['nama_kostum'] ?? ''); ?>" required>

                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" value="<?php echo htmlspecialchars($dataEdit['kategori'] ?? ''); ?>" required>

                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($dataEdit['deskripsi'] ?? ''); ?></textarea>

                <label for="stok">Stok</label>
                <input type="number" id="stok" name="stok" min="0" value="<?php echo $dataEdit['stok'] ?? 0; ?>" required>

                <label for="harga">Harga Sewa / Hari (Rp)</label>
                <input type="number" id="harga" name="harga" min="0" value="<?php echo $dataEdit['harga'] ?? 0; ?>" required>

                <label for="gambar">Gambar Kostum <?php echo $dataEdit ? '(kosongkan jika tidak ganti)' : ''; ?></label>
                <input type="file" id="gambar" name="gambar" accept="image/*">
                <?php if ($dataEdit && $dataEdit['gambar']): ?>
                    <img src="../gambar/kostum/<?php echo htmlspecialchars($dataEdit['gambar']); ?>" style="max-width:140px; margin-top:8px; border-radius:6px;">
                <?php endif; ?>

                <label style="display:flex; align-items:center; gap:8px; margin-top:12px;">
                    <input type="checkbox" name="favorit" style="width:auto;" <?php echo (!empty($dataEdit['favorit'])) ? 'checked' : ''; ?>>
                    Tampilkan sebagai Kostum Favorit di beranda
                </label>

                <button type="submit" class="btn" style="width:100%; margin-top:24px; padding:14px;">Simpan</button>
            </form>
        </div>

    <?php else: ?>
        <!-- ===== DAFTAR KOSTUM ===== -->
        <div class="admin-toolbar">
            <h2 style="margin:0; border:none; padding:0;">Kelola Inventaris Kostum</h2>
            <a href="kelola_kostum.php?aksi=form" class="btn-sm btn-success">+ Tambah Kostum</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead><tr><th>Nama</th><th>Kategori</th><th>Stok</th><th>Harga</th><th>Gambar</th><th>Favorit</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $q = mysqli_query($conn, "SELECT * FROM inventaris ORDER BY id ASC"); ?>
                <?php if (mysqli_num_rows($q) === 0): ?>
                <tr><td colspan="7">Belum ada data kostum.</td></tr>
                <?php endif; ?>
                <?php while ($d = mysqli_fetch_assoc($q)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['nama_kostum']); ?></td>
                    <td><?php echo htmlspecialchars($d['kategori']); ?></td>
                    <td><?php echo (int) $d['stok']; ?> unit</td>
                    <td><?php echo format_rupiah($d['harga']); ?></td>
                    <td><img src="../gambar/kostum/<?php echo htmlspecialchars($d['gambar']); ?>" class="thumb"></td>
                    <td><?php echo $d['favorit'] ? '✅' : '-'; ?></td>
                    <td>
                        <a href="kelola_kostum.php?aksi=form&id=<?php echo $d['id']; ?>" class="btn-sm btn-success">Edit</a>
                        <a href="kelola_kostum.php?aksi=hapus&id=<?php echo $d['id']; ?>" class="btn-sm btn-danger" data-confirm="Yakin hapus kostum ini?">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
