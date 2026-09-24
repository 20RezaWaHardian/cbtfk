@extends('layouts.app')

@section('title', 'Bank Soal - Daftar Soal')

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
                    <h4 class="fw-semibold mb-8">Daftar Soal</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create bank-soal/soal'))
                        <button type="button" class="btn btn-sm btn-primary tambah-data" style="float:right">Tambah
                            Soal</button>
                    @endif
                </div>

            </div>
        </div>
    </div>
    @can('validasi-soal')
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
               Ada <b>{{ $blm_validasi }}</b> Soal Yang Belum Divalidasi!. <a href="{{ route('bank-soal.soal.daftarSoalBelumDivalidasi') }}">Klik untuk validasi</a>
              </div>
        </div>
    </div>
    @endcan
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-4">
                    <label for="status_validasi" class="form-label">Status Validasi</label>
                    <select id="status_validasi" class="form-select">
                        <option value="">Semua status</option>
                        <option value="0">Belum validasi</option>
                        <option value="1">Diterima</option>
                        <option value="2">Ditolak</option>
                    </select>
                </div>
                <div class="col-4">
                    <label for="kategori_soal" class="form-label">Kategori Soal</label>
                    <select name="kategori_soal" id="kategori_soal" class="form-select">
                        <option value="">Pilih Kategori Soal</option>
                        @foreach ($kategori_soal as $item)
                            <option value="{{ $item->id_kategori_soal }}">{{ $item->nama_kategori}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label for="sub_kategori_soal" class="form-label">Sub Kategori Soal</label>
                    <select name="sub_kategori_soal" id="sub_kategori_soal" class="form-select" disabled>
                        <option value="">Pilih Kategori Soal Terlebih Dahulu</option>
                    </select>
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
        <div class="modal-dialog modal-md" role="document">

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
        $(document).ready(function () {
            const kategoriSoal = @json($kategori_soal);
            const subKategoriSelect = $('#sub_kategori_soal');

            function resetSubKategori() {
                subKategoriSelect.html('<option value="">Pilih Kategori Soal Terlebih Dahulu</option>');
                subKategoriSelect.prop('disabled', true);
            }

            function loadSubKategori(kategoriSoalId) {
                resetSubKategori();

                if (!kategoriSoalId) {
                    return;
                }

                let kategori = kategoriSoal.find(function (item) {
                    return item.id_kategori_soal == kategoriSoalId;
                });

                subKategoriSelect.html('<option value="">Pilih Sub Kategori Soal</option>');

                if (!kategori || !kategori.sub_kategori_soal || kategori.sub_kategori_soal.length === 0) {
                    subKategoriSelect.append('<option value="" disabled>Sub kategori tidak tersedia</option>');
                    subKategoriSelect.prop('disabled', true);
                    return;
                }

                kategori.sub_kategori_soal.forEach(function (subKategori) {
                    subKategoriSelect.append(
                        '<option value="' + subKategori.id_sub_kategori_soal + '">' + subKategori.nama_kategori + '</option>'
                    );
                });

                subKategoriSelect.prop('disabled', false);
            }

            $('#kategori_soal').on('change', function () {
                let kategori_soal = $(this).val();

                loadSubKategori(kategori_soal);

                // Ambil DataTable
                let table = $('#daftarsoal-table').DataTable();

                table.ajax.reload();
            });

            $('#sub_kategori_soal, #status_validasi').on('change', function () {
                let table = $('#daftarsoal-table').DataTable();

                table.ajax.reload();
            });

        });
    </script>
    <script>
        $('.tambah-data').on('click', function() {
            $.ajax({
                method: 'get',
                url: '{{ route('bank-soal.soal.createJenisSoalWithoutKategori') }}',
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    store();
                }
            });
        });

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
                        window.location.href = res.redirect;
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

        $('#daftarsoal-table').on('click', '.action', function() {
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
                            url: `/bank-soal/soal/` + id + `/kategori-soal/jenis-soal/delete`,
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
                                $('#daftarsoal-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {

                                // var errorMessage = 'An error occurred while deleting.';
                                // if (xhr.responseJSON.message) {
                                //     errorMessage += '<br>' + xhr.responseJSON.message;
                                // }
                                // Swal.fire(
                                //     'Failed!',
                                //     errorMessage,
                                //     'error'
                                // );
                                $('#daftarsoal-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }

        })
    </script>
@endpush
