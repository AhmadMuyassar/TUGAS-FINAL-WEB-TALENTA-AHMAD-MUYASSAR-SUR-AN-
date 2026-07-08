<?php
// ================================================================
// buat_db.php — Jalankan file ini SATU KALI di browser untuk
// membuat database, seluruh tabel, dan data contoh awal.
// ================================================================

// ================================================================
// PROTEKSI: File ini hanya boleh diakses dari localhost.
// Setelah database selesai dibuat, hapus atau rename file ini.
// ================================================================
$allowed_ips = ['127.0.0.1', '::1', '::ffff:127.0.0.1'];
$client_ip   = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($client_ip, $allowed_ips, true)) {
    http_response_code(403);
    die('Akses ditolak.');
}
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "db_talenta";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS $dbname");
mysqli_select_db($conn, $dbname);
echo "Database siap.<br>";

// ================================================================
// 1. TABEL USERS — akun login + biodata lengkap (untuk registrasi)
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','pengguna') NOT NULL DEFAULT 'pengguna',
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    jenis_identitas VARCHAR(20) NOT NULL DEFAULT '',
    nomor_identitas VARCHAR(30) NOT NULL DEFAULT '',
    foto_profil VARCHAR(255) NOT NULL DEFAULT '',
    foto_identitas VARCHAR(255) NOT NULL DEFAULT '',
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "Tabel users siap.<br>";

// Tambah kolom baru jika tabel sudah ada tapi kolom belum ada (migrasi)
$kolom_users = ['jenis_identitas VARCHAR(20) NOT NULL DEFAULT \'\'',
                'nomor_identitas VARCHAR(30) NOT NULL DEFAULT \'\'',
                'foto_profil VARCHAR(255) NOT NULL DEFAULT \'\'',
                'foto_identitas VARCHAR(255) NOT NULL DEFAULT \'\''];
foreach ($kolom_users as $def) {
    $nama = explode(' ', $def)[0];
    @mysqli_query($conn, "ALTER TABLE users ADD COLUMN $def");
}

$cek = mysqli_query($conn, "SELECT id FROM users WHERE username='admin123'");
if (mysqli_num_rows($cek) == 0) {
    $hashAdmin = password_hash("admin123", PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (username,password,role,nama_lengkap,email,no_hp,alamat)
        VALUES ('admin123','$hashAdmin','admin','Administrator','admin@talenta.test','081200000000','Kantor Sanggar Talenta')");
}

// ================================================================
// 2. TABEL INVENTARIS — data kostum (+ deskripsi & flag favorit)
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS inventaris (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_kostum VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    stok INT(11) NOT NULL DEFAULT 0,
    harga INT(11) NOT NULL DEFAULT 0,
    gambar VARCHAR(255),
    favorit TINYINT(1) NOT NULL DEFAULT 0
)");
echo "Tabel inventaris siap.<br>";

$cekInv = mysqli_query($conn, "SELECT id FROM inventaris");
if (mysqli_num_rows($cekInv) == 0) {
    $seed = [
        ["Baju Bodo Modern", "Tradisional Sulawesi", "Kostum Baju Bodo dengan sentuhan modern, cocok untuk pertunjukan formal maupun modifikasi tari kreasi.", 5, 150000, "bajubodopinkmodrn.jpeg", 1],
        ["Kostum Tari Paduppa", "Tari Tradisional", "Kostum tari penyambutan khas Sulawesi Selatan, lengkap dengan aksesoris kepala.", 12, 100000, "paduppahijau.png", 1],
        ["Kostum Kreasi Baru", "Tari Modern", "Kostum untuk tari kreasi modern dengan desain warna cerah dan bahan ringan.", 8, 120000, "kreasibaru.png", 0],
        ["Kostum Tari 4 Etnis Modern", "Tradisional Sulawesi", "Kostum kolaborasi 4 etnis dengan modifikasi modern, cocok untuk pentas budaya.", 3, 150000, "4etnis.jpeg", 1],
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO inventaris (nama_kostum,kategori,deskripsi,stok,harga,gambar,favorit) VALUES (?,?,?,?,?,?,?)");
    foreach ($seed as $s) {
        mysqli_stmt_bind_param($stmt, "sssiisi", $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6]);
        mysqli_stmt_execute($stmt);
    }
}

// ================================================================
// 3. TABEL PELATIHAN
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS pelatihan (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_pelatihan VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    jadwal VARCHAR(100),
    gambar VARCHAR(255)
)");
echo "Tabel pelatihan siap.<br>";

$cekP = mysqli_query($conn, "SELECT id FROM pelatihan");
if (mysqli_num_rows($cekP) == 0) {
    mysqli_query($conn, "INSERT INTO pelatihan (nama_pelatihan,deskripsi,jadwal,gambar) VALUES
        ('Tari Tradisional Sulawesi','Pelatihan dasar hingga mahir tari tradisional khas Sulawesi Selatan.','Setiap Sabtu, 15.00 - 17.00', ''),
        ('Tari Kreasi Modern','Pelatihan koreografi kreasi modern untuk pertunjukan panggung.','Setiap Minggu, 09.00 - 11.00', '')");
}

// ================================================================
// 4. TABEL HASIL_KARYA — galeri (gambar tanpa harga)
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hasil_karya (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    gambar VARCHAR(255)
)");
echo "Tabel hasil_karya siap.<br>";

// ================================================================
// 5. TABEL EVENT_PRESTASI
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS event_prestasi (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    tanggal DATE,
    gambar VARCHAR(255)
)");
echo "Tabel event_prestasi siap.<br>";

// ================================================================
// 6. TABEL SEWA — transaksi penyewaan + status pembayaran
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS sewa (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    id_user INT(11) NOT NULL,
    id_kostum INT(11) NOT NULL,
    jumlah INT(11) NOT NULL,
    tanggal_sewa DATE,
    lama_sewa INT(11) NOT NULL,
    catatan TEXT,
    status ENUM('menunggu','diterima','ditolak') NOT NULL DEFAULT 'menunggu',
    catatan_admin TEXT NULL,
    metode_bayar ENUM('cash','transfer') NULL,
    status_bayar ENUM('belum_bayar','menunggu_verifikasi','lunas') NOT NULL DEFAULT 'belum_bayar',
    bukti_transfer VARCHAR(255) NULL,
    dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "Tabel sewa siap.<br>";

// ================================================================
// 7. TABEL HERO_SLIDE — slide yang tampil di beranda
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS hero_slide (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(100) NOT NULL,
    subjudul VARCHAR(200),
    gambar VARCHAR(255),
    link_btn VARCHAR(255) DEFAULT '',
    label_btn VARCHAR(50) DEFAULT 'Lihat Koleksi',
    urutan INT(11) DEFAULT 0,
    aktif TINYINT(1) DEFAULT 1
)");
echo "Tabel hero_slide siap.<br>";

// ================================================================
// 8. TABEL PROFIL_SANGGAR — konten about, statistik, CTA banner
//    Disimpan sebagai key-value: satu baris per setting.
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS profil_sanggar (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    kunci VARCHAR(80) NOT NULL UNIQUE,
    nilai TEXT,
    gambar VARCHAR(255)
)");
echo "Tabel profil_sanggar siap.<br>";

// Isi default jika kosong
$cekPS = mysqli_query($conn, "SELECT id FROM profil_sanggar");
if (mysqli_num_rows($cekPS) == 0) {
    $defaults = [
        ['tentang_judul',    'Sanggar Seni Talenta Project', ''],
        ['tentang_deskripsi','Talenta Project adalah pusat seni tari dan penyewaan kostum profesional yang lahir dari kecintaan terhadap budaya Sulawesi Selatan. Kami hadir untuk memajukan, melestarikan, dan menampilkan keindahan seni tradisional melalui pertunjukan, pelatihan, dan koleksi kostum pilihan.', ''],
        ['tentang_deskripsi2','Dengan pengalaman lebih dari satu dekade, kami telah melayani ratusan pementasan, festival budaya, dan acara resmi di berbagai kota.', ''],
        ['tentang_gambar',   '', ''],
        ['stat1_angka',      '10+', ''],
        ['stat1_label',      'Tahun Berdiri', ''],
        ['stat2_angka',      '500+', ''],
        ['stat2_label',      'Pementasan', ''],
        ['stat3_angka',      '200+', ''],
        ['stat3_label',      'Koleksi Kostum', ''],
        ['cta_judul',        'Temukan Kostum Impian Anda', ''],
        ['cta_deskripsi',    'Lebih dari 200 koleksi kostum siap menemani penampilan terbaik Anda — tari tradisional, kreasi modern, hingga busana adat.', ''],
    ];
    $stmtPS = mysqli_prepare($conn, "INSERT INTO profil_sanggar (kunci,nilai,gambar) VALUES (?,?,?)");
    foreach ($defaults as $d) {
        mysqli_stmt_bind_param($stmtPS, "sss", $d[0], $d[1], $d[2]);
        mysqli_stmt_execute($stmtPS);
    }
}
echo "Data default profil_sanggar siap.<br>";

// ================================================================
// 9. TABEL TIM_SANGGAR — profil pendiri & kru
// ================================================================
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tim_sanggar (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    peran VARCHAR(100) NOT NULL,
    bio TEXT,
    foto VARCHAR(255),
    link_wa VARCHAR(255),
    link_ig VARCHAR(255),
    link_tiktok VARCHAR(255),
    urutan INT(11) DEFAULT 0,
    is_founder TINYINT(1) DEFAULT 0
)");
echo "Tabel tim_sanggar siap.<br>";

// Folder gambar tim
if (!is_dir(__DIR__ . '/gambar/tim')) { mkdir(__DIR__ . '/gambar/tim', 0755, true); }

echo "<br><b>Selesai!</b> Akun default: admin123 / admin123 (role admin).";
mysqli_close($conn);
