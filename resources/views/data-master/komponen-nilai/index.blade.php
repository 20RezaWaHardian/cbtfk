@extends('layouts.app')

@section('title', 'OSCE - Komponen Nilai')

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
                @include('components.alert')
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Daftar Komponen Nilai OSCE</h4>
                    <p>{{ $jenis->nama_jenis_osce }} — Simpan komponen terlebih dahulu, lalu pilih Kelola Instrumen.</p>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create data-master/komponen-nilai-osce'))
                        {{-- <button type="button" class="btn btn-sm btn-primary tambah-data" data-id="{{$id_jenis_osce}}" style="float:right">Tambah
                            Komponen</button> --}}
                        <a href="{{route('data-master.komponen-nilai-osce.create',$id_jenis_osce)}}" class="btn btn-sm btn-primary">Tambah Komponen</a>
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
    {{ $dataTable->scripts() }}


    <script>
        $('#komponennilaiosce-table').on('click', '.action', function() {
            let data = $(this).data()
            console.log(data)
            let id = data.id
            let jenis = data.jenis
            let id_jenis_osce = data.id_jenis_osce

            if (jenis == 'delete') {

                Swal.fire({
                    title: 'Hapus komponen ini?',
                    text: "Komponen akan dihapus dari daftar aktif.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Hapus', cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: `/data-master/komponen-nilai-osce/` + id,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#modalAction').find('.modal-dialog').html(res)
                                Swal.fire(
                                    'Berhasil',
                                    'Komponen berhasil dihapus.',
                                    'success'
                                )
                                $('#komponennilaiosce-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {

                                var errorMessage = 'Gagal menghapus komponen.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage += '<br>' + xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Gagal',
                                    errorMessage,
                                    'error'
                                );
                                $('#komponennilaiosce-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }


        })
    </script>
@endpush
