<?php
// ================================================================
// includes/fungsi.php — Kumpulan fungsi bantu yang dipakai di
// banyak halaman (pengguna & admin).
// ================================================================

// Format angka jadi Rupiah, contoh: 150000 -> "Rp 150.000"
function format_rupiah($angka) {
    return "Rp " . number_format((float) $angka, 0, ',', '.');
}

// Apakah pengguna sedang login?
function sudah_login() {
    return isset($_SESSION['username']);
}

// Apakah yang login adalah admin?
function is_admin() {
    return sudah_login() && $_SESSION['role'] === 'admin';
}

// Wajibkan login pengguna biasa. Jika belum login, redirect ke
// halaman login dan simpan halaman tujuan supaya bisa kembali
// otomatis setelah berhasil login.
function wajib_login($halaman_kembali) {
    $naik = str_repeat('../', substr_count(ltrim($_SERVER['SCRIPT_NAME'], '/'), '/'));
    if (!sudah_login()) {
        header("location: " . $naik . "pengguna/login.php?redirect=" . urlencode($halaman_kembali));
        exit();
    }
}

// Tidak ada wajib_pengguna — admin boleh akses halaman pengguna.
// Fungsi ini hanya memastikan sudah login (semua role).
function wajib_pengguna() {
    // Admin boleh akses halaman pengguna — tidak ada blokir.
    // Fungsi ini dibiarkan kosong sebagai placeholder.
}

// Wajibkan login sebagai admin.
// - Belum login           → redirect ke halaman login
// - Login tapi bukan admin → redirect ke beranda + pesan akses ditolak
function wajib_admin() {
    $naik = str_repeat('../', substr_count(ltrim($_SERVER['SCRIPT_NAME'], '/'), '/'));
    if (!sudah_login()) {
        header("location: " . $naik . "pengguna/login.php");
        exit();
    }
    if (!is_admin()) {
        header("location: " . $naik . "pengguna/dashboard.php?akses=ditolak");
        exit();
    }
}

// Helper path relatif dari root project, dipakai supaya link tetap
// benar baik diakses dari root, /pengguna/, maupun /admin/.
function url($path) {
    return base_root() . $path;
}
function base_root() {
    // $root diset di setiap halaman ('' jika di root, '../' jika di subfolder)
    global $root;
    return isset($root) ? $root : '';
}

// ================================================================
// UPLOAD GAMBAR — validasi ekstensi + MIME, simpan dengan nama acak.
// Kembalikan nama file baru, atau false jika gagal/tidak ada file.
//
// $field      : nama key di $_FILES
// $folder     : sub-folder di bawah gambar/ (tanpa slash, contoh: 'kostum')
// $nama_lama  : nama file lama (opsional) — jika diisi & file baru ada,
//               file lama akan dihapus otomatis setelah upload sukses.
// ================================================================
function upload_gambar($field, $folder, $nama_lama = '') {
    // Tidak ada file yang diupload → kembalikan false (biarkan caller pakai nama lama)
    if (empty($_FILES[$field]['name'])) {
        return false;
    }

    $ekstensi_ok = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $mime_ok     = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    $tmp  = $_FILES[$field]['tmp_name'];
    $ori  = $_FILES[$field]['name'];
    $ext  = strtolower(pathinfo($ori, PATHINFO_EXTENSION));

    // 1. Cek ekstensi
    if (!in_array($ext, $ekstensi_ok, true)) {
        return ['error' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, WEBP, atau GIF.'];
    }

    // 2. Cek MIME type dari isi file (bukan dari nama/header browser)
    $mime = mime_content_type($tmp);
    if (!in_array($mime, $mime_ok, true)) {
        return ['error' => 'File bukan gambar yang valid.'];
    }

    // 3. Batas ukuran 5 MB
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) {
        return ['error' => 'Ukuran file maksimal 5 MB.'];
    }

    // 4. Simpan dengan nama acak (hindari overwrite & path traversal)
    $nama_baru = bin2hex(random_bytes(12)) . '.' . $ext;
    $tujuan    = __DIR__ . '/../gambar/' . $folder . '/' . $nama_baru;

    if (!move_uploaded_file($tmp, $tujuan)) {
        return ['error' => 'Gagal menyimpan file. Periksa izin folder.'];
    }

    // 5. Hapus file lama jika ada & upload baru berhasil
    if ($nama_lama !== '') {
        $path_lama = __DIR__ . '/../gambar/' . $folder . '/' . $nama_lama;
        if (file_exists($path_lama)) {
            unlink($path_lama);
        }
    }

    return $nama_baru;
}

// ================================================================
// NOTIFIKASI EMAIL KE ADMIN saat ada penyewaan baru.
// Catatan: fungsi mail() bawaan PHP butuh server SMTP aktif
// (di hosting biasanya sudah otomatis jalan; di localhost/XAMPP
// perlu dikonfigurasi dulu, misalnya pakai PHPMailer + SMTP Gmail
// untuk hasil yang lebih pasti terkirim).
// ================================================================
function kirim_notifikasi_sewa_baru($email_admin, $nama_penyewa, $nama_kostum, $jumlah, $lama, $link_konfirmasi) {
    $subjek = "Penyewaan Baru: $nama_penyewa - $nama_kostum";
    $isi = "Ada penyewaan kostum baru yang menunggu konfirmasi.\n\n"
         . "Penyewa   : $nama_penyewa\n"
         . "Kostum    : $nama_kostum\n"
         . "Jumlah    : $jumlah set\n"
         . "Lama Sewa : $lama hari\n\n"
         . "Silakan konfirmasi melalui link berikut:\n$link_konfirmasi\n";
    $header = "From: no-reply@talentaproject.test";

    // @ dipakai agar tidak menampilkan warning jika server belum
    // dikonfigurasi SMTP-nya (supaya alur tetap lanjut walau email gagal).
    @mail($email_admin, $subjek, $isi, $header);
}

// Notifikasi status penyewaan (diterima/ditolak) ke pengguna.
function kirim_notifikasi_status($email_pengguna, $nama_kostum, $status, $catatan_admin = '') {
    $subjek = "Status Penyewaan $nama_kostum: " . strtoupper($status);
    $isi = "Penyewaan kostum \"$nama_kostum\" Anda telah di-$status oleh admin.\n";
    if ($status === 'ditolak' && $catatan_admin !== '') {
        $isi .= "Catatan admin: $catatan_admin\n";
    }
    $header = "From: no-reply@talentaproject.test";
    @mail($email_pengguna, $subjek, $isi, $header);
}
