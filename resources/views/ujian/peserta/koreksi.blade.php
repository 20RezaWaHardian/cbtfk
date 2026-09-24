@extends('layouts.app')

@section('title', 'Ujian - Koreksi Ujian')

@push('style')
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">{{$peserta_ujian->mhs_pt ? $peserta_ujian->mhs_pt->mahasiswa->nama_mahasiswa : $peserta_ujian->peserta_eksternal->nama_peserta}}</h4>
                    <p>{{ $peserta_ujian->ujian->nama_ujian }} </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="border-radius: 15px; overflow: hidden; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <div class="card-body" style="padding: 20px;">
            <div class="row">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Hasil Tangkapan Kamera :</h4>
                </div>

                <div class="col-3" style="margin-bottom: 20px;">
                    <div style="overflow: hidden; border-radius: 15px;">
                        <img src="{{ asset('regis_peserta_ujian/' . $peserta_ujian->face_register) }}" alt="Foto Peserta" style="width: 100%; height: auto; object-fit: cover; border-radius: 15px;">
                    </div>
                    <h6 style="text-align: center;" class="mt-2">Face Register</h6>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <div class="card pt-3 pr-3 pl-3 pb-3">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="koreksi-tab" data-toggle="tab" href="#koreksi" role="tab" aria-controls="koreksi" aria-selected="true">Koreksi Jawaban</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="hasil-tab" data-toggle="tab" href="#hasil" role="tab" aria-controls="hasil" aria-selected="false">Hasil Ujian</a>
                    </li>
                </ul>

                <div class="tab-content mr-3 ml-3">

                    <div class="tab-pane active" id="koreksi" role="tabpanel" aria-labelledby="koreksi-tab">
                        <div id="koreksi" class="card-body">
                        @if($koreksi_jawaban->count() != 0)
                            @foreach ($koreksi_jawaban as $item)
                            <div class="alert alert-success text-left" role="alert">
                                Pertanyaan : {!!$item->soal->pertanyaan!!}
                                kunci jawaban : {!!$item->soal->kunci!!}
                                Jawaban Peserta : {{$item->jawab}}
                                Poin Max : {!!$item->soal->poin!!}
                                <hr>
                                <div class="row">
                                    <div class="col-md-8 text-left"></div>
                                    <div class="col-md-4 ">
                                        <form action="{{ route('ujian.peserta.updateScoreEssay') }}" method="post">
                                        @csrf
                                        @method('PATCH')
                                            <input type="hidden" name="id" id="id" value="{{$item->id_essay_jawab}}">
                                            <div class="input-group">
                                            <input type="number" name="score" class="form-control" placeholder="Score" aria-label="score" aria-describedby="button-addon2" max="{{$item->soal->poin}}" value="{{ $item->soal->poin }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="submit" id="simpan">Simpan</button>
                                            </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong> Tidak ada jawaban peserta yang perlu dikoreksi </strong>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>

                        @endif
                        </div>
                    </div>
                    <div class="tab-pane " id="hasil" role="tabpanel" aria-labelledby="hasil-tab">
                        <div id="hasil" class="card-body">
                            @if ($peserta_ujian->nilai !== null)
                            <div class="row justify-content-center">
                                <div class="col-md-4">
                                    <div class="alert alert-success pt-1 pb-1" role="alert">
                                        Total Score : {{$peserta_ujian->nilai}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-success pt-1 pb-1" role="alert">
                                        Total Poin : {{ $total_poin }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-success pt-1 pb-1" role="alert">
                                        Nilai Akhir : {{ $peserta_ujian->total_nilai() }}
                                    </div>
                                </div>
                            </div>
                            @else
                            <div class="row justify-content-center">
                                <div class="col-md-4">
                                    <div class="alert alert-success pt-1 pb-1" role="alert">
                                        Total Score : {{$peserta_ujian->nilai}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-success pt-1 pb-1" role="alert">
                                        Total Poin : {{ $total_poin }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="alert alert-success pt-1 pb-1" role="alert">
                                        Nilai Akhir : -
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if ($pilgan_jawab->count() != 0)
                            <h5> <strong>Hasil Ujian Pilihan Ganda Peserta</strong> </h5>
                            <table class="table table-striped table-bordered table-sm text-center">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th scope="col" style="width:50px">No</th>
                                        <th scope="col" style="width:400px">Jawaban Peserta</th>
                                        <th scope="col" style="width:150px">Kunci Jawaban</th>
                                        <th scope="col" style="width:150px">Keterangan</th>
                                        <th scope="col" style="width:140px">Score</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; ?>
                                    @foreach ($pilgan_jawab as $item)
                                    <tr>
                                        <td scope="row"><?php  $i++;  echo $i; ?></td>
                                        <td>{{$item->jawab}}</td>
                                        <td>{{$item->soal->kunci}}</td>
                                        <td>@if ($item->status == 'T') Benar @else Salah @endif</td>
                                        <td>{{$item->score}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif

                            @if ($essay_jawab->count() != 0)
                            <h5> <strong> Hasil Ujian Essay Peserta</strong></h5>
                            <table class="table table-striped table-bordered table-sm">
                                <thead class="thead-dark text-center">
                                    <tr>
                                        <th scope="col" style="width:50px">No</th>
                                        <th scope="col" style="width:400px">Pertanyaan</th>
                                        <th scope="col" style="width:150px">Jawaban Peserta</th>
                                        <th scope="col" style="width:150px">Poin Soal</th>
                                        <th scope="col" style="width:140px">Score</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; ?>
                                    @foreach ($essay_jawab as $item)
                                    <tr>
                                        <td class="text-center" scope="row"><?php  $i++;  echo $i; ?></td>
                                        <td>{!!$item->soal->pertanyaan!!}</td>
                                        <td>{!!$item->jawab!!}</td>
                                        <td class="text-center" >{!!$item->soal->poin!!}</td>
                                        <td class="text-center" >{{$item->score}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
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
