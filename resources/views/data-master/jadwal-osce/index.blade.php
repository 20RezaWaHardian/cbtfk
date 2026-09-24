@extends('layouts.app')

@section('title', 'OSCE - Jadwal OSCE')

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
                    <h4 class="fw-semibold mb-8">Daftar Jadwal OSCE</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create data-master/jadwal-osce'))
                        {{-- <button type="button" class="btn btn-sm btn-primary tambah-data" style="float:right">Tambah</button> --}}
                        <a href="{{ route('data-master.jadwal-osce.create') }}" class="btn btn-sm btn-primary"
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
    {{ $dataTable->scripts() }}


    <script>
        

        function store() {
            $('#formAction').on('submit', function(e) {
                e.preventDefault()
                const _form = this
                const formData = new FormData(_form)
                const url = $('#formAction').attr('action')

                $.ajax({
                    method: 'POST',
                    url: url,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        $('#modalAction').modal('hide')
                        $('#jadwalosce-table').DataTable().ajax.reload();
                    },
                    error: function(res) {
                        let errors = res.responseJSON?.errors
                        $(_form).find('.text-danger.text-small').remove()
                        if (errors) {
                            for (const [key, value] of Object.entries(errors)) {
                                $(`[name='${key}']`).parent().append(
                                    `<span class="text-danger text-small"> ${value} </span>`)
                            }
                        }
                    }

                })
            })
        }

        $('#jadwalosce-table').on('click', '.action', function() {
            let data = $(this).data()
            let id = data.id
            let jenis = data.jenis

            if (jenis == 'tambah-stase') {
                $('#loading-indicator').show();
                $.ajax({
                    method: 'get',
                    url: `/data-master/tambah/stase/jadwal-osce/` + id,
                    success: function(res) {
                        $('#modalAction').find('.modal-dialog').html(res)
                        $('#modalAction').modal('show')
                        $('#loading-indicator').hide()

                    }
                })

                $('#modalAction').on('shown.bs.modal', function () {
                    $('#id_pegawai').select2({
                        placeholder: "Cari Penguji Stase..",
                        dropdownParent: $('#modalAction'),
                        ajax: {
                            url: "{{ url('/get-pegawai') }}",
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
            }

            if (jenis == 'delete-stase') {


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
                            method: 'DELETE',
                            url: `/data-master/hapus/jadwal-stase/` + id,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#modalAction').find('.modal-dialog').html(res)
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                )
                                $('#jadwalosce-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {

                                var errorMessage = 'An error occurred while deleting.';
                                if (xhr.responseJSON.message) {
                                    errorMessage += '<br>' + xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Failed!',
                                    errorMessage,
                                    'error'
                                );
                                $('#jadwalosce-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }

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
                            method: 'DELETE',
                            url: `/data-master/jadwal-osce/` + id ,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                )
                                $('#jadwalosce-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {

                                var errorMessage = 'An error occurred while deleting.';
                                if (xhr.responseJSON.message) {
                                    errorMessage += '<br>' + xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Failed!',
                                    errorMessage,
                                    'error'
                                );
                                $('#jadwalosce-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }


            if (jenis == 'edit') {
                $.ajax({
                    method: 'get',
                    url: `/bank-soal/paket-soal/` + id + `/edit`,
                    success: function(res) {
                        $('#modalAction').find('.modal-dialog').html(res)
                        $('#modalAction').modal('show')
                        store()
                        $('#jadwalosce-table').DataTable().ajax.reload();
                    }
                })

            }

        })
    </script>
@endpush
