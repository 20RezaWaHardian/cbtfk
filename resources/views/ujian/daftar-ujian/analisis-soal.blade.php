@extends('layouts.app')

@section('title', 'Ujian - Analisis Butir Soal Pilihan Ganda')

@push('style')
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">{{$ujian->nama_ujian}}</h4>
                    <p>{{ $ujian->paket_soal->judul }} </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card pt-3 pr-3 pl-3 pb-3">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="koreksi-tab" data-toggle="tab" href="#koreksi" role="tab" aria-controls="koreksi" aria-selected="true">Rank Peserta</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="hasil-tab" data-toggle="tab" href="#hasil" role="tab" aria-controls="hasil" aria-selected="false">Hasil Analisis</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="tab-content mr-3 ml-3">

        <div class="tab-pane active" id="koreksi" role="tabpanel" aria-labelledby="koreksi-tab">
            <div id="koreksi" class="card-body">
                <div class="card">
                    <div class="card-body">
                        <h5> <strong> Analisis Butir Soal Pilihan Ganda</strong> </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm text-center">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th rowspan="3" class="text-center" style="width:50px">No</th>
                                        <th rowspan="3" class="text-center" style="width:400px">NIM</th>
                                        {{-- <th rowspan="3">Nama Peserta</th> --}}
                                        <th colspan="{{ $jumlahSoal }}" class="text-center">Jawaban Soal dan Kunci Jawaban Nomor</th>
                                        <th rowspan="3" class="text-center">Skor</th>
                                        <th rowspan="3" class="text-center">Nilai</th>
                                        <th rowspan="3" class="text-center">Rank</th>
                                    </tr>
                                    <tr>
                                        @foreach ($kunciJawaban as $index => $kunci)
                                            <th>{{ ($index + 1) }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($kunciJawaban as $index => $kunci)
                                            <th>{!! $kunci !!}</th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        // Menghitung skor setiap peserta
                                        $pesertaSkor = [];
                                        foreach ($pesertaUjian as $peserta) {
                                            $skor = 0;
                                            foreach ($jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian) as $jawaban) {
                                                $soal = $jawaban->soal;
                                                if ($jawaban->jawab == $soal->kunci) {
                                                    $skor++;
                                                }
                                            }
                                            $pesertaSkor[$peserta->id_peserta_ujian] = $skor;
                                        }

                                        // ini untuk mengurutkan peserta berdasarkan skor untuk mendapatkan rank
                                        arsort($pesertaSkor);
                                    @endphp

                                    @foreach ($pesertaUjian as $index => $peserta)
                                        @php

                                            $skor = $pesertaSkor[$peserta->id_peserta_ujian];
                                            $nilai = ($skor / $jumlahSoal) * 100;
                                            $rank = array_search($peserta->id_peserta_ujian, array_keys($pesertaSkor)) + 1;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $peserta->mhs_pt->no_mhs }}</td>
                                            {{-- <td>{{ $peserta->mhs_pt->mahasiswa->nama_mahasiswa }}</td> --}}


                                            @foreach ($kunciJawaban as $index => $kunci)
                                                <td>
                                                    @php
                                                        $jawabanPesertaForSoal = $jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian)->where('soal_id', $index + 1)->first();
                                                    @endphp
                                                    {{-- Cek apakah jawaban ada --}}
                                                    {{ $jawabanPesertaForSoal ? $jawabanPesertaForSoal->jawab : 'X' }}
                                                </td>
                                            @endforeach

                                            <td>{{ $skor }}</td>
                                            <td>{{ number_format($nilai, 2) }}</td>

                                            <td>{{ $rank }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5> <strong> Peserta Berdasarkan Rank</strong> </h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm text-center">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th class="text-center" style="width:50px">Rank</th>
                                        <th class="text-center" style="width:400px">NIM</th>
                                        {{-- <th>Nama Peserta</th> --}}
                                        <th class="text-center">Skor</th>
                                        <th class="text-center">Nilai</th>
                                        <th class="text-center">Keterangan</th>

                                    </tr>

                                </thead>

                                <tbody>
                                    @php
                                    // Menghitung skor setiap peserta
                                    $pesertaSkor = [];
                                    foreach ($pesertaUjian as $peserta) {
                                        $skor = 0;
                                        foreach ($jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian) as $jawaban) {
                                            $soal = $jawaban->soal;
                                            if ($jawaban->jawab == $soal->kunci) {
                                                $skor++;
                                            }
                                        }
                                        $pesertaSkor[$peserta->id_peserta_ujian] = $skor;
                                    }

                                    // Mengurutkan peserta berdasarkan skor untuk mendapatkan rank (arsort mengurutkan skor dari tertinggi ke terendah)
                                    arsort($pesertaSkor);

                                    // Menyusun peserta berdasarkan urutan skor tertinggi
                                    $rankedPeserta = [];
                                    foreach (array_keys($pesertaSkor) as $index => $pesertaId) {
                                        $rankedPeserta[$pesertaId] = [
                                            'rank' => $index + 1, // Rank mulai dari 1
                                            'skor' => $pesertaSkor[$pesertaId]
                                        ];
                                    }

                                    // Mengurutkan pesertaUjian berdasarkan rank mereka
                                    $sortedPesertaUjian = $pesertaUjian->sortBy(function ($peserta) use ($rankedPeserta) {
                                        return $rankedPeserta[$peserta->id_peserta_ujian]['rank'];
                                    });


                                @endphp

                                @foreach ($sortedPesertaUjian as $index => $peserta)
                                    @php

                                        $rank = $rankedPeserta[$peserta->id_peserta_ujian]['rank'];
                                        $skor = $rankedPeserta[$peserta->id_peserta_ujian]['skor'];
                                        // Menghitung skor persentase
                                        $persentase = ($skor * 100) / $jumlahSoal;

                                        // Menentukan grade berdasarkan persentase
                                        if ($persentase >= 80) {
                                            $grade = 'A';
                                        } elseif ($persentase >= 77) {
                                            $grade = 'A-';
                                        } elseif ($persentase >= 75) {
                                            $grade = 'B+';
                                        } elseif ($persentase >= 70) {
                                            $grade = 'B';
                                        } elseif ($persentase >= 67) {
                                            $grade = 'B-';
                                        } elseif ($persentase >= 62) {
                                            $grade = 'C+';
                                        } elseif ($persentase >= 60) {
                                            $grade = 'C';
                                        } elseif ($persentase >= 55) {
                                            $grade = 'D+';
                                        } elseif ($persentase >= 45) {
                                            $grade = 'D';
                                        } else {
                                            $grade = 'E';
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $rank }}</td>
                                        <td>{{ $peserta->mhs_pt->no_mhs }}</td>
                                        {{-- Loop untuk jawaban setiap soal --}}
                                        {{-- <td>{{ $peserta->mhs_pt->nama_mahasiswa }}</td> --}}
                                        <td>{{ $skor }}</td>
                                        <td>{{ $skor * 100 / $jumlahSoal }}</td>
                                        <td>  {{ $grade }}</td>

                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane " id="hasil" role="tabpanel" aria-labelledby="hasil-tab">
            <div id="hasil" class="card-body">

                <div class="card">
                    <div class="card-body">
                        <h5><strong>Proporsi Peserta</strong></h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm text-center">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th rowspan="3" class="text-center" style="width:50px">Rank</th>
                                        <th rowspan="3" class="text-center" style="width:400px">NIM</th>
                                        <th colspan="{{ $jumlahSoal }}" class="text-center">Jawaban Soal dan Kunci Jawaban Nomor</th>
                                    </tr>
                                    <tr>
                                        @foreach ($kunciJawaban as $index => $kunci)
                                            <th>{{ ($index + 1) }}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach ($kunciJawaban as $index => $kunci)
                                            <th>{!! $kunci !!}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody id="pesertaTableBody">
                                    <tr><th colspan="{{ $jumlahSoal + 2 }}" class="text-center">Rank Atas</th></tr>

                                    @foreach ($pesertaRankAtas as $peserta)
                                        <tr>
                                            <td>{{ $rankedPeserta[$peserta->id_peserta_ujian]['rank'] }}</td>
                                            <td>{{ $peserta->mhs_pt->no_mhs }}</td>
                                            @foreach ($kunciJawaban as $index => $kunci)
                                                <td>
                                                    @php
                                                        $jawabanPesertaForSoal = $jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian)->where('soal_id', $index + 1)->first();
                                                    @endphp
                                                    {{ $jawabanPesertaForSoal ? $jawabanPesertaForSoal->jawab : 'X' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="2"><strong>JB/JP</strong></td>
                                        @foreach ($correctAnswersPerQuestionAtas as $correctAnswerCount)
                                            <td>{{ number_format($correctAnswerCount/$jumlahPesertaRankAtas,2) }}</td>
                                        @endforeach
                                    </tr>
                                    {{-- <tr>
                                        <td colspan="2"><strong>JB</strong></td>
                                        @foreach ($correctAnswersPerQuestionAtas as $correctAnswerCount)
                                            <td>{{ $correctAnswerCount }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>JP</strong></td>
                                        @foreach ($correctAnswersPerQuestionAtas as $correctAnswerCount)
                                            <td>{{ $jumlahPesertaRankAtas }}</td>
                                        @endforeach
                                    </tr> --}}

                                    <tr><th colspan="{{ $jumlahSoal + 2 }}" class="text-center">Rank Bawah</th></tr>


                                    @foreach ($pesertaRankBawah as $peserta)
                                        <tr>
                                            <td>{{ $rankedPeserta[$peserta->id_peserta_ujian]['rank'] }}</td>
                                            <td>{{ $peserta->mhs_pt->no_mhs }}</td>
                                            @foreach ($kunciJawaban as $index => $kunci)
                                                <td>
                                                    @php
                                                        $jawabanPesertaForSoal = $jawabanPeserta->where('peserta_ujian_id', $peserta->id_peserta_ujian)->where('soal_id', $index + 1)->first();
                                                    @endphp
                                                    {{ $jawabanPesertaForSoal ? $jawabanPesertaForSoal->jawab : 'X' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach


                                    <tr>
                                        <td colspan="2"><strong>JB/JP</strong></td>
                                        @foreach ($correctAnswersPerQuestionBawah as $correctAnswerCount)

                                            <td>{{ number_format($correctAnswerCount/$jumlahPesertaRankBawah,2) }}</td>
                                        @endforeach
                                    </tr>
                                    {{-- <tr>
                                        <td colspan="2"><strong>JB</strong></td>
                                        @foreach ($correctAnswersPerQuestionBawah as $correctAnswerCount)
                                            <td>{{ $correctAnswerCount }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>JP</strong></td>
                                        @foreach ($correctAnswersPerQuestionBawah as $correctAnswerCount)
                                            <td>{{ $jumlahPesertaRankBawah }}</td>
                                        @endforeach
                                    </tr> --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><strong>Daya Beda</strong></h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm text-center">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th class="text-center" rowspan="2" style="width:50px">&nbsp;</th>
                                            <th colspan="{{ $jumlahSoal }}" class="text-center" style="width:50px">Nomor Soal</th>

                                        </tr>
                                        <tr>
                                            @foreach ($correctAnswersPerQuestionAtas as $soalId => $correctAnswerCount)
                                                <th class="text-center">{{ $soalId }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td><b>Daya Beda</b></td>
                                            @foreach ($correctAnswersPerQuestionAtas as $soalId => $correctAnswerCount)
                                                @php
                                                    // rasio jawaban benar pada Rank Atas
                                                    $correctRatioAtas = $correctAnswerCount / $jumlahPesertaRankAtas;
                                                    #hitung jawabanbenar bos
                                                    $correctAnswerBawah = $correctAnswersPerQuestionBawah[$soalId] ?? 0;

                                                    $difference = $correctRatioAtas - ($correctAnswerBawah / $jumlahPesertaRankBawah);

                                                    if ($difference > 0.3) {
                                                        $status = 'Baik';
                                                    } else {
                                                        $status = 'Kurang Baik';
                                                    }
                                                @endphp

                                                <td >{{ number_format($difference, 2) }} <br>[{{ $status }}]</td>
                                            @endforeach
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5><strong>Tingkat Kesukaran</strong></h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm text-center">
                                    <thead class="thead-dark text-center">
                                        <tr>
                                            <th class="text-center" rowspan="2" style="width:50px">&nbsp;</th>
                                            <th colspan="{{ $jumlahSoal }}" class="text-center" style="width:50px">Nomor Soal</th>

                                        </tr>
                                        <tr>
                                            @foreach ($correctAnswersPerQuestionAtas as $soalId => $correctAnswerCount)
                                                <th class="text-center">{{ $soalId }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr>
                                            <td><b>Tingkat Kesukaran</b></td>
                                            @foreach ($correctAnswersPerQuestionAtas as $soalId => $correctAnswerCount)
                                                @php

                                                    $correctRatioAtas = $correctAnswerCount / $jumlahPesertaRankAtas;
                                                    $correctAnswerBawah = $correctAnswersPerQuestionBawah[$soalId] ?? 0;

                                                    $differenceAtas = $correctRatioAtas;
                                                    $differenceBawah = $correctAnswerBawah / $jumlahPesertaRankBawah;


                                                    $averageDifficulty = ($differenceAtas + $differenceBawah) / 2;

                                                    if ($averageDifficulty > 0.3 && $averageDifficulty <= 0.7) {
                                                        $status = 'Sedang';
                                                    } else {
                                                        $status = 'Sulit';
                                                    }
                                                @endphp

                                                <td>{{ number_format($averageDifficulty, 2) }} <br> [{{ $status }}]</td>
                                            @endforeach
                                        </tr>

                                    </tbody>
                                </table>
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
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
          $('#myTab li:first-child a').tab('show')
        })
      </script>
@endpush
