@extends('layouts.app')

@section('title', 'Bank Soal - Daftar Soal Belum Divalidasi')

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
                    <h4 class="fw-semibold mb-8">Daftar Soal Belum Divalidasi</h4>
                    <p> Silahkan Validasi Soal!</p>
                </div>

            </div>
        </div>
    </div>


    <div class="row">
        <div style="float:left">
            <a href="{{ route('bank-soal.soal.daftarSoal') }}"
                class="btn btn-sm btn-secondary mb-2"> Kembali</a>
        </div>
        <div class="col-12">

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <label for="status_validasi" class="form-label">Status Validasi</label>
                            <select id="status_validasi" class="form-select">
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
                    <div class="row ">
                        <div class="col-12 p-2">
                            <button type="button" class="btn btn-sm btn-primary validasi-soal">Validasi</button>
                            <p style="font-size: 10px; color:red">Untuk melakukan validasi soal, centang satu atau lebih soal terlebih dahulu.</p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


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
                let table = $('#daftarsoalbutuhvalidasi-table').DataTable();

                table.ajax.reload();
            });

            $('#sub_kategori_soal, #status_validasi').on('change', function () {
                let table = $('#daftarsoalbutuhvalidasi-table').DataTable();

                table.ajax.reload();
            });

        });
    </script>
    <script>
    function confirmValidasi(url) {
        if (confirm('Apakah Anda yakin ingin memvalidasi soal ini?')) {

            window.location.href = url;
        } else {
            return false;
        }
    }

    $(document).ready(function () {
        $('.validasi-soal').click(function() {
                var selectedIds = [];
                $('#daftarsoalbutuhvalidasi-table tbody tr').each(function() {
                    if ($(this).find('td:nth-child(2) input[type=checkbox]').prop('checked')) {
                        selectedIds.push($(this).find('td:nth-child(2) input[type=checkbox]')
                            .val());
                    }
                });
                console.log(selectedIds)
                if (selectedIds.length === 0) {
                    alert('Minimal centang satu data!');
                    return;
                }
                $('#selectedIds').val(selectedIds.join(','));
                // Proceed with validation after confirmation
                Swal.fire({
                    title: 'Keputusan validasi',
                    html: `
                        <div class="text-start">
                            <label for="keputusan-validasi" class="form-label">Status</label>
                            <select id="keputusan-validasi" class="form-select">
                                <option value="">Pilih keputusan</option>
                                <option value="1">Diterima</option>
                                <option value="2">Ditolak</option>
                            </select>
                            <div id="komentar-penolakan-form" class="mt-3" hidden>
                                <label for="komentar-penolakan" class="form-label">Komentar penolakan <span class="text-danger">*</span></label>
                                <textarea id="komentar-penolakan" class="form-control" rows="4" maxlength="5000" placeholder="Isi alasan penolakan..."></textarea>
                                <small>Komentar berlaku untuk semua soal terpilih.</small>
                            </div>
                        </div>`,
                    showCancelButton: true,
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    focusConfirm: false,
                    didOpen: () => {
                        const popup = Swal.getPopup();
                        const keputusan = popup.querySelector('#keputusan-validasi');
                        const komentar = popup.querySelector('#komentar-penolakan');
                        keputusan.addEventListener('change', () => {
                            const ditolak = keputusan.value === '2';
                            popup.querySelector('#komentar-penolakan-form').hidden = !ditolak;
                            komentar.required = ditolak;
                            Swal.resetValidationMessage();
                            if (ditolak) komentar.focus();
                        });
                        keputusan.focus();
                    },
                    preConfirm: () => {
                        const popup = Swal.getPopup();
                        const status = popup.querySelector('#keputusan-validasi').value;
                        const komentar = popup.querySelector('#komentar-penolakan').value.trim();
                        if (!status) {
                            Swal.showValidationMessage('Pilih keputusan.');
                            return false;
                        }
                        if (status === '2' && !komentar) {
                            Swal.showValidationMessage('Komentar wajib diisi.');
                            return false;
                        }
                        return {status, komentar: status === '2' ? komentar : null};
                    }
                }).then(result => {
                    if (!result.isConfirmed) return;
                    const {status, komentar} = result.value;
                    $.ajax({
                        url: '{{ route('bank-soal.soal.validasiSoal') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: selectedIds,
                            status_validasi: status,
                            komentar_validasi: komentar
                        },
                        success: function(response) {
                            // Handle success response
                            if(response.status === 200)
                            {
                                alert(response.pesan);
                                $('#daftarsoalbutuhvalidasi-table').DataTable().ajax.reload();
                            }else{
                                alert(response.pesan);
                                $('#daftarsoalbutuhvalidasi-table').DataTable().ajax.reload();
                            }
                        },
                        error: function(error) {
                            // Handle error response
                            Swal.fire('Validasi gagal', error.responseJSON?.message || 'Terjadi kesalahan saat validasi.', 'error');
                        }
                    });
                });
            });
    });

    $('#daftarsoalbutuhvalidasi-table').on('click', '.action', function() {
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
                                $('#daftarsoalbutuhvalidasi-table').DataTable().ajax.reload();
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
                                $('#daftarsoalbutuhvalidasi-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }

        })

    </script>
@endpush
