@extends('layouts.exam.app')
@section('title', 'Exam')
@push('style')
<style>
.modal-content {
  pointer-events: none; /* Nonaktifkan semua interaksi */
}

.modal-content .fullscreen-btn {
  pointer-events: auto; /* Hanya tombol fullscreen yang bisa diklik */
}

.normal-text {
    font-size: 24px;
}

.blink {
    color: red;
    animation: blink-animation 1s steps(5, start) infinite;
}

#prev, #next {
    z-index: 9999;  /* Menempatkan tombol di depan elemen lainnya */
}
@keyframes blink-animation {
    to {
        visibility: hidden;
    }
}

.unanswerd-soal {
        background-color: red !important; /* Merah untuk soal yang belum dijawab */
        color: white;
    }

    .answered-soal {
        background-color: green !important; /* Hijau untuk soal yang sudah dijawab */
        color: white;
    }

    .ragu {
        background-color: rgb(248, 171, 17) !important; /* Hijau untuk soal yang sudah dijawab */
        color: white;
    }
    

    .active-soal {
        background-color: blue !important; /* Biru untuk soal yang aktif */
        color: white;
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

/* Menyusun pagination agar elemen rata tengah */
.pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;  /* Menata pagination agar berada di tengah */
    margin: 20px 0;  /* Memberikan margin atas dan bawah untuk memberi ruang */
}

/* Mengatur margin antar kotak pagination */
.pagination li {
    list-style: none;
    margin: 5px;  /* Jarak antar kotak */
}

/* Style untuk link pagination dan kotak soal */
.pagination a, .pagination span {
    display: inline-block;
    padding: 10px 15px;
    background-color: #e67c35fe; /* Warna latar belakang */
    color: white; /* Warna teks */
    border-radius: 5px; /* Sudut kotak */
    text-decoration: none;
}

.pagination .page-item .page-link {
    width: 60px; /* Lebar tetap untuk link pagination */
    text-align: center; /* Rata tengah untuk teks */
}

/* Efek hover pada pagination link */
.pagination a:hover {
    background-color: #0056b3; /* Warna latar belakang saat hover */
}

/* Style untuk kotak yang sedang aktif */
.pagination .active span {
    background-color: #0056b3; /* Warna latar belakang untuk kotak aktif */
    color: white;
}

</style>

@endpush
@section('contents')

    <div class="row">
        <div class="col-sm-12 col-lg-2 d-flex justify-content-center align-items-center">
            <div class="card" style="width: 140px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); overflow: hidden;">


                    <video id="pesertaVideo" autoplay width="100%"
                    height="100%"
                    style="border-radius: 10px; object-fit: cover;"></video>


            </div>
        </div>

        <div class="col-sm-12 col-lg-6 d-flex justify-content-lg-start justify-content-center text-center text-lg-start "> <!-- Menggunakan kelas margin Bootstrap -->
            <div class="card" style="width: 100%;">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-1">{{ $ujian->nama_ujian }}</h5>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-lg-4 d-flex justify-content-lg-end justify-content-center text-center text-lg-end">
            <div class="card" style="width: 100%;">
                <div class="card-body">
                    <h6 style="color:black; font-weight:bold;" id="teks_durasi"></h6>
                    <input type="hidden" name="sisa_waktu" id="sisaWaktu" value="">
                    {{-- <div>
                        <span>Latency Anda: <span id="latency">Mengukur...</span></span>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>


    <div id="loading" style="display: none;">
        <div class="loading-content">
            <p>Loading...</p>
        </div>
    </div>


    <div class="row" id="table_data">
    </div>

    <div class="row" id='akhiri-ujian'>
        @include('exam.room.akhiri-ujian')
    </div>


@endsection
@push('modal')
    <!-- Modal -->

    <div class="modal fade" id="modalFullscreen" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFullscreenLabel">Ayo Masuk ke Mode Layar Penuh</h5>
            </div>
            <div class="modal-body">
                Untuk melanjutkan ujian, harap tekan tombol "Masuk ke Layar FullScreen" di bawah ini.
            </div>
            <div class="modal-footer">
                <button id="fullscreenBtn" class="btn btn-md btn-primary fullscreen-btn">Masuk ke Layar FullScreen</button>
            </div>
        </div>
        </div>
    </div>


    <div class="modal fade" id="modalUjianBerakhir" tabindex="-1" aria-labelledby="modalUjianBerakhirLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUjianBerakhirLabel">Sesi Ujian Telah Berakhir</h5>
                </div>

                <div class="modal-body">
                    Waktu ujian Anda telah berakhir!. Halaman akan dialihkan dalam 5 detik
                </div>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.1/echo.iife.js"></script>
<script src="https://cdn.metered.ca/sdk/frame/1.4.3/sdk-frame.min.js"></script>
<script src="https://cdn.metered.ca/sdk/video/1.4.6/sdk.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Atur header CSRF untuk semua request AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    });
</script>
<script>
    var ujian_id = '{{ encrypt($ujian->id_ujian) }}';
    var peserta_id = '{{ encrypt($peserta->id_peserta_ujian) }}';


    $(document).ready(function()
    {
        function refreshToken() {
            const refresh_token = localStorage.getItem('refresh_token'); 

            $.ajax({
                url: "{{route('refreshToken')}}",
                method: "POST",
                data: {
                    refresh_token: refresh_token,
                    _token: "{{ csrf_token() }}" // kalau route pakai web middleware
                },
                success: function(response) {
                    console.log("Token baru:", response);
                    if (response.refresh_token) {
                        localStorage.setItem('refresh_token', response.refresh_token);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Gagal refresh token:", xhr.responseText || error);
                }
            });
        }

        // Setiap 5 menit
        setInterval(refreshToken, 300000);

        setInterval(function () {
            $.ajax({
                url: '/store-sisa-waktu',
                method: 'POST',
                data:{
                    id_peserta_ujian : peserta_id,
                    sisa_waktu : document.getElementById('sisaWaktu').value
                },
                success: function (data) {
                    // Misalnya tampilkan di elemen dengan ID #hasil
                    console.log(data.status)
                },
                error: function () {
                    console.error("Gagal mengambil data.");
                }
            });
        }, 10000); // 5000 ms = 5 detik

        // untuk durasi
        $("#text").hide();
        $("#start").hide();

        const sisaWaktuDalamDetik = {{ $sisa_waktu }}; // misalnya 3600 detik
        const waktuSelesai = new Date().getTime() + (sisaWaktuDalamDetik * 1000); // ← ini yang benar!

        const totalSoal = {{ $totalSoal }};
        const totalJawaban = {{ $totalJawaban }};

        if (totalSoal == totalJawaban) {
            $('#akhiriUjian').show();
        }

        var hitung_durasi;
        var timerSudahMulai = false;

        function updateTimer() {
            const sekarang = new Date().getTime();
            const durasi = waktuSelesai - sekarang;
            const durasiDetik = Math.floor(durasi / 1000);

            document.getElementById('sisaWaktu').value = durasiDetik;

            const jam = Math.floor(durasi / (1000 * 60 * 60));
            const menit = Math.floor((durasi % (1000 * 60 * 60)) / (1000 * 60));
            const detik = Math.floor((durasi % (1000 * 60)) / 1000);

            const teks_durasi = document.getElementById('teks_durasi');

            if (durasi > 0) {
                teks_durasi.innerHTML = `Durasi: ${jam} jam ${menit} menit ${detik} detik lagi`;

                // Tampilkan tombol jika sudah lewat 75%
                const durasiTotal = sisaWaktuDalamDetik * 1000; // total durasi awal
                const elapsedTime = durasiTotal - durasi;
                const tujuhLimaPersen = durasiTotal * 0.75;

                if (elapsedTime >= tujuhLimaPersen) {
                    $('#akhiriUjian').show();
                }

                if (durasi <= 5 * 60 * 1000) {
                    teks_durasi.innerHTML += ' 🔥';
                    teks_durasi.style.color = 'red';
                } else {
                    teks_durasi.style.color = 'blue';
                }
            } else {
                clearInterval(hitung_durasi);
                teks_durasi.innerHTML = 'Ujian telah berakhir';
                teks_durasi.style.color = 'red';

                $('#modalUjianBerakhir').modal('show');
                setTimeout(function () {
                    window.location.href = '{{ route("peserta.finishUjianTimeOut", ["id_ujian" => "__id_ujian__", "id_peserta_ujian" => "__id_peserta_ujian__"]) }}'
                        .replace('__id_ujian__', ujian_id)
                        .replace('__id_peserta_ujian__', peserta_id);
                }, 15000);
            }
        }

        function startTimer() {
            if (timerSudahMulai) return;
            timerSudahMulai = true;

            updateTimer();
            hitung_durasi = setInterval(updateTimer, 5000); // ← disarankan 1 detik agar lebih smooth
        }

        {{-- const waktu_selesai = new Date('{{ $waktu_selesai }}').getTime();
        const waktuSelesaiDalamDetik = {{ $sisa_waktu }}; // misalnya: 3600
        const totalDuration = waktuSelesaiDalamDetik * 1000; // jadi milidetik
        const tujuhLimaPersen = totalDuration * 0.75;

        const waktuMulai = new Date().getTime(); // waktu sekarang saat mulai
        const waktuSelesai = waktuMulai + totalDuration; // waktu selesai dalam timestamp --}}
        {{-- const waktuSelesaiDalamDetik = {{ $sisa_waktu }}; // misalnya 3600
        const totalDuration = waktuSelesaiDalamDetik * 1000;
        const tujuhLimaPersen = totalDuration * 0.75;

        const waktuMulai = new Date().getTime();
        const waktuSelesai = waktuMulai + totalDuration;

        const totalSoal = {{ $totalSoal }};
        const totalJawaban = {{ $totalJawaban }};

        if (totalSoal == totalJawaban) {
            $('#akhiriUjian').show();
        } --}}

        {{-- const hitung_durasi = setInterval(function() {
            const sekarang = new Date().getTime();
            const durasi = waktuSelesai - sekarang;
            const durasiDetik = Math.floor(durasi / 1000);

            const valueSisaWaktu = document.getElementById('sisaWaktu').value = durasiDetik;

            const jam = Math.floor(durasi / (1000 * 60 * 60));
            const menit = Math.floor((durasi % (1000 * 60 * 60)) / (1000 * 60));
            const detik = Math.floor((durasi % (1000 * 60)) / 1000);

            const teks_durasi = document.getElementById('teks_durasi');
            teks_durasi.innerHTML = `Durasi: ${jam} jam ${menit} menit ${detik} detik lagi`;

            const elapsedTime = totalDuration - durasi;

            if (elapsedTime >= tujuhLimaPersen) {
                $('#akhiriUjian').show();
            }

            if (durasi < 0) {
                clearInterval(hitung_durasi);
                teks_durasi.innerHTML = 'Ujian telah berakhir';
                teks_durasi.style.color = 'red';

                $('#modalUjianBerakhir').modal('show');
                setTimeout(function() {
                    window.location.href = '{{ route("peserta.finishUjianTimeOut", ["id_ujian" => "__id_ujian__", "id_peserta_ujian" => "__id_peserta_ujian__"]) }}'
                        .replace('__id_ujian__', ujian_id)
                        .replace('__id_peserta_ujian__', peserta_id);
                }, 5000);
            }
            else if (durasi <= 5 * 60 * 1000) {
                teks_durasi.innerHTML += ' 🔥';
                teks_durasi.style.color = 'red';
            } else {
                teks_durasi.style.color = 'blue';
            }
        }, 1000); --}}

        //dibawah baru komen
        {{-- var hitung_durasi;
        var timerSudahMulai = false;

        function updateTimer() {
            const sekarang = new Date().getTime();
            const durasi = waktuSelesai - sekarang;
            const durasiDetik = Math.floor(durasi / 1000);

            // simpan ke hidden input
            document.getElementById('sisaWaktu').value = durasiDetik;

            const jam = Math.floor(durasi / (1000 * 60 * 60));
            const menit = Math.floor((durasi % (1000 * 60 * 60)) / (1000 * 60));
            const detik = Math.floor((durasi % (1000 * 60)) / 1000);

            const teks_durasi = document.getElementById('teks_durasi');

            if (durasi > 0) {
                teks_durasi.innerHTML = `Durasi: ${jam} jam ${menit} menit ${detik} detik lagi`;

                const elapsedTime = totalDuration - durasi;
                if (elapsedTime >= tujuhLimaPersen) {
                    $('#akhiriUjian').show();
                }

                if (durasi <= 5 * 60 * 1000) {
                    teks_durasi.innerHTML += ' 🔥';
                    teks_durasi.style.color = 'red';
                } else {
                    teks_durasi.style.color = 'blue';
                }
            } else {
                clearInterval(hitung_durasi);
                teks_durasi.innerHTML = 'Ujian telah berakhir';
                teks_durasi.style.color = 'red';

                $('#modalUjianBerakhir').modal('show');
                setTimeout(function() {
                    window.location.href = '{{ route("peserta.finishUjianTimeOut", ["id_ujian" => "__id_ujian__", "id_peserta_ujian" => "__id_peserta_ujian__"]) }}'
                        .replace('__id_ujian__', ujian_id)
                        .replace('__id_peserta_ujian__', peserta_id);
                }, 15000);
            }
        }

        function startTimer() {
            if (timerSudahMulai) return; // biar nggak dobel
            timerSudahMulai = true;

            updateTimer(); // tampilkan sekali langsung
            hitung_durasi = setInterval(updateTimer, 12000);
        } 
        //batas durasi


        {{-- var firstSoalId = {{ $paket_has_soal->soal_id }}; --}}
        var firstSoalId = {{ $paket_has_soal->id_soal }};
        var paketHasSoal = {{ $paket_has_soal->paket_soal_id }};

        load_data(firstSoalId);

        //Baru
        let warningShown = false; // status peringatan pertama

        // Tombol untuk masuk fullscreen
        document.getElementById('fullscreenBtn').addEventListener('click', function () {
            enableFullscreen();
            $('#modalFullscreen').modal('hide');
            $('#fullscreenButton').show();
        });

        function enableFullscreen() {
            let docElement = document.documentElement;
            if (docElement.requestFullscreen) {
                docElement.requestFullscreen();
            } else if (docElement.mozRequestFullScreen) { // Firefox
                docElement.mozRequestFullScreen();
            } else if (docElement.webkitRequestFullscreen) { // Chrome, Safari, Opera
                docElement.webkitRequestFullscreen();
            } else if (docElement.msRequestFullscreen) { // IE/Edge
                docElement.msRequestFullscreen();
            }
        }

        // ✅ Deteksi keluar fullscreen
        document.addEventListener("fullscreenchange", function() {
            if(document.fullscreenElement){
                startTimer();
            }else{
                handleViolation("Anda keluar dari mode fullscreen!");
            }
        });

        // ✅ Deteksi pindah tab / minimize
        document.addEventListener("visibilitychange", function() {
            if (document.hidden) {
                handleViolation("Anda meninggalkan halaman ujian!");
            }
        });

        window.addEventListener("blur", function () {
            handleViolation("Anda beralih jendela dari ujian!");
        });

        // 🔹 Fungsi untuk handle pelanggaran
        function handleViolation(message) {
            if (!warningShown) {
                warningShown = true;
                alert(message + " Ini peringatan pertama. Jika terjadi lagi, ujian akan dihentikan.");
                enableFullscreen(); // coba paksa balik fullscreen
            } else {
                alert(message + " Ujian dihentikan.");
                // Redirect langsung ke route Laravel untuk finish ujian
                window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                    .replace('__id_ujian__', ujian_id)
                    .replace('__id_peserta_ujian__', peserta_id);
            }
        }



    });



    {{-- // Fullscreen
    document.getElementById('fullscreenBtn').addEventListener('click', function () {
        enableFullscreen();
        $('#modalFullscreen').modal('hide');
        $('#fullscreenButton').show();
    });

    function enableFullscreen() {
        var docElement = document.documentElement;
        if (docElement.requestFullscreen) {
            docElement.requestFullscreen();
        } else if (docElement.mozRequestFullScreen) { // Firefox
            docElement.mozRequestFullScreen();
        } else if (docElement.webkitRequestFullscreen) { // Chrome, Safari, Opera
            docElement.webkitRequestFullscreen();
        } else if (docElement.msRequestFullscreen) { // IE/Edge
            docElement.msRequestFullscreen();
        }
    }

   // Menambahkan event listener untuk memantau gerakan pointer
    document.addEventListener('mousemove', function(event) {
        // Hanya lakukan pemeriksaan jika fullscreen aktif
        if (document.fullscreenElement) {
            var pointerX = event.clientX;
            var pointerY = event.clientY;

            // Mendapatkan ukuran jendela atau viewport saat fullscreen
            var screenWidth = window.innerWidth;
            var screenHeight = window.innerHeight;

            // Mengecek apakah pointer berada dalam area fullscreen
            if (pointerX >= 0 && pointerX <= screenWidth && pointerY >= 0 && pointerY <= screenHeight) {
                // console.log("Pointer berada dalam area fullscreen");
            } else {

                    alert("Anda keluar dari fullscreen. Sistem akan logout sekarang.");
                    // Redirect langsung tanpa penundaan
                    window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                        .replace('__id_ujian__', ujian_id)
                        .replace('__id_peserta_ujian__', peserta_id);
                                }
                            }

    });
    document.addEventListener('mouseleave', function(event) {
        if (document.fullscreenElement) {

            alert("Anda keluar dari fullscreen. Sistem akan logout sekarang.");

            // Redirect langsung tanpa penundaan
            window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                .replace('__id_ujian__', ujian_id)
                .replace('__id_peserta_ujian__', peserta_id);
        }
    }); --}}

    {{-- //Baru
    let warningShown = false; // status peringatan pertama

    // Tombol untuk masuk fullscreen
    document.getElementById('fullscreenBtn').addEventListener('click', function () {
        enableFullscreen();
        $('#modalFullscreen').modal('hide');
        $('#fullscreenButton').show();
    });

    function enableFullscreen() {
        let docElement = document.documentElement;
        if (docElement.requestFullscreen) {
            docElement.requestFullscreen();
        } else if (docElement.mozRequestFullScreen) { // Firefox
            docElement.mozRequestFullScreen();
        } else if (docElement.webkitRequestFullscreen) { // Chrome, Safari, Opera
            docElement.webkitRequestFullscreen();
        } else if (docElement.msRequestFullscreen) { // IE/Edge
            docElement.msRequestFullscreen();
        }
    }

    // ✅ Deteksi keluar fullscreen
    document.addEventListener("fullscreenchange", function() {
        if(document.fullscreenElement){
            startTimer();
        }else{
            handleViolation("Anda keluar dari mode fullscreen!");
        }
    });

    // ✅ Deteksi pindah tab / minimize
    document.addEventListener("visibilitychange", function() {
        if (document.hidden) {
            handleViolation("Anda meninggalkan halaman ujian!");
        }
    });

    // 🔹 Fungsi untuk handle pelanggaran
    function handleViolation(message) {
        if (!warningShown) {
            warningShown = true;
            alert(message + " Ini peringatan pertama. Jika terjadi lagi, ujian akan dihentikan.");
            enableFullscreen(); // coba paksa balik fullscreen
        } else {
            alert(message + " Ujian dihentikan.");
            // Redirect langsung ke route Laravel untuk finish ujian
            window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                .replace('__id_ujian__', ujian_id)
                .replace('__id_peserta_ujian__', peserta_id);
        }
    } --}}



    
    // document.addEventListener("fullscreenchange", function () {
    //     if (!document.fullscreenElement) { // Ketika keluar dari fullscreen

    //         alert("Anda keluar dari fullscreen. Sistem akan logout sekarang.");

    //         // Redirect langsung tanpa penundaan
    //         window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
    //             .replace('__id_ujian__', ujian_id)
    //             .replace('__id_peserta_ujian__', peserta_id);
    //     }else{
    //         document.getElementById('table_data').style.display = 'inline';
    //     }
    // });

    document.addEventListener('DOMContentLoaded', function () {

        // Sembunyikan table_data di awal (jika belum disembunyikan via HTML/CSS)
        document.getElementById('table_data').style.display = 'none';

        document.addEventListener("fullscreenchange", function () {
            // alert(document.fullscreenElement);
            if (!document.fullscreenElement) {
                alert("Anda keluar dari fullscreen. Sistem akan logout sekarang.");

                // Redirect langsung tanpa penundaan
                window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                    .replace('__id_ujian__', ujian_id)
                    .replace('__id_peserta_ujian__', peserta_id);
            } else{
                document.getElementById('table_data').style.removeProperty('display');
            }
        });
    });
    
    // Mencegah reload manual dengan tombol refresh
    document.addEventListener("keydown", function (e) {
        {{-- if (e.key === "F5" || (e.ctrlKey && e.key === "r")) {
            e.preventDefault(); // Mencegah reload dengan F5 atau Ctrl+R
            alert("Reload tidak diizinkan. Sistem akan logout.");
            window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                .replace('__id_ujian__', ujian_id)
                .replace('__id_peserta_ujian__', peserta_id);
        } --}}

        if (e.ctrlKey && e.key === "Tab") {
            e.preventDefault();
            alert("Ctrl + Tab tidak diizinkan. Sistem akan logout.");
            window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                .replace('__id_ujian__', ujian_id)
                .replace('__id_peserta_ujian__', peserta_id);
        }

        // Cegah Alt+Tab (sayangnya tidak bisa full di browser modern)
        if (e.altKey && e.key === "Tab") {
            e.preventDefault();
            alert("Alt + Tab tidak diizinkan. Sistem akan logout.");
            window.location.href = '{{ route("peserta.finishUjianExitFullScreen", ["id_ujian" => "__id_ujian__", "__id_peserta_ujian__"]) }}'
                .replace('__id_ujian__', ujian_id)
                .replace('__id_peserta_ujian__', peserta_id);
        }

        // Blokir Inspect Element
        if ((e.ctrlKey && e.shiftKey && e.key === "I") || 
            (e.ctrlKey && e.key === "u") || 
            e.key === "F12") {
            e.preventDefault();
            handleViolation("Akses inspeksi tidak diizinkan selama ujian!");
        }
    });

    document.addEventListener("contextmenu", function (e) {
        e.preventDefault(); // cegah menu klik kanan muncul
        handleViolation("Klik kanan tidak diizinkan selama ujian!");
    });



    async function joinMeeting() {
        try {
            {{-- const meeting = new Metered.Meeting();
            const meetingInfo = await meeting.join({
                roomURL: "{{ $participantUrl }}",
                name: "{{ $participantName }}",
            });

                try {
                    await meeting.startVideo();
                } catch (error) {
                    console.log("Error starting video:", error);
                } --}}

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: { ideal: 320 },   // lebih kecil dari 640
                            height: { ideal: 240 },  // lebih kecil dari 360
                            frameRate: { ideal: 10, max: 10 } // lebih rendah dari 15 fps
                        }
                    });
                    {{-- const stream = await navigator.mediaDevices.getUserMedia({ video: true }); --}}
                    document.getElementById("pesertaVideo").srcObject = stream;
                    document.getElementById("pesertaVideo").play();
                    $('#modalFullscreen').modal('show');
                } catch (error) {
                    console.log("Error accessing local video stream manually:", error);
                }
        } catch (error) {
            console.error("Error joining meeting:", error);
            alert("Failed to join the meeting. Please try again.");

        }
    }

    joinMeeting();




    // Fungsi untuk memuat soal melalui AJAX berdasarkan id_soal
    function load_data(id_soal) {
        $.ajax({
            url: "/pagination/fetch_data/" + id_soal,
            type: "GET",
            xhrFields:{
                withCredentials:true
            },
            data: {
                peserta_ujian_id: {{ $peserta->id_peserta_ujian }}
            },
            beforeSend: function() {
                $('#loading-indicator').show();
            },
            success: function(soal) {
                $('#loading-indicator').hide();
                $('#table_data').html(soal);
                adjustNavigationButtons();
            },
            error: function(xhr, status, error) {
                $('#loading-indicator').hide();
                 if (errorMessage.includes("Unauthenticated")) {
                    window.location.reload(true); // reload full
                } else {
                    location.reload(); // reload biasa
                }
                {{-- let errorMessage = "An error occurred while fetching data. ";
                errorMessage += "Status: " + status + ". ";

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += "Message: " + xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMessage += "Response: " + xhr.responseText;
                } else {
                    errorMessage += "Error: " + error;
                }

                // alert(errorMessage);
                alert("Ujian Telah Selesai atau Berhenti"+errorMessage);
                setTimeout(() => {
                    window.location.href = "https://siakad-blok.unja.ac.id/home";
                }, 1000); --}}
            }
        });
    }



    var soalList = @json($pagination_soal);
    var activeSoal = {{ $active_soalId }};
    function adjustNavigationButtons() {

        let currentIndex = soalList.findIndex(soal => soal.soal_id === activeSoal);
        let prevSoalId = currentIndex > 0 ? soalList[currentIndex - 1].soal_id : null;
        let nextSoalId = currentIndex < soalList.length - 1 ? soalList[currentIndex + 1].soal_id : null;



        if (prevSoalId === null) {
            $('#prev').css('display', 'none');
        } else {
            $('#prev').css('display', '').data('soal-id', prevSoalId);
        }

        if (nextSoalId === null) {
            $('#next').css('display', 'none');
        } else {
            $('#next').css('display', '').data('soal-id', nextSoalId);
        }
    }

    // Fungsi untuk menangani klik pada nomor soal
    $(document).on('click', '.soal-box, #prev, #next', function() {
        var soalId = $(this).data('soal-id');
        if (!soalId) {
            if ($(this).attr('id') === 'prev') {
                var currentIndex = soalList.findIndex(soal => soal.soal_id === activeSoal);
                soalId = soalList[currentIndex - 1]?.soal_id;
            } else if ($(this).attr('id') === 'next') {
                var currentIndex = soalList.findIndex(soal => soal.soal_id === activeSoal);
                soalId = soalList[currentIndex + 1]?.soal_id;
            }
        }

        if (soalId) {
            activeSoal = soalId;
            setActiveSoal(soalId);
        }
    });


    function setActiveSoal(soalId) {
        load_data(soalId);
        adjustNavigationButtons();
    }

    // Pengaturan JS untuk simpan jawaban essay
    $(document).on('change', 'textarea[id^="jawaban_essay"]', function(){
        var jawaban_essay = $(this).val();
        var essay_id = $('#essay_id').val(); // Ambil nilai dari essay_id
        var soal_id = $('#soal_id').val(); // Ambil nilai dari soal_id
        var peserta_ujian_id = $('#peserta_ujian_id').val();
        var paket_soal_id = $('#paket_soal_id').val();
        $.ajax({
            url: "{{ url('jawab/soal/essay') }}",
            type: "GET",
            dataType: 'json',
            data: {
                jawaban_essay: jawaban_essay,
                soal_id: soal_id,
                essay_id: essay_id,
                peserta_ujian_id: peserta_ujian_id,
                paket_soal_id: paket_soal_id,
            },
            // beforeSend: function() {
            //     $('#loading-indicator').show();  // Tampilkan indikator loading sebelum request
            // },
            success: function(data) {
                // $('#loading-indicator').hide();
                const totalSoal = data.totalSoal;
                const totalJawaban = data.totalJawaban;
                if (totalSoal == totalJawaban ) {
                    $('#akhiriUjian').show();
                }
            },
            error: function(xhr, status, error) {
                // $('#loading-indicator').hide();
                console.error("Error:", xhr.responseText); // Menangani error
            },
        });
    });

    // Pengaturan JS untuk simpan jawaban pilgan
    $(document).on('click', 'input[type=radio][name^="pilihan_"]', function() {
        // Ambil ID soal dan pilihan yang diklik
        var soalId = $(this).attr('name').split('_')[1]; // Ambil ID soal dari nama pilihan
        var jawabPilgan = this.value; // Nilai dari pilihan radio yang dipilih
        var pilganId = $("#pilgan_id_" + soalId + "_0").val(); // Ambil ID pilgan berdasarkan soalId
        var pesertaUjianId = $("#peserta_ujian_id").val(); // Ambil ID peserta ujian
        var ujianId = $('#ujian_id').val(); // Ambil ID ujian
        var paketSoalId = $('#paket_soal_id').val(); // Ambil ID paket soal

        // Lakukan request AJAX untuk menyimpan jawaban
        $.ajax({
            url: "{{ url('jawab/soal/pilgan') }}",
            type: "GET",
            dataType: 'json',
            data: {
                jawaban_pilgan: jawabPilgan,
                id_soal: soalId, // Menggunakan soalId dari nama pilihan radio
                pilgan_id: pilganId, // ID pilgan yang sesuai
                peserta_ujian_id: pesertaUjianId,
                paket_soal_id: paketSoalId,
                ujian_id: ujianId
            },
            // beforeSend: function() {
            //     $('#loading-indicator').show();  // Tampilkan indikator loading sebelum request
            // },
            error: function(xhr, status, error) {
                // $('#loading-indicator').hide();
                console.error("Error:", xhr.responseText); // Menangani error
            },
            success: function(data) {
                // $('#loading-indicator').hide();
                // Cek apakah jawaban sudah lengkap
                const totalSoal = data.totalSoal;
                const totalJawaban = data.totalJawaban;
                if (totalSoal === totalJawaban) {
                    $('#akhiriUjian').show(); // Tampilkan tombol 'Akhiri Ujian' jika semua soal terjawab
                }
            }
        });
    });

    // $(document).on('click', '#ragu', function() {
    //     // Ambil ID soal dan pilihan yang diklik
    //     var soalId = $('.soal_id').val(); // Ambil ID soal dari nama pilihan
    //     var jawabPilgan = $('.pilihan:checked').val(); // Nilai dari pilihan radio yang dipilih
    //     var pilganId = $("#pilgan_id_" + soalId + "_0").val(); // Ambil ID pilgan berdasarkan soalId
    //     var pesertaUjianId = $("#peserta_ujian_id").val(); // Ambil ID peserta ujian
    //     var ujianId = $('#ujian_id').val(); // Ambil ID ujian
    //     var paketSoalId = $('#paket_soal_id').val(); // Ambil ID paket soal

    //     // Lakukan request AJAX untuk menyimpan jawaban
    //     $.ajax({
    //         url: "{{ url('jawab/soal/pilgan') }}",
    //         type: "GET",
    //         dataType: 'json',
    //         data: {
    //             jawaban_pilgan: jawabPilgan,
    //             id_soal: soalId, // Menggunakan soalId dari nama pilihan radio
    //             pilgan_id: pilganId, // ID pilgan yang sesuai
    //             peserta_ujian_id: pesertaUjianId,
    //             paket_soal_id: paketSoalId,
    //             ujian_id: ujianId
    //         },
    //         // beforeSend: function() {
    //         //     $('#loading-indicator').show();  // Tampilkan indikator loading sebelum request
    //         // },
    //         error: function(xhr, status, error) {
    //             // $('#loading-indicator').hide();
    //             console.error("Error:", xhr.responseText); // Menangani error
    //         },
    //         success: function(data) {
    //             // $('#loading-indicator').hide();
    //             // Cek apakah jawaban sudah lengkap
    //             const totalSoal = data.totalSoal;
    //             const totalJawaban = data.totalJawaban;
    //             if (totalSoal === totalJawaban) {
    //                 $('#akhiriUjian').show(); // Tampilkan tombol 'Akhiri Ujian' jika semua soal terjawab
    //             }
    //         }
    //     });
    // });

    function jwbragu(soalId,pesertaUjianId,value_jwb_ragu,jenis_soal)
    {
        // alert(soalId + '    ' +pesertaUjianId)
        if(jenis_soal === "pilgan")
        {
            $.ajax({
                url: "{{ url('jawab/ragu') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id_soal: soalId,
                    peserta_ujian_id: pesertaUjianId,
                    value_jwb_ragu : value_jwb_ragu,
                    _csrf:"{{csrf_token()}}"
                },
                // beforeSend: function() {
                //     $('#loading-indicator').show();  // Tampilkan indikator loading sebelum request
                // },
                error: function(xhr, status, error) {
                    // $('#loading-indicator').hide();
                    console.error("Error:", xhr.responseText); // Menangani error
                },
                success: function(data) {
                    // $('#loading-indicator').hide();
                    // Cek apakah jawaban sudah lengkap
                    load_data(soalId)
                }
            });
        }else{
            $.ajax({
                url: "{{ url('jawab/ragu/essay') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    id_soal: soalId,
                    peserta_ujian_id: pesertaUjianId,
                    value_jwb_ragu : value_jwb_ragu,
                    _csrf:"{{csrf_token()}}"
                },
                // beforeSend: function() {
                //     $('#loading-indicator').show();  // Tampilkan indikator loading sebelum request
                // },
                error: function(xhr, status, error) {
                    // $('#loading-indicator').hide();
                    console.error("Error:", xhr.responseText); // Menangani error
                },
                success: function(data) {
                    // $('#loading-indicator').hide();
                    // Cek apakah jawaban sudah lengkap
                    load_data(soalId)
                }
            });
        }
        
    }

    $(document).ready(function()
    {
        adjustNavigationButtons();
    });

    // function checkLatency() {
    //     const startTime = performance.now();
    //     fetch('/latency-peserta')
    //         .then(response => response.text())
    //         .then(() => {
    //             const latency = Math.round(performance.now() - startTime);
    //             document.getElementById('latency').innerText = `${latency} ms`;
    //             saveLatencyToServer(latency);
    //         })
    //         .catch(() => {
    //             document.getElementById('latency').innerText = `Connection Error`;
    //         });
    // }

    // function saveLatencyToServer(latency) {
    //     fetch('/save-latency-peserta', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //         },
    //         body: JSON.stringify({ latency: latency, peserta_id: peserta_id })
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         console.log(data.message);
    //     })
    //     .catch(error => {
    //         console.error('Error:', error);
    //     });
    // }

    // // Jalankan setiap beberapa detik untuk update real-time
    // setInterval(checkLatency, 5000);

</script>


@endpush
