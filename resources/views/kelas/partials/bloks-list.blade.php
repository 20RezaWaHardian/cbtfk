<div class="row">
        @foreach ($data['kelas'] as $key => $item)
        
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        @if(isset($item->id_blok))
                            <div class="col-8">
                                {{-- {{ $item->id_blok }} --}}
                                <h4 class="fw-semibold mb-8">{{ $item->tahun_blok }}</h4>
                            </div>
                            <div class="col-4">
                                @if (auth()->user()->can('create konfigurasi/menus'))
                                
                                        <a href="{{route('kelas.getBlockDetail',encrypt($item->id_kelas))}}" class="btn btn-sm btn-primary" style="float:right">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                            Detail
                                        </a>
                                    
                                @endif
                            </div>
                        @else
                            <span class="badge bg-warning" style="font-size: 11px">Pastikan Materi Blok Sudah Di Input Pada Siakad Blok</span>
                        @endif
        
                    </div>
                    
                </div>
                <div class="card-body">
                    <div class="col-md-9">
                        <p class=" fw-semibold mb-1">
                            {{ $item->nama_blok }}
                        </p>
                        <p class="mb-0"> <span style="color:brown">[ {{ $item->kode_matakuliah }} ] </span>
                            {{ $item->nama_matakuliah }} / {{ $item->sks_total }} SKS
                        </p>
                        <p class="mb-0" style="color:green"><i> ( {{ $item->nama_kurikulum }} ) </i></p>
                        <p class="mb-0" style="color:purple"> {{ $item->kode_kelas }}  | {{ $item->jumlah_peserta }} MHS
                            @isset($data['kelasmerge'][$item->id_kelas][0])
                               | {{ collect($data['kelasmerge'][$item->id_kelas])->sum() }} Mhs Ulang
                            @endisset
                            
                        </p>
                    </div>
                    
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <hr>
