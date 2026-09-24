@extends('layouts.app')
@section('title', 'Log Aktivitas')
@push('style')
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
@endpush
@section('contents')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-1">Log Aktivitas Peserta</h5>
            <p class="mb-0"> Berikut Merupakan Log Aktivitas Peserta</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <a href="{{ route('ujian.monitoring.index',encrypt($peserta->ujian_id))  }}" class="btn btn-sm btn-primary">Kembali</a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <div style="overflow: hidden; border-radius: 15px;">
                <img src="{{ asset('regis_peserta_ujian/' . $peserta->face_register) }}" alt="Foto Peserta" style="width: 100%; height: auto; object-fit: cover; border-radius: 15px;">
            </div>
            <h6 style="text-align: center;" class="mt-2">Face Register</h6>
        </div>
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <div style="overflow: hidden; border-radius: 15px;">
                <img src="{{ asset('regis_peserta_ujian/' . $peserta->face_register) }}" alt="Foto Peserta" style="width: 100%; height: auto; object-fit: cover; border-radius: 15px;">
            </div>
            <h6 style="text-align: center;" class="mt-2">Foto Peserta</h6>
        </div>
        <div class="col-sm-12 col-xl-8 table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>NAMA</th>
                        <td>{{ $peserta->mahasiswa->nama ?? $peserta->peserta_eksternal->nama_peserta }}</td>
                    </tr>

                    <tr>
                        <th>NIM</th>
                        <td>{{ $peserta->mahasiswa->nim ?? $peserta->peserta_eksternal->username }}</td>
                    </tr>

                    <tr>
                        <th>UJIAN</th>
                        <td>{{ $peserta->ujian->nama_ujian }}</td>
                    </tr>
                    <tr>
                        <th>PAKET SOAL</th>
                        <td>{{ $peserta->ujian->paket_soal->judul }}</td>
                    </tr>
                    <tr>
                        <th>IP ADDRESS</th>
                        <td>{{ $peserta->ip_address }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-sm-12 col-xl-12 table responsive">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($log as $dt)
                            <tr>
                                <td>{{ $dt->tanggal ?? '-' }}</td>
                                <td>{{ $dt->aktivitas ?? '-'}}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center">Data Tidak Ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                <table>

            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush
