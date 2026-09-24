@extends('layouts.exam.app')
@section('title', 'Exam')
@push('style')
<style>
.normal-text {
    font-size: 24px;
}

.blink {
    color: red;
    animation: blink-animation 1s steps(5, start) infinite;
}

@keyframes blink-animation {
    to {
        visibility: hidden;
    }
}
.pagination {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.pagination li {
    list-style: none;
    margin: 5px; /* Jarak antar kotak */
}

.pagination a, .pagination span {
    display: inline-block;
    padding: 10px 15px;
    background-color: #e67c35fe; /* Warna latar belakang */
    color: white; /* Warna teks */
    border-radius: 5px; /* Sudut kotak */
    text-decoration: none;
}

.pagination .page-item .page-link {
    width: 60px; /* Set a fixed width */
    text-align: center; /* Center the text */
}
.pagination a:hover {
    background-color: #0056b3; /* Warna latar belakang saat hover */
}

.pagination .active span {
    background-color: #0056b3; /* Warna latar belakang untuk kotak aktif */
    color: white;
}

#loading {
    display: none; /* Hidden by default */
    position: fixed; /* Fixed position */
    top: 0; /* Cover the top */
    left: 0; /* Cover the left */
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    background-color: rgba(0, 0, 0, 0.5); /* Black background with transparency */
    z-index: 9999; /* High z-index to cover everything */
    color: white; /* Text color */
    text-align: center; /* Center text */
    padding-top: 20%; /* Center the loading text vertically */
}

.loading-content {
    position: relative;
    top: 50%;
    transform: translateY(-50%); /* Center vertically */
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
        @include('exam.room.pagination_data')
    </div>

    <div class="row" id='akhiri-ujian'>
        @include('exam.room.akhiri-ujian')
    </div>


@endsection
@push('modal')
    <!-- Modal -->
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
<script>

{{-- async function joinMeeting() {
    try {
        const meeting = new Metered.Meeting();
        const meetingInfo = await meeting.join({
            roomURL: "{{ $participantUrl }}",
            name: "{{ $participantName }}",
        });
            try {
                await meeting.startVideo();
            } catch (error) {
                console.log("Error starting video:", error);
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                document.getElementById("pesertaVideo").srcObject = stream;
                document.getElementById("pesertaVideo").play();
            } catch (error) {
                console.log("Error accessing local video stream manually:", error);
            }
    } catch (error) {
        console.error("Error joining meeting:", error);
        alert("Failed to join the meeting. Please try again.");
    }
}

joinMeeting(); --}}


$(document).ready(function() {
    // Hide certain elements on page load
    $("#text").hide();
    $("#start").hide();

    // Encrypt Ujian and Peserta IDs
    var ujian_id = '{{ encrypt($ujian->id_ujian) }}';
    var peserta_id = '{{ encrypt($peserta->id_peserta_ujian) }}';

    // Get finish time and total duration
    const waktu_selesai = new Date('{{ $waktu_selesai }}').getTime(); // Already formatted using Blade
    const totalDuration = {{ $total_duration_in_seconds }} * 1000; // Convert to milliseconds
    const tujuhLimaPersen = totalDuration * 0.75; // 75% of the total duration

    const totalSoal = {{ $totalSoal }};
    const totalJawaban = {{ $totalJawaban }};
    if (totalSoal == totalJawaban) {
            $('#akhiriUjian').show();
        }
    // Timer countdown function
    const hitung_durasi = setInterval(function() {
        const sekarang = new Date().getTime();
        const durasi = waktu_selesai - sekarang;

        // Calculate hours, minutes, and seconds remaining
        const jam = Math.floor(durasi / (1000 * 60 * 60));
        const menit = Math.floor((durasi % (1000 * 60 * 60)) / (1000 * 60));
        const detik = Math.floor((durasi % (1000 * 60)) / 1000);

        const teks_durasi = document.getElementById('teks_durasi');
        teks_durasi.innerHTML = `Durasi: ${jam} jam ${menit} menit ${detik} detik lagi`;

        const elapsedTime = totalDuration - durasi;


        // Show "End Exam" button when 75% of the time has passed
        if (elapsedTime >= tujuhLimaPersen) {
            $('#akhiriUjian').show();
        }

        // When the time is up, stop the timer and show "Exam Ended" message
        if (durasi < 0) {
            clearInterval(hitung_durasi);
            teks_durasi.innerHTML = 'Ujian telah berakhir';
            teks_durasi.style.color = 'red';

            // Show modal for exam end and redirect after 5 seconds
            $('#modalUjianBerakhir').modal('show');
            setTimeout(function() {
                window.location.href = '{{ route("peserta.finishUjianTimeOut", ["id_ujian" => "__id_ujian__", "id_peserta_ujian" => "__id_peserta_ujian__"]) }}'
                    .replace('__id_ujian__', ujian_id)
                    .replace('__id_peserta_ujian__', peserta_id);
            }, 5000); // Redirect after 5 seconds
        }
        // Display warning if time left is 5 minutes or less
        else if (durasi <= 5 * 60 * 1000) {
            teks_durasi.innerHTML += ' 🔥'; // Add fire emoji
            teks_durasi.style.color = 'red'; // Change text color to red
        } else {
            teks_durasi.style.color = 'blue'; // Default text color is blue
        }
    }, 1000); // Run every second,,

    // JS untuk handle button pagination
    $(document).on('click', '.prev-page, .next-page, .pagination a', function(event) {
        event.preventDefault();
        var page = $(this).attr('href').split('page=')[1];
        fetch_data(page);
    });

    // Fetch data yang dipagination
    function fetch_data(page) {
        var peserta_ujian_id = $("#peserta_ujian_id").val();
        $('#loading-indicator').show(); // tampilkan loading
        $.ajax({
            url: "/pagination/fetch_data?page=" + page,
            type: "GET",
            data: {
                peserta_ujian_id: peserta_ujian_id
            },
            success: function(soal_satuan) {
                $('#loading-indicator').hide(); // sembunyikan loadig
                $('#table_data').html(soal_satuan);
            },
            error: function(xhr, status, error) {
                $('#loading-indicator').hide();
                let errorMessage = "An error occurred while fetching data. ";
                errorMessage += "Status: " + status + ". ";

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += "Message: " + xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    errorMessage += "Response: " + xhr.responseText;
                } else {
                    errorMessage += "Error: " + error;
                }

                // munculkan pesan error
                alert(errorMessage);
            }
        });
    }
});

    // --------------------------------------------------------------------------
    //Pengaturan JS untuk akses kamera user
    // var video = document.querySelector("#video-webcam");
    // navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;
    // if (navigator.getUserMedia) {
    //     navigator.getUserMedia({ video: true }, handleVideo, videoError);
    // }
    // function handleVideo(stream) {
    //     video.srcObject = stream;
    // }
    // function videoError(e) {
    //     alert("Izinkan menggunakan webcam untuk demo!");
    // }


</script>


@endpush
