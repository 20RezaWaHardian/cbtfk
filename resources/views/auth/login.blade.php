<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | CBT FK</title>
    <meta name="description" content="Halaman login CBT FK untuk akses peserta, pengawas, dosen, dan pengelola ujian.">

    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
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

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            color: var(--ink);
            background: #f6fcff;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-font { font-family: 'Exo 2', sans-serif; }

        .login-shell {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
            overflow: hidden;
            background:
                radial-gradient(circle at 8% 18%, rgba(255,255,255,.85), transparent 18rem),
                radial-gradient(circle at 88% 12%, rgba(34,211,238,.34), transparent 24rem),
                radial-gradient(circle at 44% 92%, rgba(255,255,255,.42), transparent 20rem),
                linear-gradient(135deg, #effcff 0%, #b9efff 36%, #38bdf8 74%, #0ea5e9 100%);
        }

        .login-shell::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: .18;
            background-image:
                linear-gradient(rgba(255,255,255,.78) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.78) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: linear-gradient(to bottom, #000 0%, transparent 82%);
            pointer-events: none;
        }

        .floating-orb {
            position: absolute;
            border-radius: 999px;
            background: rgba(255,255,255,.32);
            box-shadow: inset 0 0 30px rgba(255,255,255,.32);
            pointer-events: none;
        }

        .orb-one { width: 140px; height: 140px; left: 6%; bottom: 12%; }
        .orb-two { width: 90px; height: 90px; right: 10%; top: 18%; }

        .login-content { position: relative; z-index: 1; width: 100%; }

        .brand-panel {
            height: 100%;
            padding: clamp(1.6rem, 4vw, 3rem);
            border: 1px solid rgba(255,255,255,.5);
            border-radius: 36px;
            background: rgba(255,255,255,.2);
            box-shadow: 0 28px 70px rgba(7,89,133,.16);
            backdrop-filter: blur(16px);
        }

        .logo-wrap {
            width: 92px;
            height: 92px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .35rem;
            border-radius: 26px;
            background: #fff;
            box-shadow: 0 18px 42px rgba(2,132,199,.2);
        }

        .logo-wrap img { width: 100%; height: 100%; object-fit: contain; }

        .brand-title {
            max-width: 600px;
            margin: 1.35rem 0 0;
            color: #083344;
            background: linear-gradient(135deg, #083344 0%, #075985 55%, #0e7490 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: clamp(2.4rem, 5vw, 4.6rem);
            font-weight: 800;
            line-height: .98;
            letter-spacing: -.045em;
            filter: drop-shadow(0 2px 0 rgba(255,255,255,.48));
        }

        .brand-copy {
            max-width: 580px;
            margin-top: 1.25rem;
            padding: 1rem 1.15rem;
            border: 1px solid rgba(255,255,255,.55);
            border-left: 5px solid var(--primary);
            border-radius: 22px;
            color: #123047;
            background: rgba(255,255,255,.62);
            box-shadow: 0 18px 40px rgba(7,89,133,.1);
            font-weight: 600;
            line-height: 1.8;
        }

        .quick-list {
            display: grid;
            gap: .85rem;
            margin: 1.35rem 0 0;
            padding: 0;
            list-style: none;
        }

        .quick-list li {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            color: #164e63;
            font-weight: 700;
        }

        .quick-list i {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 34px;
            border-radius: 12px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            box-shadow: 0 12px 26px rgba(2,132,199,.18);
        }

        .login-card {
            border: 1px solid rgba(255,255,255,.62);
            border-radius: 34px;
            background: rgba(255,255,255,.86);
            box-shadow: 0 30px 80px rgba(7,89,133,.2);
            overflow: hidden;
            backdrop-filter: blur(18px);
        }

        .login-card-header {
            padding: 1.5rem 1.5rem 1rem;
            text-align: center;
        }

        .login-mini-logo {
            width: 76px;
            height: 76px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: .28rem;
            border-radius: 23px;
            background: #fff;
            box-shadow: 0 16px 34px rgba(2,132,199,.18);
        }

        .login-mini-logo img { width: 100%; height: 100%; object-fit: contain; }

        .login-card-body { padding: 0 1.5rem 1.5rem; }

        .form-label { color: #25465b; font-weight: 800; }
        .input-group-modern { position: relative; }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 3;
        }

        .form-control-modern {
            min-height: 54px;
            padding-left: 2.85rem;
            border: 1px solid #dbeafe;
            border-radius: 18px;
            color: var(--ink);
            background: #f8fdff;
            font-weight: 700;
            box-shadow: none;
        }

        .form-control-modern:focus {
            border-color: #7dd3fc;
            background: #fff;
            box-shadow: 0 0 0 .22rem rgba(14,165,233,.16);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            color: #64748b;
            background: transparent;
            z-index: 4;
            padding: .4rem;
        }

        .btn-submit-login {
            width: 100%;
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            border: 0;
            border-radius: 18px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            box-shadow: 0 18px 36px rgba(2,132,199,.26);
            font-weight: 900;
            transition: .2s ease;
        }

        .btn-submit-login:hover { transform: translateY(-2px); box-shadow: 0 22px 42px rgba(2,132,199,.34); }

        .back-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            color: var(--primary-dark);
            font-weight: 800;
            text-decoration: none;
        }

        .back-home:hover { color: var(--primary); }

        .alert-modern {
            border: 0;
            border-radius: 18px;
            font-weight: 700;
        }

        @media (max-width: 991.98px) {
            .login-shell { padding: 1rem 0; }
            .brand-panel { margin-bottom: 1rem; }
            .brand-title { font-size: clamp(2.1rem, 8vw, 3.4rem); }
        }

        @media (max-width: 575.98px) {
            .brand-panel { padding: 1.25rem; border-radius: 28px; }
            .login-card { border-radius: 28px; }
            .logo-wrap { width: 76px; height: 76px; }
            .quick-list li { font-size: .9rem; }
        }
    </style>
</head>

<body>
    <main class="login-shell">
        <span class="floating-orb orb-one"></span>
        <span class="floating-orb orb-two"></span>

        <div class="login-content">
            <div class="container">
                <div class="row align-items-center justify-content-center g-4">
                    <div class="col-lg-7">
                        <section class="brand-panel">
                            <a href="{{ url('/') }}" class="logo-wrap" aria-label="Kembali ke beranda CBT FK">
                                <img src="{{ asset('assets/images/logos/logouin.png') }}" alt="Logo UIN">
                            </a>
                            <h1 class="brand-title brand-font">Masuk ke sistem ujian digital CBT FK.</h1>
                            <p class="brand-copy mb-0">
                                Akses dashboard sesuai peran Anda untuk mengikuti ujian, memantau peserta, mengelola soal, jadwal, dan hasil evaluasi akademik secara lebih tertib.
                            </p>
                            <ul class="quick-list">
                                <li><i class="ti ti-shield-check"></i><span>Login aman dengan validasi akun dan sesi pengguna.</span></li>
                                <li><i class="ti ti-clock-hour-4"></i><span>Informasi ujian, waktu pengerjaan, dan status akses lebih jelas.</span></li>
                                <li><i class="ti ti-chart-bar"></i><span>Proses ujian mendukung monitoring dan rekap hasil evaluasi.</span></li>
                            </ul>
                        </section>
                    </div>

                    <div class="col-md-9 col-lg-5 col-xl-4">
                        <section class="login-card">
                            <div class="login-card-header">
                                <a href="{{ url('/') }}" class="login-mini-logo mb-3" aria-label="CBT FK">
                                    <img src="{{ asset('assets/images/logos/logouin.png') }}" alt="Logo UIN">
                                </a>
                                <h3 class="brand-font fw-bold mb-1">Selamat Datang</h3>
                                <p class="text-muted mb-0">Silakan masukkan username dan password Anda.</p>
                            </div>

                            <div class="login-card-body">
                                @if (Session::has('error'))
                                    <div class="alert alert-danger alert-modern" role="alert">
                                        <i class="ti ti-alert-circle me-1"></i>{{ session('error') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="alert alert-danger alert-modern" role="alert">
                                        <div class="fw-bold mb-1"><i class="ti ti-alert-circle me-1"></i>Periksa input Anda</div>
                                        <ul class="mb-0 ps-3">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('loginPost') }}" class="needs-validation" novalidate>
                                    @csrf
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <div class="input-group-modern">
                                            <i class="ti ti-user input-icon"></i>
                                            <input type="text" class="form-control form-control-modern" id="username" name="username" value="{{ old('username') }}" placeholder="Masukkan username" autocomplete="username" required autofocus>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group-modern">
                                            <i class="ti ti-lock input-icon"></i>
                                            <input type="password" class="form-control form-control-modern pe-5" name="password" id="password" placeholder="Masukkan password" autocomplete="current-password" required>
                                            <button class="toggle-password" type="button" id="togglePassword" aria-label="Tampilkan password">
                                                <i class="ti ti-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-submit-login">
                                        <i class="ti ti-login-2"></i>
                                        Masuk ke CBT FK
                                    </button>
                                </form>

                                <div class="text-center mt-4">
                                    <a href="{{ url('/') }}" class="back-home">
                                        <i class="ti ti-arrow-left"></i>
                                        Kembali ke Beranda
                                    </a>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var togglePassword = document.getElementById('togglePassword');
            var passwordInput = document.getElementById('password');

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    var icon = this.querySelector('i');
                    var isPassword = passwordInput.getAttribute('type') === 'password';

                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    this.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');

                    if (icon) {
                        icon.className = isPassword ? 'ti ti-eye-off' : 'ti ti-eye';
                    }
                });
            }
        });
    </script>
</body>

</html>