@extends('layouts.app')

@section('title', 'Form Jadwal OSCE')

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
                <div class="col-8">
                    <h4 class="fw-semibold mb-8">
                        @if ($jadwal_osce->id_jadwal_osce)
                            Edit Ujian
                        @else
                            Tambah Ujian
                        @endif
                    </h4>
                    <p>Silahkan isi form dibawah untuk menambahkan/merubah ujian. </p>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form
                action="{{ $jadwal_osce->id_jadwal_osce ? route('data-master.jadwal-osce.update', $jadwal_osce->id_jadwal_osce) : route('data-master.jadwal-osce.store') }}"
                method="POST">

                @if($jadwal_osce->id_jadwal_osce)
                    @method('put')
                @endif
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">



                            <div class="col-6">
                                <label for="blok" class="mb-2"><b>Pilih Blok</b></label>
                                <div class="form-group">
                                    <select class="form-control select2" id="blok" name="blok_id" style="width:500px">
                                        <option disabled selected>Pilih Blok</option>
                                        @foreach ($blok as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $jadwal_osce->blok_id == $item->id ? 'selected' : '' }} data-nama-blok="{{ $item->nama ?? '' }}">
                                                [ {{ $item->kode ?? '' }} ]
                                                {{ $item->nama ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="id_semester" class="mb-2"><b>Semester</b></label>
                                <div class="form-group">
                                    <select class="form-control" id="id_semester" name="id_semester" >
                                        {{-- @foreach ($semester as $item) --}}
                                            <option value="{{ $semester->id_semester }}"
                                                {{ $jadwal_osce->id_semester == $semester->id_semester ? 'selected' : '' }}>
                                                {{ App\Helpers\MyHelpers::semester($semester->kode) ?? '' }}
                                            </option>
                                        {{-- @endforeach --}}
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="keterangan" class="mb-2"><b>Nama Ujian</b></label>
                                    <input type="text"name="keterangan"
                                        value="{{ old('keterangan', $jadwal_osce->keterangan) }}" class="form-control mb-2">
                                    @error('keterangan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="tanggal_ujian" class="mb-2"><b>Tanggal Ujian</b></label>
                                    <input type="date"name="tanggal_ujian"
                                        value="{{ old('tanggal_ujian', $jadwal_osce->tanggal_ujian) }}" class="form-control mb-2">
                                    @error('tanggal_ujian')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_mulai" class="mb-2"><b>Waktu Mulai</b></label>
                                    <input type="time"name="waktu_mulai"
                                        value="{{ old('waktu_mulai', $jadwal_osce->waktu_mulai) }}" class="form-control mb-2">
                                    @error('waktu_mulai')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_selesai" class="mb-2"><b>Waktu Selesai</b></label>
                                    <input type="time"name="waktu_selesai"
                                        value="{{ old('waktu_selesai', $jadwal_osce->waktu_selesai) }}" class="form-control mb-2">
                                    @error('waktu_selesai')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if(isset($jadwal_osce->id_jadwal_osce))
                            <div class="col-12">
                                <label for="status" class="mb-2"><b>Pilih Status</b></label>
                                <div class="form-group">
                                    <select class="form-control" id="status" name="status">
                                        <option disabled selected>Pilih Status</option>

                                            <option value=1
                                                {{ $jadwal_osce->status == 1 ? 'selected' : '' }}>
                                                Aktif
                                            </option>
                                            <option value=2
                                                {{ $jadwal_osce->status == 2 ? 'selected' : '' }}>
                                                Tidak Aktif
                                            </option>

                                    </select>
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                    <div class="card-footer bg-whitesmoke br">
                        <a href="{{ route('ujian.daftar-ujian.index') }}" class="btn btn-md btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-md btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('modal')
@endpush

@push('scripts')
    <script src="{{ asset('summernote/summernote-lite.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="//cdn.metered.ca/sdk/video/1.4.6/sdk.min.js"></script>
    <script>
        

        $(document).ready(function() {
            $('.select2').select2();
            $('#summernote').summernote({
                tabsize: 2,
                height: 100
            });
            
            function loadPengawas(){
                $.ajax({
                    url: '/get-pengawas',
                    method: 'GET',
                    success: function(response) {
                        var options = '<option disabled selected>Pilih Pengawas</option>';
                        $.each(response, function(index, item) {

                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }

            $("#pengawas_ujian").select2({
                placeholder: "Cari Mahasiswa..",
                ajax: {
                    url: "{{ url('/get-pegawai') }}",
                    dataTyper: "json",
                    data: function(param) {
                        var value = {
                            search: param.term,
                        }
                        return value;
                    },
                    processResults: function(hasil) {

                        return {
                            results: hasil,
                        }
                    }
                }
            });
        });

    
    </script>
    <script>
        var prodiId = $('#prodi').val();
        var semesterId = "{{ $semester->id_semester }}";
        var blokId = "{{ $jadwal_osce->blok_id }}";

        function toggleBlok(val) {
                if (val == 1) {
                    $('.jenis_blok').show();
                } else {
                    $('.jenis_blok').hide();
                    
                }
            }

        $(document).ready(function () {
            let id_jenis_ujian = $('#id_jenis_ujian').val();
            toggleBlok(id_jenis_ujian);
        });

        $(document).on('change', '#id_jenis_ujian', function () {
            toggleBlok($(this).val());
        });
        $(document).ready(function() {
                $('.select2').select2();

                

                if (prodiId) {
                    loadBlok(prodiId, semesterId);
                }

                $('#prodi').change(function() {
                    var prodiId = $(this).val();
                    if (!prodiId) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Silahkan pilih prodi terlebih dahulu!'
                        });

                        $('#blok').prop('disabled', true);
                        return;
                    }

                    $('#blok').val('');
                    loadBlok(prodiId, semesterId);
                    $('#blok').prop('disabled', false);

                });



                function loadBlok(prodiId, semesterId) {
                    $.ajax({
                        url: '/get-blok/' + prodiId + '/semester/' + semesterId,
                        method: 'GET',
                        success: function(response) {
                            var options = '<option disabled selected>Pilih Blok</option>';
                            $.each(response, function(index, item) {
                                options += '<option value="' + item.id_blok + '" data-nama-blok="' + item.nama_blok + '">' +
                                    '[ '+ item.tahun_blok + ' ] ' + item.nama_blok + '</option>';
                            });
                            $('#blok').html(options).prop('disabled', false).val(blokId).trigger('change');

                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }

                $('#blok').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var kelompokBelajarName = selectedOption.data('nama');
                    var jenisBlok = selectedOption.data('jenis-blok');
                    var namaBlok = selectedOption.data('nama-blok');
                    // Populate the `nama_ujian` input field with the selected values
                    $('input[name="nama_ujian"]').val('UJIAN CBT ' + namaBlok );
                });


                $('#blok').change(function() {
                    var selectedOption = $(this).find('option:selected');
                    var idBlok = selectedOption.val();

                    var namaBlok = selectedOption.data('nama-blok'); // Get the selected blok's name
                });
            });
    </script>
@endpush
