<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'Dashboard') &mdash; CBT Fakultas Kedokteran UIN STS Jambi</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@600;700;800&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('alert-tify/css/alertify.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('alert-tify/css/themes/default.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('alert-tify/css/themes/semantic.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('alert-tify/css/themes/bootstrap.min.css') }}" />

    <style>
        :root {
            --fk-primary: #0f766e;
            --fk-primary-dark: #0b4f6c;
            --fk-primary-soft: #e6fffb;
            --fk-blue: #2563eb;
            --fk-navy: #102a43;
            --fk-muted: #64748b;
            --fk-border: rgba(15, 118, 110, .14);
            --fk-shadow: 0 20px 45px rgba(15, 42, 67, .10);
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background:
                radial-gradient(circle at 0% 0%, rgba(15, 118, 110, .14), transparent 32%),
                radial-gradient(circle at 100% 12%, rgba(37, 99, 235, .12), transparent 28%),
                #f5f9fc;
            color: #26364a;
        }

        .page-wrapper { background: transparent; }
        .body-wrapper { background: transparent; }
        .container-fluid { max-width: 1500px; padding: 100px 28px 32px; }

        .left-sidebar {
            background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(240, 253, 250, .92));
            border-right: 1px solid var(--fk-border);
            box-shadow: 18px 0 40px rgba(15, 42, 67, .08);
        }

        .sidebar-shell { min-height: 100vh; }
        .brand-logo { padding: 24px 22px 16px; }

        .app-brand { display: flex; align-items: center; gap: 12px; color: var(--fk-navy); }
        .app-brand-logo {
            width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 18px; background: linear-gradient(135deg, #ffffff, #dffbf6);
            border: 1px solid rgba(15, 118, 110, .18); box-shadow: 0 12px 24px rgba(15, 118, 110, .13);
        }
        .app-brand-logo img { width: 42px; height: 42px; object-fit: contain; }
        .app-brand-text { display: flex; flex-direction: column; line-height: 1.15; }
        .app-brand-text strong { font-family: 'Exo 2', sans-serif; font-size: 1.18rem; letter-spacing: .03em; }
        .app-brand-text small { color: var(--fk-muted); font-size: .72rem; font-weight: 600; }

        .sidebar-nav ul .nav-small-cap { margin-top: 8px; color: var(--fk-primary-dark); font-weight: 800; letter-spacing: .04em; }
        .sidebar-nav ul .sidebar-item .sidebar-link {
            margin: 4px 14px; border-radius: 16px; color: #475569; font-weight: 700; min-height: 48px;
            transition: all .2s ease;
        }
        .sidebar-nav ul .sidebar-item .sidebar-link:hover,
        .sidebar-nav ul .sidebar-item .sidebar-link.active,
        .sidebar-nav ul .sidebar-item.selected > .sidebar-link {
            background: linear-gradient(135deg, var(--fk-primary), var(--fk-blue));
            color: #ffffff; box-shadow: 0 14px 26px rgba(15, 118, 110, .22);
        }
        .menu-icon-wrap {
            width: 32px; height: 32px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;
            background: rgba(15, 118, 110, .09); margin-right: 4px;
        }
        .sidebar-link:hover .menu-icon-wrap,
        .sidebar-link.active .menu-icon-wrap,
        .selected > .sidebar-link .menu-icon-wrap { background: rgba(255, 255, 255, .20); }
        .submenu-dot { width: 8px; height: 8px; border-radius: 99px; background: #9ca3af; margin-right: 12px; }
        .sidebar-link.active .submenu-dot { background: #ffffff; }

        .app-header {
            background: rgba(255, 255, 255, .78);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(15, 118, 110, .10);
            box-shadow: 0 12px 28px rgba(15, 42, 67, .08);
        }
        .app-topbar { min-height: 76px; padding: 0 28px; }
        .nav-action {
            width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center;
            background: var(--fk-primary-soft); color: var(--fk-primary-dark) !important;
        }
        .header-greeting span { display: block; color: var(--fk-muted); font-size: .78rem; font-weight: 600; }
        .header-greeting strong { color: var(--fk-navy); font-weight: 800; }
        .system-pill {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px;
            background: #ecfdf5; color: #047857; font-weight: 800; font-size: .82rem; border: 1px solid rgba(4, 120, 87, .12);
        }
        .user-chip {
            display: inline-flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 18px;
            background: #ffffff; border: 1px solid rgba(15, 118, 110, .12); box-shadow: 0 10px 24px rgba(15, 42, 67, .08);
        }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--fk-primary), var(--fk-blue)); color: #ffffff; font-weight: 900;
        }
        .user-avatar-lg { width: 50px; height: 50px; border-radius: 18px; }
        .user-meta { flex-direction: column; line-height: 1.15; text-align: left; }
        .user-meta strong { color: var(--fk-navy); font-size: .88rem; }
        .user-meta small { color: var(--fk-muted); font-size: .72rem; text-transform: capitalize; }
        .user-dropdown { min-width: 270px; padding: 12px; border: 0; border-radius: 22px; box-shadow: var(--fk-shadow); }
        .dropdown-user-card { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 18px; background: linear-gradient(135deg, #f0fdfa, #eff6ff); margin-bottom: 8px; }
        .dropdown-user-card strong, .dropdown-user-card small { display: block; }
        .dropdown-user-card small { color: var(--fk-muted); }
        .dropdown-action { display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 14px; font-weight: 800; text-decoration: none; }
        .dropdown-action:hover { background: #f8fafc; }

        .content-hero {
            position: relative; overflow: hidden; border-radius: 28px; padding: 26px 28px; margin-bottom: 22px;
            background: linear-gradient(135deg, rgba(15, 118, 110, .95), rgba(37, 99, 235, .90)); color: #ffffff;
            box-shadow: var(--fk-shadow);
        }
        .content-hero:after {
            content: ''; position: absolute; right: -70px; top: -90px; width: 250px; height: 250px; border-radius: 50%;
            background: rgba(255, 255, 255, .14);
        }
        .content-hero h1 { position: relative; font-family: 'Exo 2', sans-serif; margin: 0; font-size: clamp(1.45rem, 2vw, 2rem); font-weight: 800; }
        .content-hero p { position: relative; margin: 8px 0 0; color: rgba(255, 255, 255, .82); font-weight: 500; }
        .breadcrumb-soft { position: relative; display: flex; align-items: center; gap: 8px; margin-top: 12px; font-size: .82rem; font-weight: 700; color: rgba(255, 255, 255, .78); }

        .card, .table-responsive, .alert { border-radius: 20px; }
        .card { border: 1px solid rgba(15, 118, 110, .10); box-shadow: 0 15px 35px rgba(15, 42, 67, .07); }
        .btn { border-radius: 12px; font-weight: 700; }
        .form-control, .form-select, .select2-container .select2-selection--single { border-radius: 12px !important; }

        #loading-indicator {
            position: fixed; inset: 0; display: none; align-items: center; justify-content: center; flex-direction: column; gap: 14px;
            background-color: rgba(248, 250, 252, .78); backdrop-filter: blur(10px); z-index: 9999;
        }
        .loader {
            width: 58px; height: 58px; border: 6px solid rgba(15, 118, 110, .12); border-top: 6px solid var(--fk-primary);
            border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #loading-indicator p { color: var(--fk-navy); font-size: 16px; font-weight: 800; margin: 0; }

        @media (max-width: 767.98px) {
            .container-fluid { padding: 94px 16px 24px; }
            .app-topbar { padding: 0 16px; }
            .content-hero { padding: 22px; border-radius: 22px; }
        }
    </style>

    @stack('style')
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <aside class="left-sidebar">
            @include('layouts.components.sidebar')
        </aside>

        <div class="body-wrapper">
            <header class="app-header">
                @include('layouts.components.header')
            </header>

            <div class="container-fluid">
                <div id="loading-indicator">
                    <div class="loader"></div>
                    <p>Memuat data...</p>
                </div>

                <section class="content-hero">
                    <h1>@yield('title', 'Dashboard CBT FK')</h1>
                    <p>@yield('subtitle', 'Kelola kegiatan ujian CBT Fakultas Kedokteran UIN STS Jambi dengan lebih mudah dan rapi.')</p>
                    <div class="breadcrumb-soft">
                        <i class="ti ti-home"></i>
                        <span>Beranda</span>
                        <i class="ti ti-chevron-right"></i>
                        <span>@yield('title', 'Dashboard')</span>
                    </div>
                </section>

                @include('components.alert')
                @yield('contents')
                @stack('modal')
            </div>
        </div>
    </div>

    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
    <script src="{{ asset('library/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('library/tooltip.js/dist/umd/tooltip.js') }}"></script>
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('alert-tify/alertify.min.js') }}"></script>
    @stack('scripts')
</body>

</html>