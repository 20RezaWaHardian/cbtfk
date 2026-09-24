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
                action="{{ $soal->pertanyaan ? route('bank-soal.soal.updateSoalPilgan', $soal->id_soal) : route('bank-soal.soal.storeSoalPilgan', $soal->id_soal) }}"
                method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pertanyaan" class="mb-2"><b>Pertanyaan</b></label>
                                    <textarea id="summernote" name="pertanyaan" style="display: none;">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                                    @error('pertanyaan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-2">
                                <div class="form-group">
                                    <label for="poin">Poin</label>
                                    <input type="number" class="form-control" min=1 id="poin" name="poin" value="{{ old('poin', $soal->poin) }}" />
                                </div>
                            </div> --}}

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

                        <a href="{{ route('bank-soal.soal.showSoalByKategori', $soal->kategori_soal_id) }}"
                            class="btn btn-secondary">Kategori Soal</a>

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
    {{-- <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                tabsize: 2,
                height: 100,
                callbacks: {
                    onImageUpload: function(files) {
                        uploadImage(files, this);
                    },
                    onMediaDelete: function(target) {
                        deleteImage(target[0].src);
                    }
                }
            });

            // Panggil fungsi untuk menampilkan opsi-opsi yang sudah ada saat edit
            displayExistingOptions();
        });

        function uploadImage(files, editor) {

            for (let i = 0; i < files.length; i++) {

                let data = new FormData();
                data.append("image", files[i]);
                data.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('summernote.upload') }}",
                    method: "POST",
                    data: data,
                    contentType: false,
                    processData: false,
                    success: function(url) {
                        $(editor).summernote('insertImage', url);
                    },
                    error: function(data) {
                        console.log(data);
                    }
                });

            }
        }

        function deleteImage(src) {

            $.ajax({
                url: "{{ route('summernote.delete') }}",
                method: "POST",
                data: {
                    src: src,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    console.log("File deleted");
                }
            });

        }

        function displayExistingOptions() {
        @php
        if ($soal->pertanyaan) {
            foreach ($pilgan as $option) {
                $newKey = $option->kode;
                $teks = addslashes($option->teks); // Escape single quotes in the text
                $kunci = $soal->kunci; // Retrieve option key from PHP variable
        @endphp
                var newKey = '{{ $newKey }}'; // Retrieve option key from PHP variable
                var teks = '{!! addslashes($teks) !!}'; // Retrieve option text from PHP variable
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
                            <textarea class="form-control" style="height:20px" name="teks[]">${teks}</textarea>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeOption(this)">Hapus</button>
                        </div>
                    </div>
                `;
                $('.new-option-row').append(newRowHtml); // Append the new row to the form
        @php
            }
        }
        @endphp
        }

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
    </script> --}}
    <script>
        $(document).ready(function() {

            // Inisialisasi editor pertanyaan utama
            initSummernote('#summernote');

            // Tampilkan pilihan lama (mode edit)
            displayExistingOptions();
        });

        /*
        |--------------------------------------------------------------------------
        | INIT SUMMERNOTE (GLOBAL FUNCTION)
        |--------------------------------------------------------------------------
        */
        function initSummernote(selector) {

            $(selector).summernote({
                tabsize: 2,
                height: 120,
                callbacks: {
                    onImageUpload: function(files) {
                        uploadImage(files, this);
                    },
                    onMediaDelete: function(target) {
                        deleteImage(target[0].src);
                    }
                }
            });

        }

        /*
        |--------------------------------------------------------------------------
        | UPLOAD IMAGE
        |--------------------------------------------------------------------------
        */
        function uploadImage(files, editor) {

            for (let i = 0; i < files.length; i++) {

                // ================================
                // VALIDASI UKURAN FILE (5MB)
                // ================================
                let maxSize = 5 * 1024 * 1024; // 5MB

                if (files[i].size > maxSize) {
                    alert("Ukuran gambar maksimal 5MB");
                    return;
                }

                // ================================
                // VALIDASI TIPE FILE
                // ================================
                let allowedTypes = ["image/jpeg", "image/png", "image/webp"];

                if (!allowedTypes.includes(files[i].type)) {
                    alert("Format gambar harus JPG, PNG, atau WEBP");
                    return;
                }
                let data = new FormData();
                data.append("image", files[i]);
                data.append("_token", "{{ csrf_token() }}");

                $.ajax({
                    url: "{{ route('summernote.upload') }}",
                    method: "POST",
                    data: data,
                    contentType: false,
                    processData: false,
                    success: function(response) {

                        // jika return string langsung
                        if (typeof response === "string") {
                            $(editor).summernote('insertImage', response);
                        }
                        // jika return json
                        else {
                            $(editor).summernote('insertImage', response.url);
                        }

                    },
                    error: function(err) {
                        console.log(err);
                    }
                });

            }

        }

        /*
        |--------------------------------------------------------------------------
        | DELETE IMAGE
        |--------------------------------------------------------------------------
        */
        function deleteImage(src) {

            $.ajax({
                url: "{{ route('summernote.delete') }}",
                method: "POST",
                data: {
                    src: src,
                    _token: "{{ csrf_token() }}"
                }
            });

        }

        /*
        |--------------------------------------------------------------------------
        | DISPLAY EXISTING OPTIONS (EDIT MODE)
        |--------------------------------------------------------------------------
        */
        function displayExistingOptions() {

            @if(isset($soal) && $soal->pertanyaan)

                @foreach($pilgan as $option)

                    var newKey = '{{ $option->kode }}';
                    var teks = `{!! $option->teks !!}`;
                    var kunci = '{{ $soal->kunci }}';
                    var checked = kunci === newKey ? 'checked' : '';

                    var newRowHtml = `
                        <div class="col-md-12 option-row">
                            <div class="form-group">
                                <label class="mb-2 mt-2">
                                    <input type="radio" name="kunci" value="${newKey}" ${checked} />
                                    <b>Pilihan ${newKey}</b>
                                </label>

                                <input type="hidden" name="kode[]" value="${newKey}">

                                <textarea class="form-control summernote" name="teks[]">
                                    ${teks}
                                </textarea>

                                <button type="button"
                                    class="btn btn-sm btn-danger mt-2"
                                    onclick="removeOption(this)">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    `;

                    $('.new-option-row').append(newRowHtml);

                    // init editor yang baru ditambahkan
                    initSummernote('.new-option-row .option-row:last-child .summernote');

                @endforeach

            @endif

        }

        /*
        |--------------------------------------------------------------------------
        | ADD OPTION
        |--------------------------------------------------------------------------
        */
        function addOption() {

            var optionsCount = $('.new-option-row .option-row').length;
            var newKey = String.fromCharCode(65 + optionsCount); // A, B, C...

            var newRowHtml = `
                <div class="col-md-12 option-row">
                    <div class="form-group">
                        <label class="mb-2 mt-2">
                            <input type="radio" name="kunci" value="${newKey}" />
                            <b>Pilihan ${newKey}</b>
                        </label>

                        <input type="hidden" name="kode[]" value="${newKey}">

                        <textarea class="form-control summernote"
                            name="teks[]"></textarea>

                        <button type="button"
                            class="btn btn-sm btn-danger mt-2"
                            onclick="removeOption(this)">
                            Hapus
                        </button>
                    </div>
                </div>
            `;

            $('.new-option-row').append(newRowHtml);

            // init editor baru
            initSummernote('.new-option-row .option-row:last-child .summernote');

        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE OPTION
        |--------------------------------------------------------------------------
        */
        function removeOption(btn) {
            $(btn).closest('.option-row').remove();
        }

    </script>
@endpush
