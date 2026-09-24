@extends('layouts.app')
@section('title', 'Monitoring Ujian')
@push('style')

<style>
        .card {
            margin: 10px;
        }

        .participant-card {
            margin-bottom: 20px;
        }
        .participant-video {
            width: 100%;
            height: 300px;
            border: none;
        }
        .participant-info {
            font-weight: bold;
            text-align: center;
            margin-top: 10px;
        }

</style>

@endpush
@section('contents')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-1">{{ $ujian->nama_ujian }}</h5>
            <p class="mb-0">
                {{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->translatedFormat('l, d F Y') }}
            </p>
        </div>
    </div>


    <div class="row">
        {{-- Kiri --}}
        <div class="col-sm-12 col-xl-12">
            <div class="card bg-primary-subtle shadow-none">
                <div class="card-body">

                    <div class="d-flex align-items-center">

                        <div class="col-6">
                            <h6 class="mb-0">Monitoring Peserta</h6>
                        </div>
                        <div class="col-6">
                            <a href="{{route('ujian.monitoring.mulaiPesertAll',encrypt($ujian->id_ujian))}}" 
                                class="btn btn-sm btn-warning">
                                    Mulai Peserta Berhenti
                            </a>
                            @if (auth()->user()->can('read monitoring/ujian'))
                                <a href="{{ route('monitoring.downloadBeritaAcara', encrypt($ujian->id_ujian)) }}" class="btn btn-sm btn-primary"
                                    style="float:right">Download BA</a>
                            @endif
                            <button onclick="reloadPage()"
                                class="btn btn-sm btn-danger"
                                style="float:right; margin-right: 10px;">
                                    Muat Ulang Halaman
                        </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Monitoring -->
            <ul class="nav nav-tabs mt-2" id="monitorTabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <a class="nav-link active" id="data-participants-tab" data-bs-toggle="tab" href="#data-participants" role="tab" aria-controls="data-participants" aria-selected="true">Data Peserta</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="monitor-camera-tab" data-bs-toggle="tab" href="#monitor-camera" role="tab" aria-controls="monitor-camera" aria-selected="true">Monitor Kamera</a>
                </li>
            </ul>

            <div class="tab-content" id="monitorTabsContent">
                <!-- Data Peserta -->
                <div class="tab-pane fade show active" id="data-participants" role="tabpanel" aria-labelledby="data-participants-tab">

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>

                                            <th>NIM</th>
                                            <th>Nama Peserta</th>
                                            <th>IP Address</th>
                                            <th>Status Peserta</th>
                                            <th>Status Perangkat</th>
                                            <th>Aksi</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($peserta_ujian as $peserta)
                                            <tr>
                                                {{-- <td>
                                                    @if (isset($peserta->face_register))
                                                    <img src="{{ asset('regis_peserta_ujian/'.$peserta->face_register) }}" alt="Face Register" width="50" height="50">
                                                    @else
                                                    -
                                                    @endif
                                                </td> --}}
                                                <td>{{ $peserta->mahasiswa->nim ?? auth()->user()->username}}</td>
                                                <td>{{ $peserta->mahasiswa->nama ?? $peserta->peserta_eksternal->nama_peserta}}</td>
                                                <td>{{ $peserta->ip_address ?? '-' }}</td>
                                                <td>
                                                    @if($peserta->status_pengerjaan === 0)
                                                        <span class="badge bg-secondary">Belum Mengerjakan</span>
                                                    @elseif($peserta->status_pengerjaan === 1)
                                                        <span class="badge bg-info">Sedang Dikerjakan</span>
                                                    @elseif($peserta->status_pengerjaan === 2)
                                                        <span class="badge bg-success">Telah Dikerjakan</span>
                                                    @elseif($peserta->status_pengerjaan === 3)
                                                        <span class="badge bg-danger">Dihentikan</span>
                                                    @else
                                                        <span class="badge bg-warning">Status Tidak Diketahui</span>
                                                    @endif
                                                </td>
                                                <td id="camera-status-{{ $peserta->mahasiswa->nama ??  $peserta->peserta_eksternal->nama_peserta}}"> <!-- Placeholder for camera status -->
                                                    <span class="badge bg-secondary">Offline</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('monitoring.logAktivitas', $peserta->id_peserta_ujian) }}" data-toggle="tooltip" title="Log Aktivitas" class="btn btn-sm btn-secondary">
                                                        <i class="fa fa-repeat" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="{{ route('monitoring.beritaAcara', $peserta->id_peserta_ujian) }}" data-toggle="tooltip" title="Berita Acara" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-file-text" aria-hidden="true"></i>
                                                    </a>
                                                    @if ($peserta->status_pengerjaan == 1)
                                                        <a href="{{ route('monitoring.updateStatusPeserta', $peserta->id_peserta_ujian) }}" data-toggle="tooltip" title="Tunda Ujian" class="btn btn-sm btn-warning">
                                                            <i class="fa fa-pause-circle" aria-hidden="true"></i>
                                                        </a>
                                                        <a href="{{ route('peserta.finishUjian', ['id_ujian' => encrypt($peserta->ujian_id), 'id_peserta_ujian' => encrypt($peserta->id_peserta_ujian)]) }}" data-toggle="tooltip" title="Akhiri Ujian" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-ban" aria-hidden="true"></i>
                                                        </a>
                                                    @elseif($peserta->status_pengerjaan == 3)
                                                        <a href="{{ route('monitoring.updateStatusPeserta', $peserta->id_peserta_ujian) }}" data-toggle="tooltip" title="Mulai Ujian" class="btn btn-sm btn-success">
                                                            <i class="fa fa-play-circle" aria-hidden="true"></i>
                                                        </a>
                                                        <a href="{{ route('peserta.finishUjian', ['id_ujian' => encrypt($peserta->ujian_id), 'id_peserta_ujian' => encrypt($peserta->id_peserta_ujian)]) }}" data-toggle="tooltip" title="Akhiri Ujian" class="btn btn-sm btn-danger">
                                                            <i class="fa fa-ban" aria-hidden="true"></i>
                                                        </a>
                                                    @endif
                                                </td>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                </div>

                   <!-- Monitor Kamera -->
                <div class="tab-pane fade " id="monitor-camera" role="tabpanel" aria-labelledby="monitor-camera-tab">
                    <div class="row mt-3" id="participantsVideos">
                    </div>
                </div>
            </div>
        </div>

        {{-- Kanan --}}
        {{-- <div class="col-sm-12 col-xl-4">
            <div class="card bg-danger-subtle shadow-none">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0 fw-semibold">Informasi Ujian</h6>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-4">
                        <ul>
                            <li>1. Jumlah Peserta :{{ count($peserta_ujian) }} Mahasiswa</li>
                            <li>2. Jumlah Soal : {{ $jumlah_soal }} Soal</li>
                            <li>3. Belum Mengerjakan : {{ $belum_mengerjakan }} Mahasiswa</li>
                            <li>4. Sedang Mengerjakan : {{ $sedang_mengerjakan }} Mahasiswa</li>
                            <li>5. Selesai Mengerjakan : {{ $selesai_mengerjakan }} Mahasiswa</li>
                        </ul>

                    </div>
                </div>
            </div>
            <div class="card bg-success-subtle shadow-none">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0 fw-semibold">Informasi Warna</h6>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-4">
                        <ul>
                            <li>1. Orange : Belum Mengerjakan</li>
                            <li>2. Biru : Sedang Mengerjakan</li>
                            <li>3. Hijau : Selesai Mengerjakan</li>

                        </ul>

                    </div>
                </div>
            </div>
        </div> --}}

    </div>

@endsection

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.1/echo.iife.js"></script> --}}
    {{-- <script src="https://js.pusher.com/7.0/pusher.min.js"></script> --}}
    {{-- <script src="https://cdn.metered.ca/sdk/video/1.4.5/sdk.min.js"></script> --}}
    <script src="https://cdn.metered.ca/sdk/video/1.4.6/sdk.min.js"></script>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
        var status = '{{$ujian->status}}';
        {{-- if(status == 1){
            async function joinMeeting() {
                try {
                    const meeting = new Metered.Meeting();
                    const meetingInfo = await meeting.join({
                        roomURL: "{{ $roomUrl }}",
                        name: "Admin",
                    });

                    showNoParticipantsAlert();

                    // Event untuk menangkap track video dari setiap peserta yang bergabung
                    meeting.on("remoteTrackStarted", function(remoteTrackItem) {
                        if (remoteTrackItem.type === "video") {
                            hideNoParticipantsAlert();

                            const participantName= remoteTrackItem.participant.name;


                            //  camera status "Offline"
                            const cameraStatusElement = document.getElementById(`camera-status-${participantName}`);
                            if (cameraStatusElement) {
                                cameraStatusElement.innerHTML = '<span class="badge bg-success">Online</span>';
                            }

                            const videoElement = document.createElement("video");
                            videoElement.autoplay = true;
                            videoElement.muted = true;
                            videoElement.setAttribute("playsinline", "true");

                            // Untuk mengatur ukuran dan style video
                            videoElement.style.width = "100%";
                            videoElement.style.height = "150px";
                            videoElement.style.borderRadius = "10px";
                            videoElement.style.objectFit = "cover";

                            const mediaStream = new MediaStream();
                            mediaStream.addTrack(remoteTrackItem.track);
                            videoElement.srcObject = mediaStream;

                            // struktur card video
                            const colDiv = document.createElement("div");
                            colDiv.className = "col-2";
                            colDiv.style.margin = "10px";
                            colDiv.setAttribute("data-participant-id", remoteTrackItem.participantSessionId); // Set participantId untuk penghapusan nanti

                            const cardDiv = document.createElement("div");
                            cardDiv.className = "card";
                            cardDiv.style.width = "160px";
                            cardDiv.style.borderRadius = "10px";
                            cardDiv.style.boxShadow = "0 4px 8px rgba(0, 0, 0, 0.2)";
                            cardDiv.style.overflow = "hidden";

                            const nameElement = document.createElement("p");
                            nameElement.style.margin = "5px";
                            nameElement.style.textAlign = "center";
                            nameElement.style.fontSize = "14px";
                            nameElement.style.fontWeight = "bold";
                            nameElement.textContent = remoteTrackItem.participant.name;

                            cardDiv.appendChild(videoElement);
                            cardDiv.appendChild(nameElement);
                            colDiv.appendChild(cardDiv);

                            document.getElementById("participantsVideos").appendChild(colDiv);

                            // Tambahkan penundaan sebelum memutar video untuk memastikan stream terhubung
                            setTimeout(() => {
                                videoElement.play().catch(error => console.error("Error playing video with delay:", error));
                            }, 100);
                        }
                    });

                    // Event untuk menghapus video ketika peserta meninggalkan meeting
                    meeting.on("participantLeft", function(participant) {
                        console.log("Participant left:", participant);

                        //  camera status "Offline"
                        const cameraStatusElement = document.getElementById(`camera-status-${participant.name}`);
                        if (cameraStatusElement) {
                            cameraStatusElement.innerHTML = '<span class="badge bg-secondary">Offline</span>';
                        }

                        const colDiv = document.querySelector(`[data-participant-id="${participant._id}"]`);
                        if (colDiv) {
                            colDiv.remove();
                        }
                    });

                } catch (error) {
                    alert("Failed to join the meeting as admin. Please try again.");
                }
            }
            joinMeeting();

        } --}}
        function showNoParticipantsAlert() {
            const alertElement = document.createElement("div");
            alertElement.className = "card card-body alert alert-warning alert-dismissible fade show";
            alertElement.role = "alert";
            alertElement.id = "noParticipantsAlert";
            alertElement.innerHTML = `
                <strong> Camera Peserta Tidak Ditemukan. </strong>
            `;
            document.getElementById("participantsVideos").appendChild(alertElement);
        }


        function hideNoParticipantsAlert() {
            const alertElement = document.getElementById("noParticipantsAlert");
            if (alertElement) {
                alertElement.remove();
            }
        }
    </script>
    <script>
        function reloadPage() {
            location.reload();
        }
    </script>
@endpush
