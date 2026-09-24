@extends('layouts.app')

@section('title', 'Ujian - Peserta Ujian')

@push('style')
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">{{ $ujian->nama_ujian }} </h4>
                    <p>Buka : {{ $ujian->tanggal_ujian }} | Tutup : {{ $ujian->selesai_ujian }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-9 fw-semibold">Pilih Peserta :</h5>
                    @if ($ujian->id_jenis_ujian == 2 && $ujian->status != 2)
                        <div class="border rounded p-3 mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>Import Peserta PMB</strong>
                                <a href="{{ route('ujian.peserta.templateImportPmb') }}" class="btn btn-sm btn-success">
                                    Download Template
                                </a>
                            </div>
                            <form action="{{ route('ujian.peserta.importPmb', $ujian->id_ujian) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="file_import_pmb" class="form-control form-control-sm mb-2" accept=".xlsx,.xls" required>
                                <small class="text-muted d-block mb-2">Kolom wajib: <b>username</b>, <b>nama_peserta</b>. Password dibuat otomatis sistem.</small>
                                <button type="submit" class="btn btn-sm btn-primary w-100">
                                    Import Peserta PMB
                                </button>
                            </form>
                        </div>
                    @endif
                    <div class="table-responsive">
                        @if (count($daftar_mahasiswa) > 0)
                            <form id="form-validasi" action="{{ route('ujian.peserta.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="ujian_id" value="{{ $ujian->id_ujian }}">

                                {{-- 🔍 Input pencarian --}}
                                <div class="mb-2">
                                    <input type="text" id="search-mahasiswa" class="form-control form-control-sm"
                                           placeholder="Cari mahasiswa berdasarkan nama atau NIM...">
                                </div>

                                @if($ujian->id_jenis_ujian == 1)
                                    <ul id="list-mahasiswa">
                                        <li>
                                            <input type="checkbox" id="select-allPeserta">
                                            <label for="select-allPeserta"><b>Pilih Semua Mahasiswa</b></label>
                                        </li>
                                        @foreach ($daftar_mahasiswa as $mhs)
                                            <li class="item-mahasiswa">
                                                <input type="checkbox" class="selected-mahasiswa" name="selected_mahasiswa[]"
                                                    value="{{ $mhs->id_mhs_pt }}">
                                                [ {{ $mhs->no_mhs }} ] {{ ucwords(strtolower($mhs->nama_mahasiswa)) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @elseif($ujian->id_jenis_ujian == 2)
                                    <ul id="list-mahasiswa">
                                        <li>
                                            <input type="checkbox" id="select-allPeserta">
                                            <label for="select-allPeserta"><b>Pilih Semua Peserta</b></label>
                                        </li>
                                        @foreach ($daftar_mahasiswa as $mhs)
                                            <li class="item-mahasiswa">
                                                <input type="checkbox" class="selected-mahasiswa" name="selected_mahasiswa[]"
                                                    value="{{ $mhs->id_user }}">
                                                [ {{ $mhs->username }} ] {{ ucwords(strtolower($mhs->nama_peserta)) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($ujian->status != 2)
                                    <button type="button" id="btn-validasi" class="btn btn-sm btn-primary mt-3" style="float:right">
                                        Validasi Peserta
                                    </button>
                                @endif
                            </form>
                        @else
                            <p>Data Tidak Ditemukan!</p>
                        @endif

                    </div>
                </div>
            </div>
        </div>
        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    @if ($ujian->status !=2 )
                    <button type="button" class="btn btn-sm btn-danger delete-data mr-2" style="float:right">
                        <i class="fas fa-trash"></i>
                        Hapus Data
                    </button>
                    @endif



                    <h5 class="card-title mb-9 fw-semibold">Daftar Peserta </h5>
                    <button class="btn btn-sm btn-primary" id="sync" data-id="{{encrypt($ujian->id_ujian)}}">Sync</button>
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
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{ $dataTable->scripts() }}

    <script>
        document.getElementById("sync").addEventListener("click", function(){
            let id = $(this).data().id 
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Anda yakin ingin Sinkronisasi nilai?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Validasi'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'get',
                        url: `/ujian/peserta/sync/` + id,
                        beforeSend: function() {
                            $('#loading-indicator').show(); // Move this inside beforeSend or at the start
                        },
                        success: function(res) {
                            $('#modalAction').find('.modal-dialog').html(res)
                            $('#loading-indicator').hide()
                            Swal.fire(
                                'Sukses!',
                                res.pesan,
                                'success'
                            )
                            $('#pesertaujian-table').DataTable().ajax.reload();
                        },
                        error: function(xhr, status, error) {
                            $('#loading-indicator').hide()
                            var errorMessage = 'An error occurred while deleting.';
                            if (xhr.responseJSON.message) {
                                errorMessage += '<br>' + xhr.responseJSON.message;
                            }
                            Swal.fire(
                                'Failed!',
                                errorMessage,
                                'error'
                            );
                            $('#pesertaujian-table').DataTable().ajax.reload();
                        }
                    })
                }
            });
        })
    </script>

    <script>
        document.getElementById('search-mahasiswa').addEventListener('keyup', function () {
            let keyword = this.value.toLowerCase();
            let items = document.querySelectorAll('#list-mahasiswa .item-mahasiswa');

            items.forEach(function (item) {
                let text = item.textContent.toLowerCase();
                if (text.includes(keyword)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('.delete-data').click(function() {
                var selectedIds = [];
                $('#pesertaujian-table tbody tr').each(function() {
                    if ($(this).find('td:nth-child(2) input[type=checkbox]').prop('checked')) {
                        selectedIds.push($(this).find('td:nth-child(2) input[type=checkbox]')
                            .val());
                    }
                });
                if (selectedIds.length === 0) {
                    alert('Minimal centang satu data!');
                    return;
                }
                $('#selectedIds').val(selectedIds.join(','));
                // Proceed with deletion after confirmation
                if (confirm('Anda yakin ingin menghapus data yang dipilih?')) {
                    $.ajax({
                        url: '{{ route('ujian.peserta.destroy') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            id: selectedIds
                        },
                        success: function(response) {
                            // Handle success response
                            location.reload(); // Reload halaman
                            alert('Data berhasil dihapus!');
                        },
                        error: function(error) {
                            // Handle error response
                            alert('Terjadi kesalahan saat menghapus data!');
                        }
                    });
                }
            });
            const form = document.getElementById('form-validasi');
            const submitButton = document.getElementById('btn-validasi');

            if (form && submitButton) {
                submitButton.addEventListener('click', function(event) {
                    event.preventDefault();

                    const checkboxes = document.querySelectorAll('.selected-mahasiswa');
                    let atLeastOneChecked = false;

                    checkboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            atLeastOneChecked = true;
                        }
                    });

                    if (atLeastOneChecked) {
                        Swal.fire({
                            title: 'Konfirmasi',
                            text: 'Anda yakin ingin melakukan validasi peserta ujian?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Validasi'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.submit();
                            }
                        });
                    } else {
                        alert('Pilih setidaknya satu mahasiswa untuk melakukan validasi.');
                    }
                });
            }

            const selectAllCheckbox = document.getElementById('select-allPeserta');
            const checkboxes = document.querySelectorAll('.selected-mahasiswa');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });

                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const allChecked = [...checkboxes].every(checkbox => checkbox.checked);
                        selectAllCheckbox.checked = allChecked;
                    });
                });
            }
        });
    </script>
@endpush
