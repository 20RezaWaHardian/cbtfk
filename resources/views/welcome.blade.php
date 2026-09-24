<!DOCTYPE html>
<html lang="id" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>CBT FK | Computer Based Test</title>
    <meta name="description" content="Platform Computer Based Test Fakultas Kedokteran untuk ujian digital, pengelolaan soal, monitoring peserta, dan analisis hasil evaluasi akademik.">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('landing/assets/img/favicons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('landing/assets/img/favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('landing/assets/img/favicons/favicon-16x16.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('landing/assets/img/favicons/favicon.ico') }}">
    <meta name="theme-color" content="#0ea5e9">

    <link href="{{ asset('landing/assets/css/theme.css') }}" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@500;600;700;800&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #075985;
            --cyan: #22d3ee;
            --ink: #113449;
            --muted: #64748b;
            --line: rgba(14, 116, 144, .14);
        }

        * { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            color: var(--ink);
            background: #f6fcff;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-font { font-family: 'Exo 2', sans-serif; }

        .page-shell {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            background:
                radial-gradient(circle at 9% 17%, rgba(255,255,255,.78), transparent 18rem),
                radial-gradient(circle at 82% 13%, rgba(34,211,238,.38), transparent 24rem),
                linear-gradient(135deg, #effcff 0%, #b9efff 34%, #38bdf8 72%, #0ea5e9 100%);
        }

        .page-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: .18;
            background-image:
                linear-gradient(rgba(255,255,255,.72) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.72) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: linear-gradient(to bottom, #000 0%, transparent 80%);
            pointer-events: none;
        }

        .navbar-landing {
            position: fixed;
            top: 18px;
            left: 50%;
            transform: translateX(-50%);
            width: min(1120px, calc(100% - 30px));
            z-index: 20;
            padding: .85rem 1rem;
            border: 1px solid rgba(255,255,255,.55);
            border-radius: 999px;
            background: rgba(255,255,255,.78);
            box-shadow: 0 20px 50px rgba(14,116,144,.18);
            backdrop-filter: blur(16px);
        }

        .brand-badge {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            padding: .25rem;
            background: #fff;
            box-shadow: 0 12px 28px rgba(2,132,199,.2);
        }

        .brand-logo {
            width: 800px;
            height: 80px;
            object-fit: contain;
        }

        .nav-link-custom {
            color: #31566c;
            font-size: .9rem;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
        }

        .nav-link-custom:hover { color: var(--primary); }

        .btn-login, .btn-white, .btn-outline-white {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            transition: .2s ease;
        }

        .btn-login {
            padding: .8rem 1.25rem;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 16px 32px rgba(2,132,199,.28);
        }

        .btn-login:hover, .btn-white:hover, .btn-outline-white:hover { transform: translateY(-2px); }
        .btn-login:hover { color: #fff; box-shadow: 0 20px 38px rgba(2,132,199,.36); }

        .hero { position: relative; z-index: 1; padding: 9rem 0 5rem; color: #fff; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            padding: .55rem .85rem;
            border: 1px solid rgba(255,255,255,.45);
            border-radius: 999px;
            color: #075985;
            font-size: .82rem;
            font-weight: 800;
            background: rgba(255,255,255,.72);
            backdrop-filter: blur(10px);
        }

        .hero-title {
            max-width: 760px;
            margin-top: 1.25rem;
            color: #083344;
            background: linear-gradient(135deg, #083344 0%, #075985 54%, #0e7490 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: clamp(2.6rem, 6vw, 5.75rem);
            font-weight: 800;
            line-height: .96;
            letter-spacing: -.05em;
            filter: drop-shadow(0 2px 0 rgba(255,255,255,.48));
        }

        .hero-copy {
            max-width: 670px;
            margin: 1.25rem 0 0;
            padding: 1rem 1.15rem;
            border: 1px solid rgba(255,255,255,.55);
            border-left: 5px solid var(--primary);
            border-radius: 22px;
            color: #123047;
            background: rgba(255,255,255,.62);
            box-shadow: 0 18px 40px rgba(7,89,133,.12);
            backdrop-filter: blur(12px);
            font-size: 1.05rem;
            font-weight: 600;
            line-height: 1.85;
        }

        .hero-actions { display: flex; flex-wrap: wrap; gap: .9rem; margin-top: 2rem; }
        .btn-white, .btn-outline-white { padding: 1rem 1.35rem; }
        .btn-white { color: var(--primary-dark); background: #fff; box-shadow: 0 18px 34px rgba(7,89,133,.22); }
        .btn-white:hover { color: var(--primary-dark); }
        .btn-outline-white { color: #fff; border: 1px solid rgba(255,255,255,.6); background: rgba(255,255,255,.12); backdrop-filter: blur(12px); }
        .btn-outline-white:hover { color: #fff; background: rgba(255,255,255,.2); }

        .hero-card {
            position: relative;
            padding: 1.3rem;
            border: 1px solid rgba(255,255,255,.55);
            border-radius: 34px;
            background: rgba(255,255,255,.28);
            box-shadow: 0 28px 70px rgba(7,89,133,.22);
            backdrop-filter: blur(18px);
        }

        .exam-preview { overflow: hidden; border-radius: 26px; background: #fff; color: var(--ink); }
        .preview-top { padding: 1.1rem; color: #fff; background: linear-gradient(135deg, var(--primary-dark), var(--primary)); }
        .timer-pill { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .7rem; border-radius: 999px; background: rgba(255,255,255,.16); font-weight: 800; }
        .question-box { padding: 1.15rem; }

        .answer-option {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .85rem;
            margin-top: .7rem;
            border: 1px solid #dbeafe;
            border-radius: 16px;
            background: #f8fdff;
        }

        .answer-dot { width: 20px; height: 20px; border: 5px solid #bae6fd; border-radius: 999px; }
        .answer-option.active { border-color: #7dd3fc; background: #e0f7ff; }
        .answer-option.active .answer-dot { border-color: var(--primary); }

        .floating-note {
            position: absolute;
            right: -18px;
            bottom: 34px;
            width: 210px;
            padding: 1rem;
            border-radius: 22px;
            color: var(--ink);
            background: rgba(255,255,255,.92);
            box-shadow: 0 18px 44px rgba(7,89,133,.2);
        }

        .stat-strip { transform: translateY(50%); position: relative; z-index: 3; }
        .stat-card { height: 100%; padding: 1.25rem; border: 1px solid var(--line); border-radius: 24px; background: rgba(255,255,255,.92); box-shadow: 0 18px 40px rgba(14,116,144,.12); }
        .stat-number { color: var(--primary-dark); font-family: 'Exo 2', sans-serif; font-size: 2rem; font-weight: 800; }
        .section { padding: 6rem 0; }
        .section-after-stats { padding-top: 8rem; }
        .section-label { color: var(--primary); font-size: .78rem; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
        .section-title { color: var(--ink); font-size: clamp(2rem, 4vw, 3.25rem); font-weight: 800; letter-spacing: -.035em; }
        .section-copy { color: var(--muted); line-height: 1.8; }

        .feature-card, .step-card, .info-panel {
            height: 100%;
            padding: 1.5rem;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: rgba(255,255,255,.86);
            box-shadow: 0 18px 44px rgba(14,116,144,.08);
            transition: .22s ease;
        }

        .feature-card:hover, .step-card:hover { transform: translateY(-5px); box-shadow: 0 24px 54px rgba(14,116,144,.14); }
        .feature-icon, .step-number { width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; border-radius: 18px; color: #fff; background: linear-gradient(135deg, var(--primary), var(--cyan)); box-shadow: 0 14px 30px rgba(2,132,199,.22); font-weight: 900; }
        .module-list { display: grid; gap: .8rem; margin: 0; padding: 0; list-style: none; }
        .module-list li { display: flex; gap: .75rem; align-items: flex-start; color: #36566a; }
        .module-list i { color: var(--primary); margin-top: .25rem; }

        .cta-panel {
            position: relative;
            overflow: hidden;
            padding: clamp(2rem, 5vw, 3.5rem);
            border-radius: 36px;
            color: #fff;
            background: radial-gradient(circle at 82% 18%, rgba(255,255,255,.22), transparent 18rem), linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 26px 70px rgba(2,132,199,.25);
        }

        .footer-landing { padding: 2rem 0; color: #557086; background: #eef9ff; border-top: 1px solid var(--line); }

        @media (max-width: 991.98px) {
            .navbar-landing { border-radius: 28px; }
            .hero { padding-top: 8rem; }
            .hero-card { margin-top: 2rem; }
            .floating-note { position: static; width: auto; margin-top: 1rem; }
            .stat-strip { transform: none; margin-top: -1rem; }
            .section-after-stats { padding-top: 5rem; }
        }

        @media (max-width: 575.98px) {
            .nav-menu { display: none !important; }
            .navbar-landing { top: 10px; width: calc(100% - 20px); }
            .hero { padding: 7rem 0 3.5rem; }
            .hero-actions a { width: 100%; justify-content: center; }
        }
    </style>
</head>

<body>
    <main>
        <section class="page-shell">
            <nav class="navbar-landing">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <a href="{{ url('/') }}" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                        <span class="brand-badge">
                            <img src="{{ asset('assets/images/logos/logouin.png') }}" alt="Logo UIN" class="brand-logo">
                        </span>
                        <span>
                            <span class="brand-font fw-bold d-block text-dark lh-1">CBT FK</span>
                            <small class="text-muted fw-semibold">Computer Based Test</small>
                        </span>
                    </a>
                    <div class="nav-menu d-flex align-items-center gap-4">
                        <a class="nav-link-custom" href="#fitur">Fitur</a>
                        <a class="nav-link-custom" href="#alur">Alur</a>
                        <a class="nav-link-custom" href="#panduan">Panduan</a>
                    </div>
                    <a href="{{ route('loginEksternal') }}" class="btn-login">
                        <i class="fas fa-right-to-bracket"></i>
                        Login
                    </a>
                </div>
            </nav>

            <div class="hero">
                <div class="container">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-7">
                            <span class="eyebrow"><i class="fas fa-shield-heart"></i> Sistem Ujian Digital Fakultas Kedokteran</span>
                            <h1 class="hero-title brand-font" style="font-color:rgb(6, 83, 227)">Ujian online lebih tertib, cepat, dan terukur.</h1>
                            <p class="hero-copy">
                                CBT FK membantu pelaksanaan evaluasi akademik mulai dari pengelolaan bank soal, penjadwalan ujian, akses peserta, monitoring proses, sampai rekap hasil ujian secara digital.
                            </p>
                            <div class="hero-actions">
                                <a href="{{ route('loginEksternal') }}" class="btn-white"><i class="fas fa-play-circle"></i> Mulai Masuk Sistem</a>
                                <a href="#panduan" class="btn-outline-white"><i class="fas fa-circle-info"></i> Lihat Panduan Singkat</a>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="hero-card">
                                <div class="exam-preview">
                                    <div class="preview-top d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="small opacity-75 fw-bold">Preview Ujian</div>
                                            <h5 class="brand-font fw-bold mb-0 text-white">Blok Kedokteran Klinis</h5>
                                        </div>
                                        <span class="timer-pill"><i class="fas fa-clock"></i> 01:24:10</span>
                                    </div>
                                    <div class="question-box">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="badge bg-info text-white rounded-pill px-3 py-2">Soal 12 / 100</span>
                                            <span class="text-muted small fw-bold">Status: Aktif</span>
                                        </div>
                                        <h6 class="fw-bold mb-2">Pilih jawaban paling tepat berdasarkan skenario klinis.</h6>
                                        <p class="text-muted small mb-2">Sistem menyimpan progres jawaban dan membantu panitia memantau pelaksanaan ujian.</p>
                                        <div class="answer-option active"><span class="answer-dot"></span><span>Jawaban tersimpan</span></div>
                                        <div class="answer-option"><span class="answer-dot"></span><span>Ragu-ragu</span></div>
                                        <div class="answer-option"><span class="answer-dot"></span><span>Belum dijawab</span></div>
                                    </div>
                                </div>
                                <div class="floating-note">
                                    <div class="fw-bold"><i class="fas fa-wifi text-info me-1"></i> Monitoring aktif</div>
                                    <small class="text-muted">Pengawas dapat melihat status peserta dan progres pengerjaan.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="stat-strip">
            <div class="container">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <div class="fw-bold">Akses berbasis web</div>
                            <small class="text-muted">Dapat dibuka dari perangkat yang memenuhi ketentuan ujian.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number">Real-time</div>
                            <div class="fw-bold">Pemantauan peserta</div>
                            <small class="text-muted">Status ujian dan aktivitas peserta lebih mudah dikendalikan.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-number">Data</div>
                            <div class="fw-bold">Rekap dan analisis</div>
                            <small class="text-muted">Hasil ujian mendukung evaluasi mutu pembelajaran.</small>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="fitur" class="section section-after-stats">
            <div class="container">
                <div class="row align-items-end mb-4 g-3">
                    <div class="col-lg-7">
                        <div class="section-label">Fitur Utama</div>
                        <h2 class="section-title brand-font mb-0">Semua kebutuhan ujian dalam satu platform.</h2>
                    </div>
                    <div class="col-lg-5">
                        <p class="section-copy mb-0">Dirancang untuk mendukung panitia, dosen, pengawas, dan mahasiswa selama proses evaluasi akademik berbasis komputer.</p>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-database"></i></div>
                            <h5 class="brand-font fw-bold">Bank Soal</h5>
                            <p class="section-copy mb-0">Pengelolaan kategori, paket, dan distribusi soal untuk berbagai kebutuhan ujian.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                            <h5 class="brand-font fw-bold">Jadwal Ujian</h5>
                            <p class="section-copy mb-0">Penjadwalan ujian, peserta, waktu mulai, batas pengerjaan, dan aturan akses.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-user-shield"></i></div>
                            <h5 class="brand-font fw-bold">Keamanan Akses</h5>
                            <p class="section-copy mb-0">Login, kontrol sesi, token, dan tahapan pra-ujian membantu menjaga ketertiban.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="feature-card">
                            <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                            <h5 class="brand-font fw-bold">Analisis Hasil</h5>
                            <p class="section-copy mb-0">Rekap nilai dan analisis butir soal membantu evaluasi pembelajaran lebih objektif.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="alur" class="section pt-0">
            <div class="container">
                <div class="text-center mb-5">
                    <div class="section-label">Alur Penggunaan</div>
                    <h2 class="section-title brand-font mb-2">Mulai ujian dalam beberapa langkah.</h2>
                    <p class="section-copy mx-auto mb-0" style="max-width: 720px;">Peserta mengikuti instruksi panitia, masuk sistem, memeriksa informasi ujian, lalu mengerjakan soal sesuai waktu yang tersedia.</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3"><div class="step-card"><div class="step-number">1</div><h5 class="brand-font fw-bold">Login Akun</h5><p class="section-copy mb-0">Masuk memakai kredensial yang diberikan atau mekanisme login yang tersedia.</p></div></div>
                    <div class="col-md-6 col-lg-3"><div class="step-card"><div class="step-number">2</div><h5 class="brand-font fw-bold">Cek Ujian</h5><p class="section-copy mb-0">Pastikan nama ujian, jadwal, durasi, dan tata tertib sudah sesuai.</p></div></div>
                    <div class="col-md-6 col-lg-3"><div class="step-card"><div class="step-number">3</div><h5 class="brand-font fw-bold">Kerjakan Soal</h5><p class="section-copy mb-0">Jawab soal dengan teliti. Perhatikan timer dan status jawaban.</p></div></div>
                    <div class="col-md-6 col-lg-3"><div class="step-card"><div class="step-number">4</div><h5 class="brand-font fw-bold">Selesai</h5><p class="section-copy mb-0">Kirim jawaban sesuai instruksi dan tunggu informasi lanjutan dari panitia.</p></div></div>
                </div>
            </div>
        </section>

        <section id="panduan" class="section pt-0">
            <div class="container">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="info-panel">
                            <div class="section-label">Panduan Peserta</div>
                            <h3 class="brand-font fw-bold mb-3">Sebelum ujian dimulai</h3>
                            <ul class="module-list">
                                <li><i class="fas fa-check-circle"></i><span>Gunakan perangkat dan jaringan internet yang stabil.</span></li>
                                <li><i class="fas fa-check-circle"></i><span>Login lebih awal sesuai arahan panitia atau pengawas.</span></li>
                                <li><i class="fas fa-check-circle"></i><span>Pastikan identitas, jadwal, dan nama ujian sudah benar.</span></li>
                                <li><i class="fas fa-check-circle"></i><span>Ikuti tata tertib ujian dan jangan menutup halaman tanpa instruksi.</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="info-panel">
                            <div class="section-label">Untuk Pengelola</div>
                            <h3 class="brand-font fw-bold mb-3">Kontrol pelaksanaan lebih mudah</h3>
                            <ul class="module-list">
                                <li><i class="fas fa-check-circle"></i><span>Kelola paket soal, peserta, jadwal, dan sesi ujian.</span></li>
                                <li><i class="fas fa-check-circle"></i><span>Pantau peserta saat ujian berlangsung melalui fitur monitoring.</span></li>
                                <li><i class="fas fa-check-circle"></i><span>Gunakan data hasil ujian untuk rekap nilai dan evaluasi akademik.</span></li>
                                <li><i class="fas fa-check-circle"></i><span>Atur akses sesuai peran pengguna pada sistem.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section pt-0">
            <div class="container">
                <div class="cta-panel">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <div class="section-label text-white opacity-75">Siap Digunakan</div>
                            <h2 class="brand-font fw-bold text-white mb-2">Masuk ke CBT FK dan lanjutkan aktivitas ujian Anda.</h2>
                            <p class="mb-0 opacity-75">Gunakan tombol login untuk mengakses dashboard sesuai peran Anda sebagai peserta, pengawas, dosen, atau pengelola sistem.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('loginEksternal') }}" class="btn-white"><i class="fas fa-right-to-bracket"></i> Login Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="footer-landing">
            <div class="container d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
                <div>
                    <strong class="brand-font">CBT FK</strong>
                    <div class="small">Computer Based Test Fakultas Kedokteran</div>
                </div>
                <div class="small">© {{ date('Y') }} CBT FK. Semua hak dilindungi.</div>
            </div>
        </footer>
    </main>

    <script src="{{ asset('landing/vendors/@popperjs/popper.min.js') }}"></script>
    <script src="{{ asset('landing/vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('landing/vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/theme.js') }}"></script>
</body>

</html>