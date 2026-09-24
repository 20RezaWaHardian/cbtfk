@extends('layouts.app')
@section('title', 'Bank Soal - Show Data Soal')
@section('contents')

    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Analisis Soal Pada Judul Paket : {{ $paket_soal->judul ?? '' }}</h4>
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <dic class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Pertanyaan</th>
                                <th>Jumlah Yang Jawab Benar</th>
                                <th>Jumlah Yang Jawab Salah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$item["pertanyaan"]}}</td>
                                    <td style="background-color: rgb(136, 238, 136);color:black">{{$item["jumlah_benar"]}}</td>
                                    <td style="background-color: rgb(246, 91, 88);color:black">{{$item["jumlah_salah"]}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </dic>
    </div>
@endsection

@push('modal')
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog modal-md" role="document">

        </div>
    </div>

@endpush

@push('scripts')
@endpush
