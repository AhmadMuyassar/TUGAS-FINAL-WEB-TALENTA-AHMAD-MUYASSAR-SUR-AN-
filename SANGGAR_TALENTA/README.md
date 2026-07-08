# Talenta Project — Sistem Penyewaan Kostum Sanggar Seni
Akun default admin: **admin123 / admin123**


## Struktur Folder
```
SANGGAR_TALENTA/
├── admin/                 -> Halaman khusus admin
│   ├── dashboard.php
│   ├── kelola_kostum.php  -> CRUD kostum (list+tambah+edit+hapus jadi 1 file, via ?aksi=)
│   ├── kelola_sewa.php    -> Konfirmasi sewa + verifikasi pembayaran + hapus
│   └── kelola_konten.php  -> CRUD Pelatihan / Hasil Karya / Event (via ?tab=)
│
├── pengguna/               -> Halaman untuk pengunjung & pengguna
│   ├── dashboard.php      -> Beranda publik (bisa diakses tanpa login)
│   ├── kostum.php         -> Daftar kostum + pencarian & filter kategori
│   ├── sewa.php           -> Form sewa + deskripsi kostum + syarat & ketentuan
│   ├── riwayat.php        -> Riwayat sewa pengguna (wajib login)
│   ├── pembayaran.php     -> Pilih Cash / Transfer (QRIS)
│   ├── login.php
│   ├── daftar.php         -> Registrasi + biodata lengkap
│   └── logout.php
│
├── proses/                 -> File pemroses form (tidak menampilkan HTML)
│   ├── proses_login.php
│   ├── proses_daftar.php
│   ├── proses_sewa.php    -> Simpan sewa + kirim notifikasi email ke admin
│   └── proses_bayar.php
│
├── includes/
│   ├── koneksi.php        -> Satu-satunya koneksi database
│   ├── fungsi.php         -> Helper: cek login, notifikasi email, format rupiah
│   ├── header.php
│   └── footer.php
│
├── gambar/
│   ├── kostum/            -> Foto kostum
│   ├── pelatihan/         -> Foto pelatihan
│   ├── karya/              -> Foto hasil karya
│   ├── event/              -> Foto event/prestasi
│   ├── qris/               -> Gambar QRIS pembayaran
│   └── bukti_transfer/     -> Upload bukti transfer pengguna
│
├── assets/
│   ├── style.css
│   └── script.js
│
├── buat_db.php             -> Setup database (jalankan sekali)
└── index.php               -> Redirect ke pengguna/dashboard.php
