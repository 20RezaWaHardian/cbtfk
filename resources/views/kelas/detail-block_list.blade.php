@extends('layouts.app')

@section('title', 'Bank Soal - Soal')

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
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Detail Kelas Blok</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class=" fw-semibold mb-1">
                                {{ $kelas->nama_blok }}
                            </p>
                            <p class="mb-0"> <span style="color:brown">[ {{ $kelas->kode_matakuliah }} ] </span>
                                {{ $kelas->nama_matakuliah }} / {{ $kelas->sks_total }} SKS
                            </p>
                            <p class="mb-0" style="color:green"><i> ( {{ $kelas->nama_kurikulum }} ) </i></p>
                            <p class="mb-0" style="color:purple"> {{ $kelas->kode_kelas }}| {{ $kelas->jumlah_peserta }}
                                Mhs
                            </p>
                            <hr>
                            {{-- <p>Koordinator : {{}}</p> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Detail Kelas Blok</h5>
                    <hr>
                    
                    @foreach ($jenisBlok as $item)
                        <div class="card">
                            <div class="card-header">
                                {{$item->jenis_blok ?? '-'}}
                            </div>
                            
                            <div class="card-body">
                            @foreach (App\Helpers\SiakadBlok::kelompok_belajar($id_block,$item->id_jenisblok,$id_kelas) as $dt)
                                    <div class="card">
                                        <div class="card-header">
                                            {{$dt->nama_kelompok_belajar ?? '-'}}
                                            <button type="button" class="btn btn-sm btn-warning mahasiswa" 
                                            data-block-id="{{$dt->id_kelompok_belajar}}"
                                            data-block-kelas="{{$dt->id_kelas}}"
                                            style="float:right; margin-left:6px">
                                                Mahasiswa
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary materi"
                                            data-block-id="{{$id_block}}"
                                            data-block-jenis="{{$item->id_jenisblok}}"
                                            data-block-kelompok="{{$dt->id_kelompok_belajar}}"
                                            style="float:right; margin-left:6px">
                                                Materi dan Dosen
                                            </button>
                                        </div>
                                        <div class="card-body" id="card-body{{$dt->id_kelompok_belajar}}" style="display: none">

                                        </div>
                                        <div class="card-body" id="card-body2{{$dt->id_kelompok_belajar}}" style="display: none">

                                        </div>
                                    </div>
                                @endforeach
                                
                            </div>
                        </div>
                    @endforeach
                    
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- js for this page only -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on('click', '.mahasiswa', function() {
            var blockId = $(this).data('block-id');
            var kelas = $(this).data('block-kelas');
            var targetCardBody = '#card-body' + blockId;

            if ($(targetCardBody).is(':visible')) {
                $(targetCardBody).slideUp(); // Hide the card body with a sliding effect
            } else {
                $('#loading-indicator').show();
                $.ajax({
                    url: '/kelas/get-blocks/mahasiswa/' + blockId,
                    method: 'GET',
                    data:{
                        kelas:kelas
                    },
                    success: function(response) {
                        $(targetCardBody).html(response).slideDown();
                        $('#loading-indicator').hide();
                    },
                    error: function(xhr, status, error) {
                        $('#loading-indicator').hide();
                        console.error(error);
                    }
                });
            }
        });
        
        $(document).on('click', '.materi', function() {
            var blockId = $(this).data('block-id');
            var jenisBlok = $(this).data('block-jenis');
            var kelompok = $(this).data('block-kelompok');
            var targetCardBody = '#card-body2' + kelompok;

            if ($(targetCardBody).is(':visible')) {
                $(targetCardBody).slideUp(); // Hide the card body with a sliding effect
            } else {
                $('#loading-indicator').show();

                $.ajax({
                    url: '/kelas/get-blocks/materi/'+blockId + '/'+ jenisBlok, // Hapus blockId dari URL
                    method: 'GET',
                    data: {
                        kelompok: kelompok,
                    },
                    success: function(response) {
                        $(targetCardBody).html(response).slideDown();
                        $('#loading-indicator').hide();
                    },
                    error: function(xhr, status, error) {
                        $('#loading-indicator').hide();
                        console.error(error);
                    }
                });
            }
        });
    </script>
@endpush
