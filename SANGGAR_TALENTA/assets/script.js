// assets/script.js
// JavaScript utama Talenta Project

document.addEventListener('DOMContentLoaded', function () {

    // ================================================================
    // HERO SLIDESHOW
    // ================================================================
    var slides    = document.querySelectorAll('.hero-slide');
    var dots      = document.querySelectorAll('.hero-dot');
    var btnPrev   = document.getElementById('heroPrev');
    var btnNext   = document.getElementById('heroNext');
    var current   = 0;
    var autoTimer = null;

    function goTo(idx) {
        slides[current].classList.remove('active');
        dots[current].classList.remove('active');
        current = (idx + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current].classList.add('active');
    }
    function startAuto() {
        autoTimer = setInterval(function () { goTo(current + 1); }, 4500);
    }
    function resetAuto() { clearInterval(autoTimer); startAuto(); }

    if (slides.length > 1) {
        startAuto();
        if (btnPrev) btnPrev.addEventListener('click', function () { goTo(current - 1); resetAuto(); });
        if (btnNext) btnNext.addEventListener('click', function () { goTo(current + 1); resetAuto(); });
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                goTo(parseInt(dot.dataset.index));
                resetAuto();
            });
        });
        // Swipe support (touch)
        var touchStartX = 0;
        var slider = document.getElementById('heroSlider');
        if (slider) {
            slider.addEventListener('touchstart', function (e) { touchStartX = e.changedTouches[0].clientX; }, { passive: true });
            slider.addEventListener('touchend', function (e) {
                var dx = e.changedTouches[0].clientX - touchStartX;
                if (Math.abs(dx) > 50) { goTo(dx < 0 ? current + 1 : current - 1); resetAuto(); }
            });
        }
    }



    // ================================================================
    // 1. Highlight menu navigasi yang sedang aktif
    // ================================================================
    var currentPage = window.location.pathname.split('/').pop().split('?')[0];
    document.querySelectorAll('nav a').forEach(function (link) {
        var href = link.getAttribute('href') || '';
        var linkPage = href.split('/').pop().split('?')[0];
        if (linkPage && linkPage === currentPage) {
            link.style.color = '#d4af37';
            link.style.borderColor = '#d4af37';
            link.style.background = 'rgba(212,175,55,0.08)';
        }
    });

    // ================================================================
    // 2. Preview gambar upload — pakai atribut data-preview="<img-id>"
    //    Berlaku untuk semua input[type=file] yang punya atribut ini.
    //    Contoh: <input type="file" data-preview="preview-profil">
    //            <img id="preview-profil" ...>
    // ================================================================
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) return;
            var targetId = input.getAttribute('data-preview');
            var img = document.getElementById(targetId);
            if (img) {
                img.src = URL.createObjectURL(input.files[0]);
            }
        });
    });

    // Preview foto identitas (tidak pakai data-preview karena pakai wrap div)
    var inputIdentitas = document.getElementById('foto_identitas');
    var previewIdWrap  = document.getElementById('preview-identitas-wrap');
    var previewIdImg   = document.getElementById('preview-identitas');
    if (inputIdentitas && previewIdWrap && previewIdImg) {
        inputIdentitas.addEventListener('change', function () {
            if (inputIdentitas.files && inputIdentitas.files[0]) {
                previewIdImg.src = URL.createObjectURL(inputIdentitas.files[0]);
                previewIdWrap.style.display = 'block';
            }
        });
    }

    // Preview gambar kostum/konten admin (input[name="gambar"] tanpa data-preview)
    var inputGambar = document.querySelector('input[name="gambar"]:not([data-preview])');
    if (inputGambar) {
        inputGambar.addEventListener('change', function () {
            if (!inputGambar.files || !inputGambar.files[0]) return;
            var existing = document.getElementById('preview-gambar');
            if (!existing) {
                existing = document.createElement('img');
                existing.id = 'preview-gambar';
                existing.style.cssText = 'max-width:140px;margin-top:10px;border-radius:6px;display:block;';
                inputGambar.insertAdjacentElement('afterend', existing);
            }
            existing.src = URL.createObjectURL(inputGambar.files[0]);
        });
    }

    // ================================================================
    // 3. Validasi form daftar akun baru
    // ================================================================
    var formDaftar = document.getElementById('form-daftar');
    if (formDaftar) {
        formDaftar.addEventListener('submit', function (e) {
            var wajib = ['nama_lengkap', 'username', 'password', 'email', 'no_hp', 'alamat', 'jenis_identitas', 'nomor_identitas'];
            for (var i = 0; i < wajib.length; i++) {
                var el = formDaftar[wajib[i]];
                if (el && el.value.trim() === '') {
                    e.preventDefault();
                    alert('Semua kolom wajib diisi, termasuk data identitas.');
                    el.focus();
                    return;
                }
            }
            // Foto identitas wajib
            var fotoId = formDaftar['foto_identitas'];
            if (fotoId && fotoId.files && fotoId.files.length === 0) {
                e.preventDefault();
                alert('Foto identitas wajib diupload.');
                fotoId.focus();
            }
        });
    }

    // ================================================================
    // 4. Validasi form login
    // ================================================================
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

    // ================================================================
    // 5. Validasi form penyewaan
    // ================================================================
    var formSewa = document.getElementById('form-sewa');
    if (formSewa) {
        formSewa.addEventListener('submit', function (e) {
            var jumlah = parseInt(formSewa.jumlah.value, 10);
            var lama   = parseInt(formSewa.lama.value, 10);
            if (jumlah < 1) { e.preventDefault(); alert('Jumlah kostum minimal 1.'); return; }
            if (lama < 1)   { e.preventDefault(); alert('Lama sewa minimal 1 hari.'); return; }
        });
    }

    // ================================================================
    // 6. Konfirmasi hapus data (data-confirm="pesan")
    // ================================================================
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // ================================================================
    // 7. Form konfirmasi penyewaan admin — toggle textarea catatan
    // ================================================================
    var selectStatus     = document.getElementById('status');
    var wrapCatatanAdmin = document.getElementById('wrap-catatan-admin');
    var inputCatatan     = document.getElementById('catatan_admin');
    if (selectStatus && wrapCatatanAdmin && inputCatatan) {
        function toggleCatatan() {
            var ditolak = selectStatus.value === 'ditolak';
            wrapCatatanAdmin.style.display = ditolak ? 'block' : 'none';
            inputCatatan.required = ditolak;
        }
        selectStatus.addEventListener('change', toggleCatatan);
        toggleCatatan();
    }

    var formKonfirmasi = document.getElementById('form-konfirmasi');
    if (formKonfirmasi) {
        formKonfirmasi.addEventListener('submit', function (e) {
            if (formKonfirmasi.status.value === 'ditolak' &&
                formKonfirmasi.catatan_admin.value.trim() === '') {
                e.preventDefault();
                alert('Catatan penolakan wajib diisi jika status Ditolak.');
            }
        });
    }

    // ================================================================
    // 8. Toggle metode pembayaran (transfer tampilkan upload bukti)
    // ================================================================
    var selectMetode = document.getElementById('metode_bayar');
    var wrapTransfer = document.getElementById('wrap-transfer');
    if (selectMetode && wrapTransfer) {
        function toggleTransfer() {
            wrapTransfer.style.display = (selectMetode.value === 'transfer') ? 'block' : 'none';
        }
        selectMetode.addEventListener('change', toggleTransfer);
        toggleTransfer();
    }

    var formBayar = document.getElementById('form-bayar');
    if (formBayar) {
        formBayar.addEventListener('submit', function (e) {
            if (formBayar.metode_bayar.value === '') {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran terlebih dahulu.');
            }
        });
    }

});
