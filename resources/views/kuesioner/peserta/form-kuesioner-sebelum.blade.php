@extends('layouts.exam.app')

@section('title', 'Kuesioner')

@push('style')
<style>
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ccc;
    }



    input[type="radio"] {
    transform: scale(1.5);  /* Memperbesar ukuran radio button */
    cursor: pointer; /* Memastikan radio button tetap interaktif */
    margin: 0 auto;
}
</style>
@endpush

@section('contents')
    <div class="card bg-info-subtle position-relative overflow-hidden mx-auto text-center" style="max-width: 800px; z-index:2">
        <div class="card-body px-4 py-3">
            <div class="row">
                <div class="col-12 align-items-center">
                    <h5 class="fw-semibold"><p>{{ $kuesioner->judul_kuesioner }}</p></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card position-relative overflow-hidden mb-6" style="margin-top: -60px;">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12 mt-2">
                    <p class="fw-semibold mt-2">Petunjuk Pengisian:</p>
                    <ul class="pl-3">
                        {{-- <li>1. Kuesioner berupa pertanyaan yang memuat beberapa aspek seperti (Aspek Tangibles, Aspek Reliability, Aspek Responsiviness, Aspek Assurance, Aspek Empathy dan Aspek Information System).</li> --}}
                        <li>1. Pertanyaan Menggunakan Skala Likert dengan rentang 1-5</li>
                        <li>2. Peserta bebas menjawab diantara rentang tersebut</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    <!-- Form Start -->
    <form action="{{ route('peserta.kuesionerStoreSebelum',['id_peserta_ujian'=> $peserta->id_peserta_ujian,'id_ujian'=> $ujian->id_ujian,'id_kuesioner'=> $kuesioner->id_kuesioner]) }}" method="POST" id="kuesionerForm">
        @csrf

        <div class="card position-relative overflow-hidden mb-6">
            <div class="card-body px-4 py-3">
                <div class="row text-start">
                    <div class="col-12 mt-2">
                        @foreach ($kuesioner->kategori_kuesioner as $aspek)
                        <p class="fw-semibold mt-2">{{ $aspek->nama_kategori }}</p>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:60%;">Pertanyaan</th>
                                    @if($aspek->jenis == 'point')
                                    <input type="hidden" name="ada_terbuka" value="0">
                                    {{-- <th style="width:10%;"class="text-center">Sangat Tidak Puas</th>
                                    <th style="width:10%;"class="text-center">Tidak Puas</th>
                                    <th style="width:10%;"class="text-center">Puas</th>
                                    <th style="width:10%;"class="text-center">Sangat Puas</th> --}}
                                    @for ($i = 1; $i <= 4; $i++)
                                        <th style="width:10%;"class="text-center">{{ $i }}</th>
                                    @endfor
                                    @else
                                    <input type="hidden" name="ada_terbuka" value="1">
                                    <th colspan="4" class="text-center">Jawaban</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($aspek->pertanyaan as $pertanyaan)
                                    @php
                                        $cekjwb = $jawaban[$pertanyaan->id_pertanyaan_kuesioner] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $pertanyaan->pertanyaan }}</td>
                                        @if($pertanyaan->jenis_pertanyaan == 'point')
                                            @for ($i = 1; $i <= 4; $i++)
                                                <td class="text-center">
                                                    <input type="radio" id="pertanyaan_{{ $pertanyaan->id_pertanyaan_kuesioner }}_{{ $i }}"
                                                    name="pertanyaan[{{ $pertanyaan->id_pertanyaan_kuesioner }}]" 
                                                    value="{{ $i }}"
                                                    {{ ($cekjwb && $cekjwb->jawaban == $i) ? 'checked' : '' }}
                                                >
                                                </td>
                                            @endfor
                                        @else
                                            <td>
                                                <textarea cols="50" rows="5" name="jawaban_terbuka[{{ $pertanyaan->id_pertanyaan_kuesioner }}]"></textarea>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <!-- Button Kirim Jawaban -->
        <div class="card position-relative overflow-hidden mb-6">
            <div class="card-body px-4 py-3 text-center">
                <button type="submit" id="submitBtn" class="btn btn-primary">Kirim Jawaban</button>
                @if($can == true)
                <a href="{{ route('peserta.faceRegister', [
                'id_ujian' => encrypt($ujian->id_ujian), 
                'id_peserta_ujian' => encrypt($peserta->id_peserta_ujian)]) }}" 
                class="btn btn-info masukRoom">Mulai Ujian</a> <br/>
                @endif
            </div>
        </div>
    </form>
    <!-- Form End -->

@endsection

@push('modal')
@endpush

@push('scripts')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $('.masukRoom').on('click', function() {

            event.preventDefault(); // Menghentikan perilaku default dari link

            Swal.fire({
                title: 'Apakah anda yakin akan memasuki room ujian?',
                text: "Jika ya, maka aksi ini tidak bisa dibatalkan lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Masuk ke room ujian'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to the exam start page
                    window.location.href = $(this).attr('href');
                }
            });


        });
</script>

<script>
    document.getElementById('submitBtn').addEventListener('click', function (event) {
    let isValid = true;
    let firstUnansweredRadio = null;

    // Loop melalui setiap tabel di dalam form untuk memeriksa pertanyaan
    document.querySelectorAll('table').forEach(function (table) {
        // Loop melalui setiap baris pertanyaan dalam tabel
        table.querySelectorAll('tbody tr').forEach(function (row) {
            let radioButtons = row.querySelectorAll('input[type="radio"]'); // Semua radio button di dalam baris ini
            let name = radioButtons[0].getAttribute('name'); // Nama dari radio button (untuk grup pertanyaan)

            // Periksa apakah ada radio button yang dipilih dalam grup ini
            let isAnswered = document.querySelector(`input[name='${name}']:checked`);

            // Jika tidak ada jawaban yang dipilih, tandai sebagai tidak valid
            if (!isAnswered) {
                isValid = false;
                if (!firstUnansweredRadio) {
                    // Simpan radio button pertama yang belum dijawab untuk memberikan fokus nanti
                    firstUnansweredRadio = radioButtons[0];
                }
            }
        });
    });

    // Jika ada pertanyaan yang belum dijawab, tampilkan alert dan arahkan fokus ke radio button pertama yang belum dijawab
    if (!isValid) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Semua pertanyaan harus dijawab!',
            background: '#f8d7da',
            color: '#721c24',
        });

        // Arahkan fokus ke radio button pertama yang belum dijawab
        if (firstUnansweredRadio) {
            firstUnansweredRadio.focus();
            firstUnansweredRadio.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        event.preventDefault(); // Mencegah pengiriman form
    }
});

</script>
@endpush
