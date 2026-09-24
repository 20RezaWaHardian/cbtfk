@extends('layouts.app')

@section('title', 'Bank Soal - Paket Soal')

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
                    <h4 class="fw-semibold mb-8">Daftar Paket Soal</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create bank-soal/paket-soal'))
                        <button type="button" class="btn btn-sm btn-primary tambah-data" style="float:right">Tambah
                            Paket</button>
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
        function toggleBlok(val) {
            if (val == 1) {
                $('#formprodi').show();
            } else {
                $('#formprodi').hide();
            }
        }

        $(document).ready(function () {
            let id_jenis_ujian = $('#id_jenis_ujian').val();
            toggleBlok(id_jenis_ujian);
        });

        $(document).on('change', '#id_jenis_ujian', function () {
            toggleBlok($(this).val());
        });
    </script>

    <script>
        $('.tambah-data').on('click', function() {
            $('#modalAction').find('.loading-overlay').show();
            $.ajax({
                method: 'get',
                url: `{{ url('/bank-soal/paket-soal/create') }}`,
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res)
                    $('#modalAction').modal('show')
                    store()
                }
            })
        })

        function store() {
            $('#formAction').off('submit.store').on('submit.store', function(e) {
                e.preventDefault()

                const _form = this
                const $form = $(_form)
                const formData = new FormData(_form)
                const url = $('#formAction').attr('action')
                const $submitButton = $form.find('button[type="submit"]')
                const $closeButton = $form.find('button[data-bs-dismiss="modal"]')
                const submitText = $submitButton.html()
                const hasDurasiInput = $('#durasi_jam').length && $('#durasi_menit').length
                const jam = parseInt($('#durasi_jam').val(), 10) || 0
                const menit = parseInt($('#durasi_menit').val(), 10) || 0

                if (hasDurasiInput && jam === 0 && menit === 0) {
                    $('#durasi_error').attr('style', '')
                    $('#durasi_jam, #durasi_menit').addClass('is-invalid')
                    $('#durasi_jam').focus()
                    return
                }

                $.ajax({
                    method: 'POST',
                    url: url,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $form.find('.text-danger.text-small').remove()
                        $submitButton.prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menyimpan...'
                        )
                        $closeButton.prop('disabled', true)
                    },
                    success: function(res) {
                        $('#modalAction').modal('hide')
                        $('#paketsoal-table').DataTable().ajax.reload();
                    },
                    error: function(res) {
                        let errors = res.responseJSON?.errors
                        if (errors) {
                            for (const [key, value] of Object.entries(errors)) {
                                $(`[name='${key}']`).parent().append(
                                    `<span class="text-danger text-small"> ${value} </span>`)
                            }
                        }
                    },
                    complete: function() {
                        $submitButton.prop('disabled', false).html(submitText)
                        $closeButton.prop('disabled', false)
                    }
                })
            })
        }

        $('#paketsoal-table').on('click', '.action', function() {
            let data = $(this).data()
            let id = data.id
            let jenis = data.jenis

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
                            url: `/bank-soal/paket-soal/` + id + `/delete`,
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
                                $('#paketsoal-table').DataTable().ajax.reload();
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
                                $('#paketsoal-table').DataTable().ajax.reload();
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
                        $('#paketsoal-table').DataTable().ajax.reload();
                    }
                })

            }

        })

        function updateDurasi() {
            let jam = parseInt($('#durasi_jam').val()) || 0;
            let menit = parseInt($('#durasi_menit').val()) || 0;

            $('#durasi').val(
                String(jam).padStart(2, '0') + ':' +
                String(menit).padStart(2, '0') + ':00'
            );
        }

        $('#durasi_jam, #durasi_menit').on('input', updateDurasi);

        $('form').on('submit', function () {
            updateDurasi();
        });
    </script>
@endpush
