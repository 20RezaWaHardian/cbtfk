@extends('layouts.app')

@section('title', 'Manajement Menus')

@push('style')
    <!-- CSS Libraries -->
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Data User</h4>
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
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog modal-lg" role="document">

        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalLogin">
        <div class="modal-dialog modal-lg" role="document">

        </div>
    </div>
@endpush

@push('scripts')
    <!-- js for this page only -->
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{ $dataTable->scripts() }}


    <script>
        // var table = $('#user-siakad-table').Datatable();

        $('#usersiab-table').on('click', '.action', function() {
            let data = $(this).data()
            let id = $(this).attr('data-id')
            let jenis = data.jenis

            if (jenis == 'edit') {
                $.ajax({
                    method: 'get',
                    url: `/konfigurasi/users/` + id + `/edit`,
                    success: function(res) {
                        $('#modalAction').find('.modal-dialog').html(res)
                        $('#modalAction').modal('show')

                    }
                })
            }

            if (jenis == 'login-as') {
                Swal.fire({
                    title: 'Apakah Anda Yakin ?',
                    text: "Meninggalkan Halaman Ini!",
                    icon: 'info',
                    showCancelButton: true,
                    cancelButtonColor: '#d33',
                    confirmButtonText: '<i class="fa-solid fa-right-to-bracket"></i> Login',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'POST',
                            dataType: 'json',
                            url: `/konfigurasi/users/` + encodeURIComponent(id) + `/login`,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                Swal.fire(
                                    'Login!',
                                    'Anda Berhasil Login.',
                                    'success'
                                )
                                window.location.href = res.redirect;
                            },
                            error: function(res) {
                                Swal.fire(
                                    'Login!',
                                    'Anda Tidak Dapat Login.',
                                    'error'
                                )
                            }
                        })

                    }
                })
                return
            }

        })
    </script>
@endpush
