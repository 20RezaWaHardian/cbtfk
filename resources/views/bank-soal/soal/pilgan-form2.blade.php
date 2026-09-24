@extends('layouts.app')

@section('title', 'Form Soal Pilihan Ganda')

@push('style')
    <!-- CSS Libraries -->
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('summernote/summernote-lite.css') }}" rel="stylesheet">
@endpush

@section('contents')
    @include('bank-soal.soal.review-form')

    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-8">
                    <h4 class="fw-semibold mb-8">Tambah Soal Pilihan Ganda</h4>
                    <p>Silahkan isi form dibawah untuk menambahkan/merubah soal. </p>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form
                action="{{ $soal->pertanyaan ? route('bank-soal.soal.updateSoalPilganWithoutKategori', $soal->id_soal) : route('bank-soal.soal.storeSoalPilganWithoutKategori', $soal->id_soal) }}"
                method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="sub_kategori_soal_id">Sub Kategori Soal</label>
                                    <select class="form-control mb-2" id="sub_kategori_soal_id" name="sub_kategori_soal_id">
                                        <option disabled {{ old('sub_kategori_soal_id') ?: 'selected' }}>Pilih Sub Kategori Soal...</option>
                                        @foreach ($kategori_soal as $kategori)
                                            <optgroup label="{{ $kategori->nama_kategori }}">
                                                @foreach ($kategori->sub_kategori_soal as $subKategori)
                                                    <option value="{{ $subKategori->id_sub_kategori_soal }}" {{ (old('sub_kategori_soal_id') == $subKategori->id_sub_kategori_soal || (isset($soal) && $soal->sub_kategori_soal_id == $subKategori->id_sub_kategori_soal)) ? 'selected' : '' }}>
                                                        {{ $subKategori->nama_kategori }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>

                                    @error('sub_kategori_soal_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-2">
                                <div class="form-group">
                                    <label for="poin">Poin</label>
                                    <input type="number" class="form-control" min=1 id="poin" name="poin" value="{{ old('poin', $soal->poin) }}"  />
                                </div>
                            </div> --}}
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pertanyaan" class="mb-2"><b>Pertanyaan</b></label>
                                    <textarea id="summernote" name="pertanyaan" style="display: none;">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                                    @error('pertanyaan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row new-option-row">
                                @foreach (old('kode', []) as $index => $kode)
                                    @php
                                        $teks = old('teks')[$index] ?? '';
                                    @endphp
                                    <div class="col-md-12 option-row">
                                        <div class="form-group">
                                            <label for="pil_{{ $kode }}" class="mb-2 mt-2">
                                                <input type="radio" name="kunci" value="{{ $kode }}" />
                                                <b>Pilihan {{ $kode }}</b>
                                            </label>
                                            <input type="hidden" name="kode[]" value="{{ $kode }}">
                                            <textarea id="summernote" class="form-control" style="height:20px" name="teks[]">{!! $teks !!}</textarea>
                                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeOption(this)">Hapus</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-success mt-2" style="float:right"
                                    onclick="addOption()">Tambah
                                    Opsi</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-whitesmoke br">

                        <a href="{{ route('bank-soal.soal.daftarSoal') }}"
                            class="btn btn-secondary">Daftar Soal</a>

                        <button type="submit" class="btn btn-primary">Simpan</button>
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
    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                tabsize: 2,
                height: 100
            });

            // Panggil fungsi untuk menampilkan opsi-opsi yang sudah ada saat edit
            displayExistingOptions();
        });

        function displayExistingOptions() {
        @php
        if ($soal->pertanyaan) {
            foreach ($pilgan as $option) {
                $newKey = $option->kode;
                $teks = addslashes($option->teks);
                $kunci = $soal->kunci; // Retrieve option key from PHP variable
        @endphp
                var newKey = '{{ $newKey }}'; // Retrieve option key from PHP variable
                var teks = '{!! $teks !!}'; // Retrieve option text from PHP variable
                var kunci = '{{ $kunci }}'; // Retrieve option key from PHP variable

                var checked = kunci === newKey ? 'checked' : '';

                var newRowHtml = `
                    <div class="col-md-12 option-row">
                        <div class="form-group">
                            <label for="pil_${newKey}" class="mb-2 mt-2">
                                <input type="radio" name="kunci" value="${newKey}" ${checked} />
                                <b>Pilihan ${newKey}</b>
                            </label>
                            <input type="hidden" name="kode[]" value="${newKey}">
                            <textarea class="form-control summernote" style="height:20px" name="teks[]"></textarea>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeOption(this)">Hapus</button>
                        </div>
                    </div>
                `;
                $('.new-option-row').append(newRowHtml); // Append the new row to the form

                $('.new-option-row .option-row:last-child .summernote').summernote({
                    tabsize: 2,
                    height: 100
                }).summernote('code', teks);
        @php
            }
        }
        @endphp
        }

        // function addOption() {
        //     var optionsCount = $('.new-option-row .form-group').length; // Hitung jumlah opsi yang sudah ditambahkan
        //     var newKey = String.fromCharCode('A'.charCodeAt(0) + optionsCount); // Generate huruf berikutnya (A, B, C, ...)
        //     var newRowHtml = `
        //                     <div class="col-md-12 option-row">
        //                         <div class="form-group">
        //                             <label for="pil_${newKey}" class="mb-2 mt-2">
        //                                 <input type="radio" name="kunci" value="${newKey}" />
        //                                 <b>Pilihan ${newKey}</b>
        //                             </label>
        //                             <input type="hidden" name="kode[]" value="${newKey}">
        //                             <textarea class="form-control summernote" style="height:20px" name="teks[]"></textarea>
        //                             <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeOption(this)">Hapus</button>
        //                         </div>
        //                     </div>
        //                     `;
        //     $('.new-option-row').append(newRowHtml); //Tambah Row
        //     $('.summernote').summernote({
        //         tabsize: 2,
        //         height: 100
        //     });
        // }
        function addOption() {
            var optionsCount = $('.new-option-row .option-row').length; // Hitung jumlah opsi pilihan
            var newKey = String.fromCharCode('A'.charCodeAt(0) + optionsCount); // Generate huruf A, B, C, ...
            
            var newRowHtml = `
                <div class="col-md-12 option-row">
                    <div class="form-group">
                        <label for="pil_${newKey}" class="mb-2 mt-2">
                            <input type="radio" name="kunci" value="${newKey}" />
                            <b>Pilihan ${newKey}</b>
                        </label>
                        <input type="hidden" name="kode[]" value="${newKey}">
                        <textarea class="form-control summernote" style="height:20px" name="teks[]"></textarea>
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeOption(this)">Hapus</button>
                    </div>
                </div>
            `;

            $('.new-option-row').append(newRowHtml);

            // Inisialisasi summernote hanya pada textarea yang baru ditambahkan
            $('.new-option-row .option-row:last-child .summernote').summernote({
                tabsize: 2,
                height: 100
            });
        }

        function removeOption(btn) {
            $(btn).closest('.option-row').remove(); // Hapus
        }
    </script>
@endpush
