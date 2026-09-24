@extends('layouts.app')

@section('title', 'Ujian - Analisis Soal')

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
                    <h4 class="fw-semibold mb-8">Analisis Soal Ujian</h4>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            {{-- <div class="card">
                <div class="card-body">
                    <div class="table-responsive">

                    </div>
                </div>
            </div> --}}
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Analisis Tingkat Kesulitan Soal</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <a href="{{ route('ujian.riwayat-ujian.analisisSoalPdf', $id_ujian) }}" class="btn btn-sm btn-danger" target="_blank">
                            Export PDF
                        </a>
                        <a href="{{ route('ujian.riwayat-ujian.analisisSoalExcel', $id_ujian) }}" class="btn btn-sm btn-success">
                            Export Excel
                        </a>
                        <span class="badge bg-light text-dark">Data berdasarkan Grup Atas/Bawah 27%</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle" id="tableAnalisis">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Soal</th>
                                    <th class="text-center">Indeks Kesulitan (P)</th>
                                    <th class="text-center">Kategori</th>
                                    <th class="text-center">Daya Beda (D)</th>
                                    {{-- <th class="text-center">Aksi</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hasilAnalisis as $item)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    {{-- <td class="text-center">#{{ $item['soal_id'] }}</td> --}}
                                    <td class="text-center">{!! $item['soal_id'] !!}</td>
                                    <td class="text-center">
                                        <span class="fw-bold">{{ number_format($item['difficulty'], 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($item['label'] == 'Sulit')
                                            <span class="badge rounded-pill bg-danger">SULIT</span>
                                        @elseif($item['label'] == 'Sedang')
                                            <span class="badge rounded-pill bg-warning text-dark">SEDANG</span>
                                        @else
                                            <span class="badge rounded-pill bg-success">MUDAH</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="{{ $item['discrimination'] < 0.2 ? 'text-danger' : 'text-dark' }}">
                                            {{ number_format($item['discrimination'], 2) }}
                                        </span>
                                    </td>
                                    {{-- <td class="text-center">
                                        <a href="{{ url('soal/'.$item['soal_id']) }}" class="btn btn-sm btn-outline-primary">
                                            Lihat Detail Soal
                                        </a>
                                    </td> --}}
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Data analisis soal belum tersedia.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 p-3 border rounded bg-light">
                        <h6>Keterangan:</h6>
                        <ul class="small mb-0">
                            <li><strong>Difficulty (P):</strong> < 0.30 (Sulit), 0.30 - 0.70 (Sedang), > 0.70 (Mudah).</li>
                            <li><strong>Daya Beda (D):</strong> Nilai ideal adalah > 0.30. Jika di bawah 0.20, soal perlu dievaluasi.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('modal')
@endpush

@push('scripts')
    <!-- js for this page only -->
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('summernote/summernote-lite.js') }}"></script>
   
@endpush
