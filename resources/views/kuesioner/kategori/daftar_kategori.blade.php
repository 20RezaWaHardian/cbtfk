@extends('layouts.app')

@section('title', 'Kategori Kuesioner')

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
                    <h4 class="fw-semibold mb-8">Kategori Kuesioner</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create kuesioner'))
                        <button type="button" class="btn btn-sm btn-primary tambah-data-kue"
                            style="float:right"  data-kuesioner-id="{{ $kuesioner->id_kuesioner }}">Tambah</button>
                    @endif
                </div>
                <div class="col-12">
                    <p>{!! $kuesioner->judul_kuesioner !!}</p>
                </div>
            </div>

        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <a href="{{ route('kuesioner.index')  }}" class="btn btn-sm btn-primary">Kembali</a>
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
                                {{-- <th scope="col">Judul Kuesioner</th> --}}
                                <th scope="col">Kategori Kuesioner</th>
                                <th scope="col" width="300px">Aksi</th>
                              </tr>
                            </thead>
                            <tbody>
                            @forelse ($kategori as $k)
                                <tr>
                                    <th scope="row">{{$loop->iteration}}</th>
                                    {{-- <td>{{$k->kuesioner->judul_kuesioner}}</td> --}}
                                    <td>{{$k->nama_kategori}}</td>
                                    <td>
                                        <a href="{{route('kuesioner.pertanyaan-kue.index',$k->id_kategori_kuesioner)}}" class="btn btn-sm btn-primary"><i class="fa fa-plus"></i> Pertanyaan</a>
                                        <button type="button" data-id="{{$k->id_kategori_kuesioner}}" class="btn btn-sm btn-warning edit-data">
                                            <i class="fa fa-pencil"></i> Edit</button>
                                        <a href="#" onclick="confirmDelete(event, '{{ $k->id_kategori_kuesioner }}')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>

                                        <form id="delete-{{ $k->id_kategori_kuesioner }}" action="/kuesioner/{{ $k->id_kategori_kuesioner }}/destroy-kue" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">Belum Ada Kategori Kuesioner</td>
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
<div class="modal fade" tabindex="-1" role="dialog" id="modalActionKategori">
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
        $('.tambah-data-kue').on('click', function() {
            let kuesionerId = $(this).data('kuesioner-id');
            $('#loading-indicator').show();

            $.ajax({
                method: 'get',
                url: `/kuesioner/buat-kategori-kue`,
                data: { kuesionerId: kuesionerId },
                success: function(res) {
                    $('#modalActionKategori').find('.modal-dialog').html(res);
                    $('#modalActionKategori').modal('show');
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
                url: `/kuesioner/` + id + `/edit/kategori-kue`,
                success: function(res) {
                    $('#modalActionKategori').find('.modal-dialog').html(res);
                    $('#modalActionKategori').modal('show');
                    $('#loading-indicator').hide();
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
