// assets/script.js
// JavaScript utama Talenta Project: validasi form + konfirmasi hapus + highlight menu aktif

document.addEventListener('DOMContentLoaded', function () {

    // 1. Highlight menu navigasi yang sedang aktif
    var currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('nav a').forEach(function (link) {
        var linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage) {
            link.style.color = '#d4af37';
            link.style.borderColor = '#d4af37';
        }
    });

    // 2. Validasi form penyewaan (sewa.php)
    var formSewa = document.getElementById('form-sewa');
    if (formSewa) {
        formSewa.addEventListener('submit', function (e) {
            var jumlah = parseInt(formSewa.jumlah.value, 10);
            var lama = parseInt(formSewa.lama.value, 10);

            if (jumlah < 1) {
                e.preventDefault();
                alert('Jumlah kostum minimal 1.');
                return;
            }
            if (lama < 1) {
                e.preventDefault();
                alert('Lama sewa minimal 1 hari.');
                return;
            }
        });
    }

    // 2b. Preview gambar kostum yang dipilih di formulir sewa (sewa.php)
    var selectKostum = document.getElementById('id_kostum');
    var previewImg = document.getElementById('sewa-preview-img');
    var previewNama = document.getElementById('sewa-preview-nama');
    var previewHarga = document.getElementById('sewa-preview-harga');

    if (selectKostum && previewImg) {
        function updateSewaPreview() {
            var opt = selectKostum.options[selectKostum.selectedIndex];
            if (!opt) return;
            var gambar = opt.getAttribute('data-gambar');
            if (gambar) {
                previewImg.src = 'image/' + gambar;
                previewImg.style.display = 'block';
            }
            if (previewNama) previewNama.textContent = opt.getAttribute('data-nama') || '';
            if (previewHarga) previewHarga.textContent = opt.getAttribute('data-harga') || '';
        }
        selectKostum.addEventListener('change', updateSewaPreview);
        updateSewaPreview(); // tampilkan preview kostum yang terpilih pertama kali
    }

    // 3. Validasi form login (username & password tidak boleh kosong/spasi)
    var formLogin = document.getElementById('form-login');
    if (formLogin) {
        formLogin.addEventListener('submit', function (e) {
            var u = formLogin.username.value.trim();
            var p = formLogin.password.value.trim();
            if (u === '' || p === '') {
                e.preventDefault();
                alert('Username dan password wajib diisi.');
            }
        });
    }

    // 4. Konfirmasi sebelum menghapus data (dipakai lewat atribut data-confirm)
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // 5. Preview gambar sebelum upload (form tambah/edit kostum admin)
    var inputGambar = document.querySelector('input[name="gambar"]');
    var previewUpload = document.getElementById('preview-gambar');
    if (inputGambar && previewUpload) {
        inputGambar.addEventListener('change', function () {
            if (inputGambar.files && inputGambar.files[0]) {
                previewUpload.src = URL.createObjectURL(inputGambar.files[0]);
                previewUpload.style.display = 'block';
            }
        });
    }

    // 6. Form Konfirmasi Penyewaan (admin/edit_sewa.php)
    //    Kolom "Catatan Penolakan" hanya ditampilkan & wajib diisi
    //    saat admin memilih status "Ditolak".
    var selectStatus = document.getElementById('status');
    var wrapCatatanAdmin = document.getElementById('wrap-catatan-admin');
    var inputCatatanAdmin = document.getElementById('catatan_admin');

    if (selectStatus && wrapCatatanAdmin && inputCatatanAdmin) {
        function toggleCatatanAdmin() {
            var isDitolak = selectStatus.value === 'ditolak';
            wrapCatatanAdmin.style.display = isDitolak ? 'block' : 'none';
            inputCatatanAdmin.required = isDitolak;
        }
        selectStatus.addEventListener('change', toggleCatatanAdmin);
        toggleCatatanAdmin(); // set kondisi awal saat halaman dibuka
    }

    // 7. Validasi form konfirmasi sebelum dikirim: catatan penolakan
    //    wajib diisi jika status yang dipilih adalah "Ditolak".
    var formKonfirmasi = document.getElementById('form-konfirmasi');
    if (formKonfirmasi) {
        formKonfirmasi.addEventListener('submit', function (e) {
            var status = formKonfirmasi.status.value;
            var catatanAdmin = formKonfirmasi.catatan_admin.value.trim();
            if (status === 'ditolak' && catatanAdmin === '') {
                e.preventDefault();
                alert('Catatan penolakan wajib diisi jika status Ditolak.');
            }
        });
    }
});
