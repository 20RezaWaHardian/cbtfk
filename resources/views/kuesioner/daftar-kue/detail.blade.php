@extends('layouts.app')

@section('title', 'Kuesioner')

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
                    <table class="table">
                        <tr>
                            <td width="200px" style="font-weight: bold">Judul</td><td>:</td><td>{{$kue->judul_kuesioner ?? '-'}}</td>
                        </tr>
                        <tr>
                            <td width="200px" style="font-weight: bold">Jumlah Responden</td><td>:</td><td>{{$total_responden}}</td>
                        </tr>
                        <tr>
                            <td width="200px" style="font-weight: bold">
                                <a href="{{route('downloadKuesioner',encrypt($kue->id_kuesioner))}}" class="btn btn-sm btn-primary">Download</a>
                            </td>
                        </tr>
                    </table>
                </div>
                
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th scope="col" width="3px">No</th>
                                <th scope="col">Pertanyaan</th>
                                <th scope="col">Pilihan Jawaban</th>
                                <th scope="col">Jumlah</th>
                                <th scope="col">Persentase</th>
                              </tr>
                            </thead>
                            <tbody>
                                
                                

                                @foreach ($kue->kategori_kuesioner as $item)

                                    <tr>
                                        <td colspan="5" style="font-weight:bold">
                                            {{ App\Helpers\MyHelpers::angkaKeRomawi($loop->iteration) }}
                                            . {{ $item->nama_kategori }}
                                        </td>
                                    </tr>

                                    @foreach ($item->pertanyaan as $p)

                                        @php
                                            $rowspan = count($pil_jwb);

                                            $totalJawaban =
                                                $totalPerPertanyaan[$p->id_pertanyaan_kuesioner] ?? 0;
                                        @endphp

                                        @if($p->jenis_pertanyaan == 'point')

                                            @foreach ($pil_jwb as $pj)

                                                @php
                                                    $jumlah =
                                                        $rekapMap[$p->id_pertanyaan_kuesioner][$pj->id_pilihan_jwb_kue]
                                                        ?? 0;

                                                    $persentase = $totalJawaban > 0
                                                        ? number_format(($jumlah / $totalJawaban) * 100, 0)
                                                        : 0;
                                                @endphp

                                                <tr>

                                                    @if ($loop->first)
                                                        <td rowspan="{{ $rowspan }}">
                                                            {{ $loop->parent->iteration }}
                                                        </td>

                                                        <td rowspan="{{ $rowspan }}">
                                                            {{ $p->pertanyaan }}
                                                        </td>
                                                    @endif

                                                    <td>{{ $pj->nama_pilihan }}</td>
                                                    <td>{{ $jumlah }}</td>
                                                    <td>{{ $persentase }}%</td>

                                                </tr>

                                            @endforeach

                                        @else

                                            @php
                                                $persentaseUmum = $total_responden > 0
                                                    ? number_format(($totalJawaban / $total_responden) * 100, 0)
                                                    : 0;
                                            @endphp

                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $p->pertanyaan }}</td>
                                                <td>Jawaban Terbuka</td>
                                                <td>{{ $totalJawaban }}</td>
                                                <td>{{ $persentaseUmum }}%</td>
                                            </tr>

                                        @endif

                                    @endforeach
                                @endforeach

                            </tbody>
                        </table>
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

    <script>

        $('.tambah-data').on('click', function() {
            $.ajax({
                method: 'get',
                url: '{{ route('kuesioner.create') }}',
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    store();
                }
            });
        });
        $('.edit-data').on('click', function() {
            let data = $(this).data()
            let id = data.id;
            $('#loading-indicator').show();
            $.ajax({
                method: 'get',
                url: `/kuesioner/` + id + `/edit`,
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    $('#loading-indicator').hide();
                    store();
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
