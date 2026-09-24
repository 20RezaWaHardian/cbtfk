@extends('layouts.app')

@section('title', 'Ujian - Resume Ujian')
<link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
<link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
    rel="stylesheet" />
@push('style')
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">{{ $ujian->nama_ujian }}</h4>
                    <p> {{ $ujian->tanggal_ujian }}  s.d {{ $ujian->selesai_ujian }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="alert alert-success pt-1 pb-1" role="alert">
                Pogram Studi : {{ $ujian->prodi->nama_prodi ??'-' }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success pt-1 pb-1" role="alert">
                Jumlah Peserta : {{ count($ujian->peserta_ujian) }}
            </div>
        </div>
        <div class="col-md-4">
            <div class="alert alert-success pt-1 pb-1" role="alert">
                Jumlah Soal : {{ count($ujian->paket_soal->soal) }}
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>


@endsection


@push('modal')
@endpush

@push('scripts')
<script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{ $dataTable->scripts() }}
@endpush
