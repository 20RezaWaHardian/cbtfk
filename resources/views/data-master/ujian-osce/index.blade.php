@extends('layouts.app')

@section('title', 'OSCE - Ujian OSCE')

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
                    <h4 class="fw-semibold mb-8">Ujian OSCE</h4>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead >
                                <tr>
                                  <th scope="col">#</th>
                                  <th scope="col">Keterangan</th>
                                  <th scope="col">Tanggal Ujian</th>
                                  <th scope="col">Waktu Ujian</th>
                                  <th scope="col">Station</th>
                                  <th scope="col">Status</th>
                                  <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($data as $p)
                                <tr>
                                    <th scope="row">{{$loop->iteration}}</th>
                                    <td>{{$p->keterangan}} </td>
                                    <td>{{$p->tanggal_ujian}} </td>
                                    <td>{{$p->waktu_mulai}} - {{$p->waktu_selesai}} WIB</td>
                                    <td>{{$p->nama_jenis_osce}}</td>
                                    <td>
                                        @if($p->status == 1)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($p->status === 1)
                                        <a href="{{route('data-master.detailMahasiswa',['id_jadwal_osce'=>encrypt($p->id_jadwal_osce),'id_jenis_osce'=>encrypt($p->id_jenis_osce)])}}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i> Detail</a>
                                        @else
                                            <span class="badge bg-warning">Sudah Berakhir</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
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
        $('.tambah-data').on('click', function() {
            let id = $(this).data().id;

            $('#loading-indicator').show();
            $('#modalAction').find('.loading-overlay').show();
            
            $.ajax({
                method: 'GET',
                url: `/data-master/tambah/peserta-jadwal-osce/`+id,
                success: function(res) {
                    $('#loading-indicator').hide();
                    $('#modalAction').find('.loading-overlay').hide();
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');

                    
                    // Tidak perlu panggil initForm() lagi karena pakai event delegation
                },
                error: function(xhr, status, error) {
                    $('#loading-indicator').hide();
                    $('#modalAction').find('.loading-overlay').hide();
                    console.error('Error loading form:', error);
                    alert('Terjadi kesalahan saat memuat form');
                }
            });

            $('#modalAction').on('shown.bs.modal', function () {
                $('#id_mhs_pt').select2({
                    placeholder: "Cari Mahasiswa..",
                    dropdownParent: $('#modalAction'),
                    ajax: {
                        url: "{{ url('/get-mahasiswa') }}",
                        dataType: "json",
                        data: function(param) {
                            return {
                                search: param.term
                            };
                        },
                        processResults: function(hasil) {
                            return {
                                results: hasil
                            };
                        }
                    }
                });
            });

        });
    </script>


    
@endpush
