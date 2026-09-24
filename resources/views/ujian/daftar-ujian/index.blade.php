@extends('layouts.app')

@section('title', 'Ujian - Daftar Ujian')

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
                    <h4 class="fw-semibold mb-8">Daftar Ujian</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create ujian/daftar-ujian'))
                        <a href="{{ route('ujian.daftar-ujian.create') }}" class="btn btn-sm btn-primary"
                            style="float:right">Tambah
                            Ujian</a>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('modal')
@endpush

@push('scripts')
    <!-- js for this page only -->
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('summernote/summernote-lite.js') }}"></script>
    {{ $dataTable->scripts() }}


    <script>
       {{--  $(document).ready(function() {


            function updateStatus() {
                $('#daftarujian-table').DataTable().ajax.reload(function(json) {
                    var now = new Date();
                    var idUpdate1 = [];
                    var idUpdate2 = [];
                    $.each(json.data, function(index, item) {
                        var examDate = new Date(item.tanggal_ujian);
                        var expiredDate = new Date(item.selesai_ujian);

                        if (examDate < now) {
                            idUpdate1.push(item.id_ujian);
                        }

                        if (expiredDate < now) {
                            idUpdate2.push(item.id_ujian);
                        }
                    });

                    if (idUpdate1.length > 0 || idUpdate2.length > 0) {
                        $.ajax({
                            url: '/ujian/daftar-ujian/update/status-ujian',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id_update1: idUpdate1,
                                id_update2: idUpdate2,
                            },
                            success: function(response) {
                                $('#daftarujian-table').DataTable().ajax.reload();
                            }
                        });
                    } else {
                        $('#daftarujian-table').DataTable().ajax.reload();
                    }
                }, false);

            }

            // Set interval untuk auto-refresh setiap 1 menit (60000 milidetik)
            setInterval(updateStatus, 60000);
        });
 --}}

        $('#daftarujian-table').on('click', '.action', function() {
            let data = $(this).data();
            let id = data.id;
            let jenis = data.jenis;

            if (jenis == 'delete') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/ujian/daftar-ujian/${id}/delete`,
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#modalAction').find('.modal-dialog').html(res);
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                );
                                $('#daftarujian-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {
                                var errorMessage = 'An error occurred while deleting.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage += '<br>' + xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Failed!',
                                    errorMessage,
                                    'error'
                                );
                                $('#daftarujian-table').DataTable().ajax.reload();
                            }
                        });
                    }
                });
            }
        });
    </script>
@endpush
