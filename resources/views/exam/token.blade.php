@extends('layouts.exam.app')

@section('title', 'Ujian - Peserta Ujian')


@section('contents')
    <div class="card bg-info-subtle position-relative overflow-hidden mx-auto text-center" style="max-width: 300px;">
        <div class="card-body px-4 py-4">
            <div class="row">
                <div class="col-12 align-items-center">
                    <h5 class="fw-semibold">Silahkan Inputkan Token Yang Diberikan Oleh Pengawas Ujian </h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card position-relative overflow-hidden mb-6" style="margin-top: -60px;">
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12 mt-2">
                    <form action="{{ route('peserta.cekToken') }}" method="post">
                        @csrf
                        <input type="hidden" name="id_ujian" value="{{ encrypt($ujian->id_ujian) }}">
                        <input type="hidden" name="id_peserta_ujian" value="{{ encrypt($peserta->id_peserta_ujian) }}">
                        <div class="mb-2">
                            <label>Masukkan Token</label>
                            <input type="text" class="form-control" name="token" id="token">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Kirim
                        </button>
                    </form>


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
@endpush
