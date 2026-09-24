@extends('layouts.app')

@section('title', 'Form Buat Ujian')

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
                        @if ($ujian->id_ujian)
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
                action="{{ $ujian->id_ujian ? route('ujian.daftar-ujian.update', $ujian->id_ujian) : route('ujian.daftar-ujian.store') }}"
                method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <label for="prodi" class="mb-2"><b>Pilih Jenis Ujian</b></label>
                                <div class="form-group">
                                    <select class="form-control" id="id_jenis_ujian" name="id_jenis_ujian">
                                        <option disabled selected>Pilih Jenis Ujian</option>
                                        @foreach ($jenis_ujian as $item)
                                            <option value="{{ $item->id_jenis_ujian }}"
                                                {{ $ujian->id_jenis_ujian == $item->id_jenis_ujian ? 'selected' : '' }}>
                                                {{ $item->nama_jenis ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="card">
                                <div class="row jenis_blok" style="display: none">

                                    {{-- <div class="col-6">
                                        <label for="prodi" class="mb-2"><b>Pilih Prodi</b></label>
                                        <div class="form-group">
                                            <select class="form-control" id="prodi" name="prodi_id">
                                                <option disabled selected>Pilih Prodi</option>
                                                @foreach ($prodi as $item)
                                                    <option value="{{ $item->id_prodi }}"
                                                        {{ $ujian->prodi_id == $item->id_prodi ? 'selected' : '' }}>
                                                        [ {{ $item->fakultas->nama_fakultas ?? '' }} ]
                                                        {{ $item->nama_prodi ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div> --}}


                                    <div class="col-6">
                                        <label for="blok" class="mb-2"><b>Pilih Blok</b></label>
                                        <div class="form-group">
                                            {{-- <select class="form-control select2" id="blok" name="blok_id"
                                                {{ $ujian->semester_id ? '' : 'disabled' }} style="width:500px">
                                                <option disabled selected>Pilih Blok</option>
                                            </select> --}}
                                            <select class="form-control select2" id="blok" name="blok_id" style="width:500px">
                                                <option disabled selected>Pilih Blok</option>
                                                @foreach ($blok as $item)
                                                    <option value="{{ $item->id }}"
                                                        {{ $ujian->blok_id == $item->id ? 'selected' : '' }} data-nama-blok="{{ $item->nama ?? '' }}">
                                                        [ {{ $item->kode ?? '' }} ]
                                                        {{ $item->nama ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="kategori_ujian_id" class="mb-2"><b>Pilih Kategori Ujian</b></label>
                                        <div class="form-group">
                                            <select class="form-control select2" id="kategori_ujian_id" name="kategori_ujian_id">
                                                <option value="">Pilih Kategori Ujian</option>
                                                @foreach ($kategori_ujian as $item)
                                                    <option value="{{ $item->id_kategori_ujian }}"
                                                        {{ $ujian->kategori_ujian_id == $item->id_kategori_ujian ? 'selected' : '' }}>
                                                        {{ $item->nama_kategori ?? '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            

                            {{-- <div class="col-4 mt-2">
                                <label for="kelompok_belajar" class="mb-2"><b>Pilih Kelompok Belajar</b></label>
                                <div class="form-group">
                                    <select class="form-control select2" id="kelompok_belajar" name="kelompok_belajar_id"
                                        disabled>
                                        <option disabled selected>Pilih Kelompok Belajar</option>
                                    </select>
                                    @error('kelompok_belajar_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="nama_ujian" class="mb-2"><b>Nama Ujian</b></label>
                                    <input type="text"name="nama_ujian"
                                        value="{{ old('nama_ujian', $ujian->nama_ujian) }}" class="form-control mb-2">
                                    @error('nama_ujian')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4 mt-2">
                                <label for="paket_soal" class="mb-2"><b>Pilih Paket Soal</b></label>
                                <div class="form-group">
                                    <select class="form-control" id="paket_soal" name="paket_soal_id">
                                        <option disabled selected>Pilih Paket Soal</option>
                                    </select>
                                    @error('paket_soal_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4 mt-2">
                                <label for="tanggal_ujian" class="mb-2"><b>Tgl dan Waktu Mulai Ujian</b></label>
                                <div class="form-group">
                                    <input type="datetime-local" value="{{ old('tanggal_ujian', $ujian->tanggal_ujian) }}"
                                           class="form-control" name="tanggal_ujian" id="tanggal_ujian">
                                    @error('tanggal_ujian')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-4 mt-2">
                                <label for="selesai_ujian" class="mb-2"><b>Tgl dan Waktu Selesai Ujian</b></label>
                                <div class="form-group">
                                    <input type="datetime-local" value="{{ old('selesai_ujian', $ujian->selesai_ujian) }}"
                                           class="form-control" name="selesai_ujian" id="selesai_ujian">
                                    @error('selesai_ujian')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <label for="bentuk_ujian" class="mb-2"><b>Bentuk Ujian</b></label>
                                <div class="form-group">
                                    <select name="bentuk_ujian" class="form-control" id="bentuk_ujian">
                                        <option disabled selected>Pilih ...</option>
                                        <option value=1 {{ $ujian->bentuk_ujian == 1 ? 'selected' : '' }}>Utama</option>
                                        <option value=2 {{ $ujian->bentuk_ujian == 2 ? 'selected' : '' }}>Remedial</option>
                                    </select>
                                    @error('bentuk_ujian')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <label for="is_kuesioner" class="mb-2"><b>Apakah Peserta Wajib Isi Kuesioner Sebelum Ujian ?</b></label>
                                <div class="form-group">
                                    <select name="is_kuesioner_sebelum" class="form-control" id="is_kuesioner_sebelum">
                                        <option disabled selected>Pilih ...</option>
                                        <option value=0 {{ $ujian->is_kuesioner == 0 ? 'selected' : '' }}>Tidak</option>
                                        <option value=1 {{ $ujian->is_kuesioner == 1 ? 'selected' : '' }}>Ya</option>
                                    </select>
                                    @error('is_kuesioner')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Select2 Kuesioner (Disembunyikan) -->
                            <div class="col-12 mt-2" id="select-kuesioner-container-sebelum" style="display: none;">
                                <label for="kuesioner_sebelum_id" class="mb-2"><b>Pilih Kuesioner Sebelum Ujian</b></label>
                                <div class="form-group">
                                    <select name="kuesioner_sebelum_id" id="kuesioner_sebelum_id" class="form-control">
                                        <option disabled selected>Pilih Kuesioner...</option>
                                        @foreach ($kuesioner->where('terap_kue',2) as $kue)
                                            <option value="{{ $kue->id_kuesioner }}"
                                                {{ old('kue_id', $ujian->kuesioner_id) == $kue->id_kuesioner ? 'selected' : '' }}>
                                                {{ $kue->judul_kuesioner }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <label for="is_kuesioner" class="mb-2"><b>Apakah Peserta Wajib Isi Kuesioner Setelah Ujian ?</b></label>
                                <div class="form-group">
                                    <select name="is_kuesioner" class="form-control" id="is_kuesioner">
                                        <option disabled selected>Pilih ...</option>
                                        <option value=0 {{ $ujian->is_kuesioner == 0 ? 'selected' : '' }}>Tidak</option>
                                        <option value=1 {{ $ujian->is_kuesioner == 1 ? 'selected' : '' }}>Ya</option>
                                    </select>
                                    @error('is_kuesioner')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Select2 Kuesioner (Disembunyikan) -->
                            <div class="col-12 mt-2" id="select-kuesioner-container" style="display: none;">
                                <label for="kuesioner_id" class="mb-2"><b>Pilih Kuesioner Setelah Ujians</b></label>
                                <div class="form-group">
                                    <select name="kuesioner_id" id="kuesioner_id" class="form-control">
                                        <option disabled selected>Pilih Kuesioner...</option>
                                        @foreach ($kuesioner->where('terap_kue',1) as $kue)
                                            <option value="{{ $kue->id_kuesioner }}"
                                                {{ old('kue_id', $ujian->kuesioner_id) == $kue->id_kuesioner ? 'selected' : '' }}>
                                                {{ $kue->judul_kuesioner }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 mt-2">
                                <label for="tampil_nilai" class="mb-2"><b>Tampilkan Nilai</b></label>
                                <div class="form-group">
                                    <select name="tampil_nilai" class="form-control" id="tampil_nilai">
                                        <option disabled selected>Pilih Tampilkan NIlai</option>
                                        <option value="1" {{ $ujian->tampil_nilai == 1 ? 'selected' : '' }}>Tampilkan</option>
                                        <option value="2" {{ $ujian->tampil_nilai == 2 ? 'selected' : '' }}>Tidak Tampilkan</option>
                                    </select>
                                    @error('tampil_nilai')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label for="ketentuan_ujian" class="mb-2"><b>Ketentuan Ujian</b></label>
                                    <textarea id="summernote" name="ketentuan_ujian" style="display: none;">{{ old('ketentuan_ujian', $ujian->ketentuan_ujian) }}</textarea>
                                    @error('ketentuan_ujian')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label for="ketentuan_ujian" class="mb-2"><b>Pengawas Ujian</b></label>
                                    <select name="pengawas_ujian[]" style="color: white;" id="pengawas_ujian" required class="form-control" data-placeholder="Cari Dosen Pendamping..." multiple>

                                    </select>
                                    @error('ketentuan_ujian')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if(isset($ujian->id_ujian))
                            <div class="col-12">
                                <label for="status" class="mb-2"><b>Pilih Status</b></label>
                                <div class="form-group">
                                    <select class="form-control" id="status" name="status">
                                        <option disabled selected>Pilih Status</option>

                                            <option value=0
                                                {{ $ujian->status == 0 ? 'selected' : '' }}>
                                                Belum Dimulai
                                            </option>
                                            <option value=1
                                                {{ $ujian->status == 1 ? 'selected' : '' }}>
                                                Mulai
                                            </option>
                                            <option value=2
                                                {{ $ujian->status == 2 ? 'selected' : '' }}>
                                                Selesai
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
        
    </script>
    <script>
         document.getElementById('tanggal_ujian').addEventListener('input', function () {
        const tanggalUjian = new Date(this.value);
        const selesaiUjianInput = document.getElementById('selesai_ujian');

        // Ensure 'tanggal_ujian' is valid
        if (!isNaN(tanggalUjian)) {
            // Set 'selesai_ujian' value and minimum to match 'tanggal_ujian' initially
            selesaiUjianInput.value = this.value;
            selesaiUjianInput.min = this.value;
        }
    });
    var prodiId = $('#prodi').val();
    var semesterId = "{{ $semester->id_semester }}";
    var blokId = "{{ $ujian->blok_id }}";
    var kelompokBelajarId = "{{ $ujian->kelompok_belajar_id }}";
    var paketSoalId = "{{ $ujian->paket_soal_id }}";

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
        loadPaketSoal()
        // loadPaketSoal("eksternal",paketSoalId)
    });

    $(document).on('change', '#id_jenis_ujian', function () {
        toggleBlok($(this).val());
        loadPaketSoal("eksternal",paketSoalId)
    });
    $(document).ready(function() {
            $('.select2').select2();
            $('#summernote').summernote({
                tabsize: 2,
                height: 100
            });

            

            if (prodiId) {
                loadBlok(prodiId, semesterId);
                // loadPaketSoal(prodiId, paketSoalId);
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
                    $('#kelompok_belajar').prop('disabled', true);
                    $('#paket_soal').prop('disabled', true);
                    return;
                }

                $('#blok').val('');
                $('#kelompok_belajar').val('');
                loadBlok(prodiId, semesterId);
                $('#blok').prop('disabled', false);
                $('#paket_soal').prop('disabled', false);
                loadPaketSoal(prodiId,paketSoalId);

            });



            {{-- function loadBlok(prodiId, semesterId) {
                $.ajax({
                    url: '/get-blok/' + prodiId + '/semester/' + semesterId,
                    method: 'GET',
                    success: function(response) {
                        var options = '<option disabled selected>Pilih Blok</option>';
                        $.each(response, function(index, item) {
                            options += '<option value="' + item.id_blok + '" data-nama-blok="' + item.kode_kelas + ' / ' + item.tahun_blok + ' ' + item.nama_blok + '">' +
                                '[ ' + item.kode_kelas + ' / ' + item.tahun_blok + ' ] ' + item.nama_blok + '</option>';
                        });
                        $('#blok').html(options).prop('disabled', false).val(blokId).trigger('change');

                        if (blokId) {
                            loadKelompokBelajar(blokId, kelompokBelajarId);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            } --}}

            // function loadBlok(prodiId, semesterId) {
            //     $.ajax({
            //         url: '/get-blok/' + prodiId + '/semester/' + semesterId,
            //         method: 'GET',
            //         success: function(response) {
            //             var options = '<option disabled selected>Pilih Blok</option>';
            //             $.each(response, function(index, item) {
            //                 options += '<option value="' + item.id_blok + '" data-nama-blok="' + item.nama_blok + '">' +
            //                     '[ '+ item.tahun_blok + ' ] ' + item.nama_blok + '</option>';
            //             });
            //             $('#blok').html(options).prop('disabled', false).val(blokId).trigger('change');

            //             if (blokId) {
            //                 loadKelompokBelajar(blokId, kelompokBelajarId);
            //             }
            //         },
            //         error: function(xhr, status, error) {
            //             console.error(error);
            //         }
            //     });
            // }

            $('#blok').change(function() {
                var selectedOption = $(this).find('option:selected');

                var namaBlok = selectedOption.data('nama-blok');


                $('input[name="nama_ujian"]').val('UJIAN CBT ' + namaBlok);
            });


            // $('#blok').change(function() {
            //     var selectedOption = $(this).find('option:selected');
            //     var idBlok = selectedOption.val();
            //     alert(idBlok);
            //     var namaBlok = selectedOption.data('nama-blok'); // Get the selected blok's name

            //     // loadKelompokBelajar(idBlok, null, namaBlok);
            // });

            function loadKelompokBelajar(idBlok, kelompokBelajarId, namaBlok) {
                $.ajax({
                    url: '/get-kelompok-belajar/' + idBlok,
                    method: 'GET',
                    success: function(response) {

                        var options = '<option disabled selected>Pilih Kelompok Belajar</option>';
                        $.each(response, function(index, item) {
                            options += '<option value="' + item.id_kelompok_belajar + '" data-nama="' + item.nama_kelompok_belajar +
                                    '" data-jenis-blok="' + item.jenis_blok.jenis_blok + '" data-nama-blok="' + namaBlok + '">' +
                                    '[ ' + item.nama_kelompok_belajar + ' ] - ' + item.jenis_blok.jenis_blok + '</option>';
                        });
                        $('#kelompok_belajar').html(options).prop('disabled', false).val(
                            kelompokBelajarId);
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }
            $('#kelompok_belajar').change(function() {
                var selectedOption = $(this).find('option:selected');
                var kelompokBelajarName = selectedOption.data('nama');
                var jenisBlok = selectedOption.data('jenis-blok');
                var namaBlok = selectedOption.data('nama-blok');
                // Populate the `nama_ujian` input field with the selected values
                $('input[name="nama_ujian"]').val('UJIAN CBT ' + namaBlok + ' - '  + kelompokBelajarName + '[ ' + jenisBlok+ ' ]');
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

            let selectedPengawas = @json(
                $ujian->pengawas->map(function($data) {
                    return [
                        'id'   => $data->id_pegawai,
                        'text' => $data->nip . " - " .
                                $data->gelar_depan . " " .
                                ucwords(strtolower($data->nama_pegawai)) . ", " .
                                $data->gelar_belakang
                    ];
                })
            );

            // masukkan option terselect secara manual
            selectedPengawas.forEach(function(item) {
                let option = new Option(item.text, item.id, true, true);
                $("#pengawas_ujian").append(option);
            });

            $("#pengawas_ujian").select2({
                placeholder: "Cari Dosen Pemdamping..",
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

        function loadPaketSoal() {

            $.ajax({
                url: '/get-paket-soal',
                method: 'GET',
                success: function(response) {
                    console.log(response);
                    if (response.length === 0) {
                        alert("Prodi yang dipilih belum memiliki paket soal ujian, silahkan buat paket soal ujian terlebih dahulu.");
                        $('#paket_soal').prop('disabled', true);
                        return;
                    }
                    var options = '<option disabled selected>Pilih Paket Soal</option>';
                    $.each(response, function(index, item) {
                        options += '<option value="' + item.id_paket_soal  + '">' + item.judul + '</option>';
                    });
                    $('#paket_soal').html(options).prop('disabled', false).val(
                        paketSoalId);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        $(document).ready(function() {


            $('#is_kuesioner').on('change', function() {
                if ($(this).val() == 1) {
                    $('#select-kuesioner-container').slideDown();
                } else {
                    $('#select-kuesioner-container').slideUp();
                    $('#kuesioner_id').val(null).trigger('change');
                }
            });

            if ($('#is_kuesioner').val() == 1) {
                $('#select-kuesioner-container').show();
            }

            let selectedKuesioner = "{{ old('kuesioner_id', $ujian->kuesioner_id) }}";
            if (selectedKuesioner) {
                $('#kuesioner_id').val(selectedKuesioner).trigger('change');
            }

            $('#is_kuesioner_sebelum').on('change', function() {
                if ($(this).val() == 1) {
                    $('#select-kuesioner-container-sebelum').slideDown();
                } else {
                    $('#select-kuesioner-container-sebelum').slideUp();
                    $('#kuesioner_sebelum_id').val(null).trigger('change');
                }
            });

            if ($('#is_kuesioner_sebelum').val() == 1) {
                $('#select-kuesioner-container-sebelum').show();
            }

            let selectedKuesionerSebelum = "{{ old('kuesioner_sebelum_id', $ujian->kuesioner_sebelum_id) }}";
            if (selectedKuesionerSebelum) {
                $('#kuesioner_sebelum_id').val(selectedKuesionerSebelum).trigger('change');
            }
        });
    </script>
@endpush
