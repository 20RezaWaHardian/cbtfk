@extends('layouts.app')

@section('title', 'OSCE - Detail Mahasiswa')

@push('style')
    <!-- CSS Libraries -->
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('summernote/summernote-lite.css') }}" rel="stylesheet">
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Detail Mahasiswa OSCE</h4>
                </div>

            </div>
        </div>
    </div>
    <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <table class="table table-bordered">
                        <tr>
                            <td>Jadwal</td><td>:</td><td>{{$jadwal->keterangan}}</td>
                        </tr>
                        <tr>
                            <td>Tanggal Ujian</td><td>:</td><td>{{$jadwal->tanggal_ujian}}</td>
                        </tr>
                        <tr>
                            <td>Waktu</td><td>:</td><td>{{ date('H:i', strtotime($jadwal->waktu_mulai)) }} - {{ date('H:i', strtotime($jadwal->waktu_selesai)) }} WIB</td>
                        </tr>
                        <tr>
                            <td>Stase</td><td>:</td><td>{{$jadwal->nama_jenis_osce}}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="myTable">
                            <thead >
                                <tr>
                                  <th scope="col">#</th>
                                  <th scope="col">NIM</th>
                                  <th scope="col">Nama Mahasiswa</th>
                                  <th scope="col">Urutan</th>
                                  <th scope="col">Status</th>
                                  <th scope="col">Nilai Stase</th>
                                  <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                                {{-- @foreach($data as $p)
                                <tr>
                                    <th scope="row">{{$loop->iteration}}</th>
                                    <td>{{$p->no_mhs}} </td>
                                    <td>{{$p->nama_mahasiswa}} </td>

                                    <td>{{$p->nilai ?? 0}}</td>
                                    <td>
                                        <a href="{{route('data-master.beriNilai',[
                                        'id_mhs_pt'=>encrypt($p->id_mhs_pt),
                                        'id_jadwal_osce'=>encrypt($jadwal->id_jadwal_osce),
                                        'id_jenis_osce'=>encrypt($jadwal->id_jenis_osce)
                                        ])}}" class="btn btn-sm btn-info">
                                         <i class="fa fa-eye"></i> Beri Nilai
                                        </a>
                                       
                                    </td>
                                </tr>
                                @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('modal')
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog modal-lg" role="document">

        </div>
    </div>
@endpush

@push('scripts')
    <!-- js for this page only -->
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('summernote/summernote-lite.js') }}"></script>

    <script>
        $(document).ready(function() {
            var id_jadwal_osce = '{{ encrypt($jadwal->id_jadwal_osce) }}';
            var id_jenis_osce = '{{ encrypt($jadwal->id_jenis_osce) }}';
            $('#myTable').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 20,
                lengthMenu: [[20, 25, -1], [20, 25, "Semua"]],
                ajax: {
                    url: `/data-master/ujian-osce/`+id_jadwal_osce+`/stase/`+id_jenis_osce,
                    type: "GET", // default GET, boleh ditulis biar jelas
                    error: function (xhr) {
                        console.log(xhr.responseText); // bantu debugging kalau error
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'nim', name: 'nim' },
                    { data: 'nama_mahasiswa', name: 'nama_mahasiswa' },
                    { data: 'urutan_antrian', name: 'urutan_antrian' },
                    { data: 'status_station', name: 'status_station', searchable: false },
                    { data: 'nilai_akhir', name: 'nilai_akhir', searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
                ],
                order: [[1, 'asc']] // default sorting berdasarkan NIM
            });

        })
    </script>

    
@endpush
