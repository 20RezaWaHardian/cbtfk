@extends('layouts.app')

@section('title', 'Pertanyaan Kuesioner')

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
                    <h4 class="fw-semibold mb-8">Pertanyaan-Pertanyaan Kuesioner</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create kuesioner'))
                        <button type="button" class="btn btn-sm btn-primary tambah-data-pertanyaan"
                            style="float:right"  data-kategori-kuesioner-id="{{ $kategori->id_kategori_kuesioner }}">Tambah</button>
                    @endif
                </div>
                <div class="col-12">
                    <p>{!! $kategori->nama_kategori !!}</p>
                </div>

            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <a href="{{route('kuesioner.kategori-kue.index',$kategori->kuesioner_id)}}" class="btn btn-sm btn-primary">Kembali</a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @if (session('import_success'))
                <div class="alert alert-success">{{ session('import_success') }}</div>
            @endif
            @if ($errors->has('file_excel'))
                <div class="alert alert-danger" role="alert">
                    <strong>Import gagal. Tidak ada pertanyaan yang disimpan.</strong>
                    <ul class="mb-0">
                        @foreach ($errors->get('file_excel') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @can('create kuesioner')
                <div class="mb-3">
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImportPertanyaan">Import Excel</button>
                    <a href="{{ route('kuesioner.pertanyaan-kue.template') }}" class="btn btn-outline-primary">Download Template</a>
                </div>
            @endcan
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                              <tr>
                                <th scope="col" width="3px">#</th>
                                {{-- <th scope="col">Judul Kuesioner</th> --}}
                                <th scope="col">Pertanyaan</th>
                                <th scope="col" width="300px">Aksi</th>
                              </tr>
                            </thead>
                            <tbody>
                            @forelse ($pertanyaan as $k)
                                <tr>
                                    <th scope="row">{{$loop->iteration}}</th>
                                    <td>{{$k->pertanyaan}}</td>
                                    <td>
                                        <button type="button" data-id="{{$k->id_pertanyaan_kuesioner}}" class="btn btn-sm btn-warning edit-data">
                                            <i class="fa fa-pencil"></i> Edit</button>
                                        <a href="#" onclick="confirmDelete(event, '{{ $k->id_pertanyaan_kuesioner }}')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>

                                        <form id="delete-{{ $k->id_pertanyaan_kuesioner }}" action="/kuesioner/pertanyaan/{{ $k->id_pertanyaan_kuesioner }}/destroy-pertanyaan-kue" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum Ada Pertanyaan Pada Kategori Kuesioner ini</td>
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
@can('create kuesioner')
<div class="modal fade" id="modalImportPertanyaan" tabindex="-1" aria-labelledby="judulImportPertanyaan" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" action="{{ route('kuesioner.pertanyaan-kue.import', $kategori->id_kategori_kuesioner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="judulImportPertanyaan">Import Pertanyaan Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Header baris pertama: <strong>pertanyaan</strong> dan <strong>jenis</strong>. Jenis: <strong>point</strong> atau <strong>terbuka</strong>.</p>
                <p>Hanya sheet pertama dibaca. Baris kosong dilewati. Data ditambahkan ke kategori ini tanpa mengganti pertanyaan lama. Hapus contoh pada template sebelum import.</p>
                <label for="file_excel" class="form-label">File Excel (.xlsx / .xls, maksimal 5 MB)</label>
                <input type="file" id="file_excel" name="file_excel" class="form-control" accept=".xlsx,.xls" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Import</button>
            </div>
        </form>
    </div>
</div>
@endcan
<div class="modal fade" tabindex="-1" role="dialog" id="modalActionPertanyaan">
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
        $('.tambah-data-pertanyaan').on('click', function() {
            let kategoriKuesionerId = $(this).data('kategori-kuesioner-id');
            $('#loading-indicator').show();
            $.ajax({
                method: 'get',
                url: `/kuesioner/buat-pertanyaan-kue`,
                data: { kategoriKuesionerId: kategoriKuesionerId },
                success: function(res) {
                    $('#modalActionPertanyaan').find('.modal-dialog').html(res);
                    $('#modalActionPertanyaan').modal('show');
                    $('#loading-indicator').hide();
                }
            });
        });
        $('.edit-data').on('click', function() {
            let data = $(this).data()
            let id = data.id;
            $('#loading-indicator').show();
            $.ajax({
                method: 'get',
                url: `/kuesioner/` + id + `/edit/pertanyaan-kue`,
                success: function(res) {
                    $('#modalActionPertanyaan').find('.modal-dialog').html(res);
                    $('#modalActionPertanyaan').modal('show');
                    $('#loading-indicator').hide();
                }
            });
        });

        function confirmDelete(event, id) {
            event.preventDefault();
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
