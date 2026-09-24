@extends('layouts.app')

@section('title', 'OSCE - Form Penilaian Mahasiswa')

@push('style')
    <!-- CSS Libraries -->
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('summernote/summernote-lite.css') }}" rel="stylesheet">
    <style>
        .badge-danger {
            display: inline-block;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            background-color: #dc3545; /* merah */
            border-radius: 6px;
            line-height: 1;
            white-space: nowrap;
        }
    </style>
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Form Penilaian Mahasiswa OSCE</h4>
                </div>
                

            </div>
        </div>
    </div>

    
    <div class="card bg-warning-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <table class="table table-bordered">
                        <tr>
                            <td>NIM</td><td>:</td><td>{{$mhs_pt->no_mhs}}</td>
                        </tr>
                        <tr>
                            <td>Nama Mahasiswa</td><td>:</td><td>{{$mhs_pt->mahasiswa->nama_mahasiswa}}</td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div>

    

    <div class="row">
        <div class="col-12">
            <div class="card">
                <form action="{{route('data-master.simpanNilaiStase')}}" method="post">
                    @csrf
                    <input type="hidden" name="id_jadwal_osce" value="{{$jadwal->id_jadwal_osce}}">
                    <input type="hidden" name="id_jenis_osce" value="{{$jadwal->id_jenis_osce}}">
                    <input type="hidden" name="id_mhs_pt" value="{{encrypt($id_mhs_pt)}}">
                    <input type="hidden" name="id_peserta_station_osce" value="{{ $pesertaStation->id_peserta_station_osce }}">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead >
                                    <tr>
                                      <th width="2px">#</th>
                                      <th scope="col">Komponen Penilaian</th>
                                      <th scope="col">Penjelasan Nilai</th>
                                      <th scope="col">Bobot</th>
                                      <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @if($komponen->isNotEmpty())
                                    @foreach($komponen as $k)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $k->nama_komponen }}</td>
                                            <td>
                                                @forelse ($k->instrumenNilai as $item)
                                                    <li>( {{ $item->nilai }} ) {{ $item->keterangan }}</li>
                                                @empty
                                                    <span class="badge-danger">Belum Di Input</span>
                                                @endforelse
                                            </td>
                                            <td>{{ $k->bobot_nilai ?? 0 }}</td>
                                            <td>
                                                @forelse ($k->instrumenNilai as $i)
                                                    <input type="radio" 
                                                           id="nilai_{{ $k->id_komponen_nilai_osce }}_{{ $i->nilai }}"
                                                           name="nilai_mhs[{{ $k->id_komponen_nilai_osce }}]" 
                                                           value="{{ $i->nilai }}"
                                                           @if(old('nilai_mhs.'.$k->id_komponen_nilai_osce) !== null)
                                                               {{ old('nilai_mhs.'.$k->id_komponen_nilai_osce) == $i->nilai ? 'checked' : '' }}
                                                           @elseif(isset($k->nilai))
                                                               {{ $k->nilai == $i->nilai ? 'checked' : '' }}
                                                           @endif
                                                    >
                                                    <label for="nilai_{{ $k->id_komponen_nilai_osce }}_{{ $i->nilai }}">{{ $i->nilai }}</label>
                                                @empty
                                                    <span class="badge-danger">Belum Di Input</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @endforeach
                                        {{-- <tr>
                                            <td colspan="3">Total Nilai Stase</td><td>{{$nilaiStase}}</td>
                                        </tr> --}}
                                @else
                                    <tr>
                                        <td colspan="3" style="text-align: center;"> <span class="badge bg-danger">Belum Ada Komponen Penilaian pada State ini</span> </td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Station Berikutnya</label>
                                <select name="id_station_berikutnya" class="form-control">
                                    <option value="">Pilih saat selesai dinilai</option>
                                    @foreach($stationBerikutnya as $s)
                                        <option value="{{ $s->id_jenis_osce }}">{{ $s->nama_jenis_osce }}</option>
                                    @endforeach
                                    <option value="selesai_ujian">Selesai Ujian</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Catatan Perpindahan</label>
                                <input type="text" name="catatan_perpindahan" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                        @if($nilaiStase > 0)
                        <button type="submit" class="btn btn-sm btn-primary" name="tombol" value="edit">Simpan</button>

                        @else
                        <button type="submit" class="btn btn-sm btn-primary" name="tombol" value="create">Simpan</button>
                        @endif
                        <button type="submit" class="btn btn-sm btn-success" name="tombol" value="selesai" onclick="return confirm('Peserta selesai dinilai dan akan dipindahkan sesuai station berikutnya?')">Selesai Dinilai</button>
                    </div>
                </form>
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



    
@endpush
