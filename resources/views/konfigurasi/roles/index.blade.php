@extends('layouts.app')

@section('title', 'Manajement Roles')

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
                    <h4 class="fw-semibold mb-8">Data Role</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create konfigurasi/roles'))
                        <button type="button" class="btn btn-sm btn-primary tambah-data" style="float:right">Tambah
                            Role</button>
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

                        {{-- {!! $dataTable->table([
                            'class' => 'table table-striped table-bordered text-nowrap align-middle',
                        ]) !!} --}}
                        {{ $dataTable->table() }}

                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('modal')
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog" role="document">

        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalSetPermission">
        <div class="modal-dialog modal-lg  modal-dialog-scrollable" role="document">

        </div>
    </div>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalSetHakAkses">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">

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
        $('.tambah-data').on('click', function() {
            $.ajax({
                method: 'get',
                url: `{{ url('/konfigurasi/roles/create') }}`,
                success: function(res) {
                    // console.log(res)
                    $('#modalAction').find('.modal-dialog').html(res)
                    $('#modalAction').modal('show')
                    store()
                }
            })
        })

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
                        $('#role-table').DataTable().ajax.reload();
                        $('#modalAction').modal('hide')
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

        $('#role-table').on('click', '.action', function() {
            let data = $(this).data()
            let id = data.id
            let jenis = data.jenis

            if (jenis == 'delete') {

                Swal.fire({
                    title: 'Yakin?',
                    text: "Akan Menghapus Data Ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: `/konfigurasi/roles/` + id + `/delete`,
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
                                $('#role-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }


            if (jenis == 'edit') {
                $.ajax({
                    method: 'get',
                    url: `/konfigurasi/roles/` + id + `/edit`,
                    success: function(res) {
                        // console.log(res)
                        $('#modalAction').find('.modal-dialog').html(res)
                        $('#modalAction').modal('show')
                        store()
                    }
                })

            }

            if (jenis == 'set-permission') {
                $.ajax({
                    method: 'get',
                    url: `/konfigurasi/roles/` + id + `/set-permission`,
                    success: function(res) {
                        // console.log(res)
                        $('#modalSetPermission').find('.modal-dialog').html(res)
                        $('#modalSetPermission').modal('show')
                        store()
                    }
                })
            }

            if (jenis == 'set-hak-akses') {
                $.ajax({
                    method: 'get',
                    url: `/konfigurasi/roles/` + id + `/set-hak-akses`,
                    success: function(res) {
                        $('#modalSetHakAkses').find('.modal-dialog').html(res)
                        $('#modalSetHakAkses').modal('show')
                        store()
                    }
                })
            }
        })
    </script>
@endpush
