<?php
// =====================================================================
// admin/kelola_sewa.php — Satu file untuk List + Konfirmasi Sewa +
// Verifikasi Pembayaran + Hapus, dibedakan lewat parameter ?aksi=
// =====================================================================
session_start();
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/fungsi.php';
wajib_admin();

$aksi = $_GET['aksi'] ?? '';

// ----- PROSES SIMPAN KONFIRMASI STATUS SEWA -----
if ($aksi === 'konfirmasi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) $_POST['id'];
    $status = ($_POST['status'] === 'ditolak') ? 'ditolak' : 'diterima';
    $catatan_admin = trim($_POST['catatan_admin'] ?? '');

    if ($status === 'ditolak' && $catatan_admin === '') {
        header("location:kelola_sewa.php?aksi=konfirmasi&id=$id&error=1");
        exit();
    }
    $catatan_simpan = ($status === 'ditolak') ? $catatan_admin : null;

    // Ambil data sewa sebelumnya untuk tahu status lama & jumlah
    $prev = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT status, jumlah, id_kostum FROM sewa WHERE id=$id"));

    $stmt = mysqli_prepare($conn, "UPDATE sewa SET status=?, catatan_admin=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssi", $status, $catatan_simpan, $id);
    mysqli_stmt_execute($stmt);

    // Sesuaikan stok inventaris berdasarkan perubahan status
    if ($prev) {
        $status_lama = $prev['status'];
        $jumlah      = (int) $prev['jumlah'];
        $id_kostum   = (int) $prev['id_kostum'];

        if ($status === 'diterima' && $status_lama !== 'diterima') {
            // Baru diterima → kurangi stok
            $upd = mysqli_prepare($conn, "UPDATE inventaris SET stok = GREATEST(stok - ?, 0) WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ii", $jumlah, $id_kostum);
            mysqli_stmt_execute($upd);
        } elseif ($status === 'ditolak' && $status_lama === 'diterima') {
            // Diterima lalu dibatalkan/ditolak → kembalikan stok
            $upd = mysqli_prepare($conn, "UPDATE inventaris SET stok = stok + ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "ii", $jumlah, $id_kostum);
            mysqli_stmt_execute($upd);
        }
    }

    // Notifikasi status ke pengguna
    $info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT users.email, inventaris.nama_kostum
                                                      FROM sewa JOIN users ON sewa.id_user=users.id
                                                      JOIN inventaris ON sewa.id_kostum=inventaris.id
                                                      WHERE sewa.id=$id"));
    if ($info) {
        kirim_notifikasi_status($info['email'], $info['nama_kostum'], $status, $catatan_simpan ?? '');
    }

    header("location:kelola_sewa.php");
    exit();
}

// ----- VERIFIKASI PEMBAYARAN (tandai lunas) -----
if ($aksi === 'verifikasi_bayar') {
    $id = (int) ($_GET['id'] ?? 0);
    $stmt = mysqli_prepare($conn, "UPDATE sewa SET status_bayar='lunas' WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("location:kelola_sewa.php");
    exit();
}

// ----- HAPUS DATA SEWA -----
if ($aksi === 'hapus') {
    $id = (int) ($_GET['id'] ?? 0);

    // Jika sewa berstatus diterima, kembalikan stok sebelum dihapus
    $prev = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT status, jumlah, id_kostum FROM sewa WHERE id=$id"));
    if ($prev && $prev['status'] === 'diterima') {
        $jumlah    = (int) $prev['jumlah'];
        $id_kostum = (int) $prev['id_kostum'];
        $upd = mysqli_prepare($conn, "UPDATE inventaris SET stok = stok + ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, "ii", $jumlah, $id_kostum);
        mysqli_stmt_execute($upd);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM sewa WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    header("location:kelola_sewa.php");
    exit();
}

// ----- DATA UNTUK FORM KONFIRMASI -----
$dataKonfirmasi = null;
if ($aksi === 'konfirmasi' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $dataKonfirmasi = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT sewa.*, users.username, users.nama_lengkap, users.no_hp,
                users.jenis_identitas, users.nomor_identitas, users.foto_identitas,
                inventaris.nama_kostum
         FROM sewa
         JOIN users ON sewa.id_user = users.id
         JOIN inventaris ON sewa.id_kostum = inventaris.id
         WHERE sewa.id = $id"));
}

$root = '../';
$page_title = 'Data Penyewaan';
require_once __DIR__ . '/../includes/header.php';
?>

    <div class="container">

    <?php if ($aksi === 'konfirmasi' && $dataKonfirmasi): ?>
        <!-- ===== FORM KONFIRMASI STATUS SEWA ===== -->
        <h2>Konfirmasi Penyewaan</h2>
        <?php if (isset($_GET['error'])): ?>
            <p style="color:#c62828; margin-bottom:16px;">Catatan penolakan wajib diisi jika status Ditolak.</p>
        <?php endif; ?>

        <div class="form-card" style="max-width:520px;">
            <label>Penyewa</label>
            <input type="text" value="<?php echo htmlspecialchars($dataKonfirmasi['username']); ?> — <?php echo htmlspecialchars($dataKonfirmasi['nama_lengkap']); ?>" disabled>
            <label>No. HP</label>
            <input type="text" value="<?php echo htmlspecialchars($dataKonfirmasi['no_hp']); ?>" disabled>
            <label>Identitas</label>
            <input type="text" value="<?php echo htmlspecialchars($dataKonfirmasi['jenis_identitas']); ?> — <?php echo htmlspecialchars($dataKonfirmasi['nomor_identitas']); ?>" disabled>
            <?php if (!empty($dataKonfirmasi['foto_identitas'])): ?>
            <label>Foto Identitas</label>
            <a href="../gambar/identitas/<?php echo htmlspecialchars($dataKonfirmasi['foto_identitas']); ?>" target="_blank">
                <img src="../gambar/identitas/<?php echo htmlspecialchars($dataKonfirmasi['foto_identitas']); ?>"
                     style="max-width:100%; border-radius:6px; border:1px solid #e5dcc8; margin-top:4px;">
            </a>
            <?php endif; ?>
            <label>Kostum</label>
            <input type="text" value="<?php echo htmlspecialchars($dataKonfirmasi['nama_kostum']); ?>" disabled>
            <label>Jumlah / Lama</label>
            <input type="text" value="<?php echo (int) $dataKonfirmasi['jumlah']; ?> set / <?php echo (int) $dataKonfirmasi['lama_sewa']; ?> hari" disabled>

            <form method="POST" action="kelola_sewa.php?aksi=konfirmasi" id="form-konfirmasi">
                <input type="hidden" name="id" value="<?php echo $dataKonfirmasi['id']; ?>">
                <label for="status">Status Konfirmasi</label>
                <select id="status" name="status" required>
                    <option value="diterima" <?php echo $dataKonfirmasi['status'] === 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                    <option value="ditolak" <?php echo $dataKonfirmasi['status'] === 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                </select>
                <div id="wrap-catatan-admin">
                    <label for="catatan_admin">Catatan Penolakan (wajib jika Ditolak)</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="3"><?php echo htmlspecialchars($dataKonfirmasi['catatan_admin'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn" style="width:100%; margin-top:24px; padding:14px;">Simpan Konfirmasi</button>
            </form>
        </div>

    <?php else: ?>
        <!-- ===== DAFTAR SEWA + STATUS BAYAR ===== -->
        <h2>Data Penyewaan</h2>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Penyewa</th><th>Kostum</th><th>Jml/Lama</th><th>Status Sewa</th><th>Metode Bayar</th><th>Status Bayar</th><th>Bukti</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $q = mysqli_query($conn, "SELECT sewa.*, users.username, inventaris.nama_kostum
                                                 FROM sewa JOIN users ON sewa.id_user=users.id
                                                 JOIN inventaris ON sewa.id_kostum=inventaris.id
                                                 ORDER BY sewa.id DESC"); ?>
                <?php if (mysqli_num_rows($q) === 0): ?>
                <tr><td colspan="8">Belum ada data penyewaan.</td></tr>
                <?php endif; ?>
                <?php while ($d = mysqli_fetch_assoc($q)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['username']); ?></td>
                    <td><?php echo htmlspecialchars($d['nama_kostum']); ?></td>
                    <td><?php echo (int) $d['jumlah']; ?> / <?php echo (int) $d['lama_sewa']; ?>hr</td>
                    <td><span class="status-badge <?php echo $d['status']; ?>"><?php echo ucfirst($d['status']); ?></span></td>
                    <td><?php echo $d['metode_bayar'] ? ucfirst($d['metode_bayar']) : '-'; ?></td>
                    <td><span class="status-badge <?php echo $d['status_bayar'] === 'lunas' ? 'diterima' : 'menunggu'; ?>"><?php echo ucfirst(str_replace('_',' ',$d['status_bayar'])); ?></span></td>
                    <td>
                        <?php if ($d['bukti_transfer']): ?>
                            <a href="../gambar/bukti_transfer/<?php echo htmlspecialchars($d['bukti_transfer']); ?>" target="_blank">Lihat</a>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td>
                        <a href="kelola_sewa.php?aksi=konfirmasi&id=<?php echo $d['id']; ?>" class="btn-sm btn-success">Konfirmasi</a>
                        <?php if ($d['status_bayar'] === 'menunggu_verifikasi'): ?>
                            <a href="kelola_sewa.php?aksi=verifikasi_bayar&id=<?php echo $d['id']; ?>" class="btn-sm btn-success" data-confirm="Tandai pembayaran ini LUNAS?">Verifikasi Bayar</a>
                        <?php endif; ?>
                        <a href="kelola_sewa.php?aksi=hapus&id=<?php echo $d['id']; ?>" class="btn-sm btn-danger" data-confirm="Yakin hapus data penyewaan ini?">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    </div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
