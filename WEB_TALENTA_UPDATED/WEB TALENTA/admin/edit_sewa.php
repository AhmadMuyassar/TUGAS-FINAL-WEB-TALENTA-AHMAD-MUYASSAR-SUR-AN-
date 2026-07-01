<?php
// =====================================================================
// admin/edit_sewa.php — Konfirmasi Penyewaan (sisi admin)
// Halaman ini menggantikan form "edit bebas" sebelumnya. Admin hanya
// bisa memilih status Diterima/Ditolak; catatan admin WAJIB diisi
// jika statusnya Ditolak (alasan penolakan untuk pengguna).
// =====================================================================
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("location:../login.php");
    exit();
}
require_once __DIR__ . '/../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);

// ===== 1. PROSES SIMPAN KONFIRMASI (SAAT FORM DIKIRIM) =====
if (isset($_POST['konfirmasi'])) {
    $status = ($_POST['status'] === 'ditolak') ? 'ditolak' : 'diterima';
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');

    // Validasi: catatan wajib diisi jika penyewaan ditolak
    if ($status === 'ditolak' && $catatan_admin === '') {
        header("location:edit_sewa.php?id=" . $id . "&error=1");
        exit();
    }

    // Jika diterima, catatan admin tidak diperlukan
    $catatan_admin_simpan = ($status === 'ditolak') ? $catatan_admin : null;

    $stmt = mysqli_prepare($conn, "UPDATE sewa SET status = ?, catatan_admin = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $status, $catatan_admin_simpan, $id);
    mysqli_stmt_execute($stmt);

    header("location:data_sewa.php");
    exit();
}

// ===== 2. AMBIL DATA PENYEWAAN + JOIN USER & KOSTUM (UNTUK DITAMPILKAN) =====
$stmt = mysqli_prepare($conn, "SELECT sewa.*, users.username, inventaris.nama_kostum
                                FROM sewa
                                JOIN users ON sewa.id_user = users.id
                                JOIN inventaris ON sewa.id_kostum = inventaris.id
                                WHERE sewa.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    header("location:data_sewa.php");
    exit();
}

$root = '../';
$page_title = 'Konfirmasi Penyewaan';
require_once __DIR__ . '/../includes/header.php';

$statusSaatIni = $data['status'] ?? 'menunggu';
?>

    <div class="container">
        <h2>Konfirmasi Penyewaan</h2>

        <?php if (isset($_GET['error'])): ?>
            <p style="color:#c62828; margin-bottom:16px;">Catatan penolakan wajib diisi jika status Ditolak.</p>
        <?php endif; ?>

        <div class="form-card" style="max-width:520px;">

            <!-- ===== DETAIL PENYEWAAN (READ ONLY) ===== -->
            <label>Penyewa</label>
            <input type="text" value="<?php echo htmlspecialchars($data['username']); ?>" disabled>

            <label>Kostum</label>
            <input type="text" value="<?php echo htmlspecialchars($data['nama_kostum']); ?>" disabled>

            <label>Jumlah</label>
            <input type="text" value="<?php echo (int) $data['jumlah']; ?> set" disabled>

            <label>Lama Sewa</label>
            <input type="text" value="<?php echo (int) $data['lama_sewa']; ?> hari" disabled>

            <label>Catatan Penyewa</label>
            <textarea rows="2" disabled><?php echo htmlspecialchars($data['catatan'] !== '' ? $data['catatan'] : '-'); ?></textarea>

            <!-- ===== FORM KONFIRMASI: STATUS + CATATAN ADMIN ===== -->
            <form method="POST" id="form-konfirmasi">
                <label for="status">Status Konfirmasi</label>
                <select id="status" name="status" required>
                    <option value="diterima" <?php echo $statusSaatIni === 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                    <option value="ditolak" <?php echo $statusSaatIni === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                </select>

                <!-- Ditampilkan/disembunyikan otomatis oleh script.js sesuai pilihan status -->
                <div id="wrap-catatan-admin">
                    <label for="catatan_admin">Catatan Penolakan (wajib jika Ditolak)</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="3"
                              placeholder="Contoh: stok kosong pada tanggal tersebut."><?php echo htmlspecialchars($data['catatan_admin'] ?? ''); ?></textarea>
                </div>

                <button type="submit" name="konfirmasi" class="btn" style="width:100%; margin-top:24px; padding:14px;">Simpan Konfirmasi</button>
            </form>

        </div>
    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
