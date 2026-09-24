@extends('layouts.app')

@section('title', 'Kuesioner')

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
                    <h4 class="fw-semibold mb-8">Daftar Kuesioner</h4>
                </div>
                
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                              <tr>
                                <th scope="col" width="3px">#</th>
                                <th scope="col">Judul Kuesioner</th>
                                <th scope="col" width="100px">Aksi</th>
                              </tr>
                            </thead>
                            <tbody>
                            @forelse ($kuesioner as $k)
                                <tr>
                                    <th scope="row">{{$loop->iteration}}</th>
                                    <td>{{$k->judul_kuesioner}}</td>
                                    <td>
                                        {{-- <a href="{{route('detail-kuesioner',encrypt($k->id_kuesioner))}}" class="btn btn-sm btn-primary">Detail</a> --}}
                                        <a href="{{route('daftar-ujian',encrypt($k->id_kuesioner))}}" class="btn btn-sm btn-primary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">Belum Ada Kuesioner</td>
                                </tr>
                            @endforelse

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
            $.ajax({
                method: 'get',
                url: '{{ route('kuesioner.create') }}',
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    store();
                }
            });
        });
        $('.edit-data').on('click', function() {
            let data = $(this).data()
            let id = data.id;
            $('#loading-indicator').show();
            $.ajax({
                method: 'get',
                url: `/kuesioner/` + id + `/edit`,
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    $('#loading-indicator').hide();
                    store();
                }
            });
        });

        function confirmDelete(event, id) {
            event.preventDefault(); // Mencegah aksi default dari link

            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data ini akan dihapus dan tidak dapat dikembalikan!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-${id}`).submit();
                }
            });
        }

    </script>

@endpush
