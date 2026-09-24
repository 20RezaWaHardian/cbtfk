<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title') &mdash; CBT Fakultas Kedokteran UIN STS Jambi</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/logos/favicon.png') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.min.css') }}" />

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css"
        integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">



    <!-- JavaScript -->
    <script src="{{ asset('alert-tify/alertify.min.js') }}"></script>
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('alert-tify/css/alertify.min.css') }}" />
    <!-- Default theme -->
    <link rel="stylesheet" href="{{ asset('alert-tify/css/themes/default.min.css') }}" />
    <!-- Semantic UI theme -->
    <link rel="stylesheet" href="{{ asset('alert-tify/css/themes/semantic.min.css') }}" />
    <!-- Bootstrap theme -->
    <link rel="stylesheet" href="{{ asset('alert-tify/css/themes/bootstrap.min.css') }}" />
    <!-- JavaScript Alerttyify-->
    <script src="{{ asset('alert-tify/alertify.min.js') }}"></script>

    <style>
        .exam-layout { background-color: #f5f8f7; }
        .exam-layout .exam-header {
            background-color: #fff;
            border-bottom: 1px solid #dce9e3;
            box-shadow: 0 3px 16px rgba(24, 69, 51, 0.05);
        }
        .exam-header .exam-navbar { min-height: 70px; gap: 16px; justify-content: space-between; }
        .exam-header .exam-brand { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
        .exam-header .exam-logo { width: 100px; height: 50px; object-fit: contain; }
        .exam-header .exam-brand-label {
            color: #216747;
            background-color: #edf6f0;
            border: 1px solid #dce9e3;
            border-radius: 8px;
            padding: 6px 10px;
            font-weight: 600;
            white-space: nowrap;
        }
        .exam-header .exam-participant {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            min-width: 0;
        }
        .exam-header .exam-participant-info { min-width: 0; text-align: right; line-height: 1.5; }
        .exam-header .exam-participant-name,
        .exam-header .exam-participant-username {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 320px;
        }
        .exam-header .exam-participant-name { color: #263d32; font-weight: 600; }
        .exam-header .exam-participant-username { color: #64766d; }
        .exam-header .exam-avatar { flex-shrink: 0; border-radius: 50%; border: 2px solid #dce9e3; object-fit: cover; }
        @media (max-width: 575.98px) {
            .exam-layout .exam-header { padding-right: 12px; padding-left: 12px; }
            .exam-header .exam-navbar, .exam-header .exam-participant { gap: 8px; }
            .exam-header .exam-logo { width: 80px; height: 40px; }
            .exam-header .exam-brand-label { display: none; }
            .exam-header .exam-participant-name,
            .exam-header .exam-participant-username { max-width: 100%; }
        }

        @media (max-width: 768px) {

            /* Sesuaikan dengan breakpoint handphone */
            .nav-item-cbt small {
                display: none;
                /* Sembunyikan tulisan kecil */
            }
        }

        #loading-indicator {
            position: fixed;
            /* or absolute if it's within a specific container */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            /* Optional: for overlay effect */
            z-index: 9999;
            /* Ensure it’s above other elements */
        }

        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #loading-indicator p {
            color: black;
            font-size: 18px;
            text-align: center;
        }
    </style>
    @stack('style')
</head>

<body class="exam-layout">

    <!--  Main wrapper -->
    <div class="body-wrapper">
        <!--  Header Start -->
        <header class="app-header exam-header">
            @include('layouts.exam.components.header')
        </header>
        <!--  Header End -->
        <div class="container">
            <div id="loading-indicator" style="display: none;">
                <div class="loader"></div><br />
                <p> Loading . . . </p>
            </div>
            @yield('contents')
            @stack('modal')

        </div>
    </div>


    {{-- <script src="{{ asset('assets/libs/jquery/dist/jquery.min.js') }}"></script> --}}
    <script src="{{ asset('library/jquery/dist/jquery.min.js') }}"></script>
    {{-- <script src="{{ asset('library/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script> --}}
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('library/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script> --}}
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/dashboard.js') }}"></script> --}}

    <!-- JS Tambahan -->

    <script src="{{ asset('library/popper.js/dist/umd/popper.js') }}"></script>
    <script src="{{ asset('library/tooltip.js/dist/umd/tooltip.js') }}"></script>
    {{-- <script src="{{ asset('library/bootstrap/dist/js/bootstrap.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('library/jquery.nicescroll/dist/jquery.nicescroll.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('library/moment/min/moment.min.js') }}"></script> --}}

    <!-- JS Libraies -->
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('scripts')
</body>

</html>
