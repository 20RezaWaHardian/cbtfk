@extends('layouts.exam.app')

@section('title', 'Ujian - Peserta Ujian')

@push('style')
    <style>
        .video-container {
            text-align: center;
        }

        #video {
            width: 100%;
            max-width: 640px;
            height: auto;
            margin-top: 20px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Adjust the gap as needed */
        }

        .list-justified {
            text-align: justify;
        }

        .list-justified li {
            display: inline-block;
            width: 100%;
            /* Adjust as needed */
            text-align: left;
            /* Left-align text inside li */
            vertical-align: top;
            /* Align content to the top */
            margin-bottom: 10px;
            /* Optional: Add margin between list items */
        }

        .list-justified::after {
            content: '';
            display: inline-block;
            width: 100%;
            /* Fills the remaining space */
        }
        @media (max-width: 576px) {
    #capturedImage {
        object-fit: contain;
        max-height: 300px; /* Sesuaikan dengan tinggi yang diinginkan */
    }
}

    </style>
@endpush

@section('contents')
    <div class="card bg-info-subtle position-relative overflow-hidden mx-auto text-center" style="max-width: 300px;">
        <div class="card-body px-4 py-3">
            <div class="row">
                <div class="col-12 align-items-center">
                    <h5 class="fw-semibold">Petunjuk Face Register</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card position-relative overflow-hidden mb-6" style="margin-top: -60px; z-index:-1">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12 mt-2">
                    <p class="fw-semibold mt-2">Berikut petunjuk untuk melakukan face register:</p>
                    <ul class="list-justified">
                        <li>
                            1. Pastikan kamera yang digunakan memiliki resolusi yang cukup untuk menghasilkan gambar yang
                            jelas dan terang.
                            Kamera harus bisa menangkap wajah dengan detail yang cukup tanpa kabur atau terlalu gelap.
                        </li>
                        <li>
                            2. Posisikan kamera pada level mata untuk mendapatkan sudut yang optimal. Hindari posisi kamera
                            yang terlalu tinggi
                            atau rendah yang bisa mengakibatkan distorsi gambar atau penampilan yang tidak sesuai.
                        </li>
                        <li>
                            3. Gunakan ruangan dengan pencahayaan yang baik. Pencahayaan yang kurang bisa menyebabkan fitur
                            wajah tidak terlihat
                            jelas, sedangkan pencahayaan yang terlalu terang dapat menyebabkan silau.
                        </li>
                        <li>
                            4. Pastikan tidak ada refleksi cahaya pada layar atau bayangan yang menutupi bagian wajah. Hal
                            ini bisa mempengaruhi
                            deteksi wajah oleh sistem.
                        </li>
                        <li>
                            5. Sebelum memulai tes, baca semua instruksi mengenai face register dengan saksama. Pastikan
                            Anda mengikuti semua
                            langkah yang dianjurkan untuk menghindari kesalahan.
                        </li>
                    </ul>


                </div>
                <div class="col-6 align-items-center text-center">
                    <img src="{{ asset('assets/images/profile/user-1.jpg') }}" width="200px"
                        class="rounded mx-auto d-block" alt="{{ asset('assets/images/profile/user-1.jpg') }}">
                    <p> Contoh salah</p>
                </div>
                <div class="col-6 align-items-center text-center">
                    <img src="{{ asset('assets/images/profile/user-1.jpg') }}" width="200px"
                        class="rounded mx-auto d-block" alt="{{ asset('assets/images/profile/user-1.jpg') }}">
                    <p> Contoh Benar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-info-subtle position-relative overflow-hidden mx-auto text-center mt-4"
        style="max-width: 300px; z-index:2">
        <div class="card-body px-4 py-3">
            <div class="row">
                <div class="col-12 align-items-center">
                    <h5 class="fw-semibold"> Face Register</h5>
                </div>
            </div>
        </div>
    </div>
    <div class="card position-relative overflow-hidden mb-6" style="margin-top: -60px; ">
        <div class="card-body px-4 py-3">
            <div class="row">
                <div class="col-12 mt-4">
                    <center>
                        <p class="fw-semibold mt-2"> Foto pose lurus dengan pencahayaan yang baik untuk kami dapat mengenali
                            Anda </p>
                    </center>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <div class="video-container">
                    <video
                        autoplay="true"
                        id="video"
                        width="100%"
                        height="100%"
                        style="border-radius: 10px; object-fit: cover;">
                    </video>
                    </div>
                    <div style="margin-top: 20px;">
                        <button type="button" class="btn btn-primary" id="ambilGambar">
                            Ambil Gambar
                        </button>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <!-- Div untuk menampilkan gambar yang di-capture -->
                    <div id="capturedImageContainer">
                        <img id="capturedImage" src="" class="mt-3"
                            style="width: 100%; height: auto; border-radius: 10px; object-fit: cover; display: none;">
                        <div class="button-container" style="margin-top: 20px;">
                            <button type="button" class="btn btn-primary" style="display: none;" id="storeGambar">Simpan</button>
                            <button type="button" class="btn btn-secondary" style="display: none;" id="ulangiGambar">Ulangi</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('modal')
@endpush

@push('scripts')
    <!-- Bootstrap JS and Custom Script -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var video = document.getElementById("video");
            var capturedImage = document.getElementById("capturedImage");
            var capturedImageContainer = document.getElementById("capturedImageContainer");
            var storeButton = document.getElementById("storeGambar");
            var ulangiButton = document.getElementById("ulangiGambar");

            {{-- navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(function(stream) {
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.log("An error occurred: " + err);
                }); --}}
            function startCamera(constraints) {
                navigator.mediaDevices.getUserMedia({ video: constraints })
                    .then(function(stream) {
                        video.srcObject = stream;
                        video.play();
                    })
                    .catch(function(err) {
                        console.warn("Constraint gagal, coba lebih rendah:", err.name);
                        if (constraints.width.ideal > 320) {
                            startCamera({ width: { ideal: 320 }, height: { ideal: 240 } });
                        }
                    });
            }

            // mulai dengan 640x480
            startCamera({ width: { ideal: 640 }, height: { ideal: 480 } });

            document.getElementById("ambilGambar").addEventListener("click", function() {
                // Capture current frame from video to canvas
                var canvas = document.createElement("canvas");
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                var ctx = canvas.getContext("2d");
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Set captured image to display
                capturedImage.src = canvas.toDataURL();
                capturedImage.style.display = "block"; // Menampilkan gambar yang di-capture

                // Menampilkan tombol Simpan
                storeButton.style.display = "block";
                // Menampilkan tombol Ulangi
                ulangiButton.style.display = "block";
            });

            // Event listener untuk tombol Ulangi
            ulangiButton.addEventListener("click", function() {
                // Menghapus gambar yang ditampilkan
                capturedImage.src = "";
                capturedImage.style.display = "none";

                // Menyembunyikan tombol Simpan
                storeButton.style.display = "none";

                // Menyembunyikan tombol Ulangi
                ulangiButton.style.display = "none";
            });

            // Event listener untuk tombol Simpan (dummy function)
            storeButton.addEventListener("click", function() {
                var image = capturedImage.src;
                var ujianId = '{{ encrypt($ujian->id_ujian) }}';
                var pesertaId = '{{ encrypt($peserta->id_peserta_ujian) }}';
                console.log(image);
                event.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Anda yakin ingin menyimpan foto ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Lanjutkan'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('peserta.storeGambar') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                image: image,
                                ujianId: ujianId,
                                pesertaId: pesertaId,
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            },
                            error: function(response) {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });

            });
        });
    </script>
@endpush
