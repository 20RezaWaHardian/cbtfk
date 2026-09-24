<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Data Paket Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ $paket_soal->id_paket_soal ? route('bank-soal.paket-soal.update', $paket_soal->id_paket_soal) : route('bank-soal.paket-soal.store') }}"
        method="POST">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-12">
                    <label for="prodi" class="mb-2"><b>Pilih Jenis Ujian</b> <span style="color: red">*</span></label>
                    <div class="form-group">
                        <select class="form-control" id="id_jenis_ujian" name="id_jenis_ujian" required>
                            <option disabled selected>Pilih Jenis Ujian</option>
                            @foreach ($jenis_ujian as $item)
                                <option value="{{ $item->id_jenis_ujian }}"
                                    {{ $paket_soal->id_jenis_ujian == $item->id_jenis_ujian ? 'selected' : '' }}>
                                    {{ $item->nama_jenis ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="judul">Nama Paket <span style="color: red">*</span></label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="judul"
                            value="{{ $paket_soal->judul }}" name="judul">
                    </div>
                </div>
                {{-- <div class="col-md-2">
                    <div class="form-group">
                        <label for="kkm">KKM</label>
                        <input type="number" min=1 class="form-control mb-2 mr-sm-2" id="kkm"
                            value="{{ $paket_soal->kkm }}" name="kkm">
                    </div>
                </div> --}}
                {{-- <div class="col-md-2">
                    <div class="form-group">
                        <label for="durasi">Durasi <span style="color: red">*</span></label>
                        <input type="time" class="form-control mb-2 mr-sm-2" id="durasi" step="1"
                            value="{{ $paket_soal->durasi }}" name="durasi">
                    </div>
                </div> --}}
                @php
                    $durasiParts = $paket_soal->durasi ? explode(':', $paket_soal->durasi) : [];
                    $durasiJam = old('durasi_jam', isset($durasiParts[0]) ? (int) $durasiParts[0] : 1);
                    $durasiMenit = old('durasi_menit', isset($durasiParts[1]) ? (int) $durasiParts[1] : 30);
                @endphp
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="mb-2"><b>Durasi Soal</b> <span style="color:red">*</span></label>

                        <div class="row g-2">
                            <div class="col-6">
                                <label for="durasi_jam" class="form-label small text-muted mb-1">Jam</label>
                                <div class="input-group">
                                    <input type="number" class="form-control text-center" id="durasi_jam"
                                        min="0" max="23" value="{{ $durasiJam }}" inputmode="numeric"
                                        aria-label="Durasi dalam jam" required>
                                    <span class="input-group-text">Jam</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <label for="durasi_menit" class="form-label small text-muted mb-1">Menit</label>
                                <div class="input-group">
                                    <input type="number" class="form-control text-center" id="durasi_menit"
                                        min="0" max="59" value="{{ $durasiMenit }}" inputmode="numeric"
                                        aria-label="Durasi dalam menit" required>
                                    <span class="input-group-text">Menit</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-1 mt-2" aria-label="Pilihan cepat durasi">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-durasi-cepat" data-jam="0" data-menit="30">30 menit</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-durasi-cepat" data-jam="1" data-menit="0">1 jam</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-durasi-cepat" data-jam="1" data-menit="30">1 jam 30 menit</button>
                            <button type="button" class="btn btn-sm btn-outline-primary btn-durasi-cepat" data-jam="2" data-menit="0">2 jam</button>
                        </div>

                        <small class="text-muted d-block mt-2" id="durasi_preview">
                            Total durasi: 1 jam 30 menit
                        </small>
                        <div class="invalid-feedback d-block" id="durasi_error" style="display:none !important;">
                            Durasi harus lebih dari 0 menit.
                        </div>

                        <input type="hidden" name="durasi" id="durasi" value="{{ sprintf('%02d:%02d:00', $durasiJam, $durasiMenit) }}">
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="deskripsi" class="mb-2"><b>Deskripsi</b> <span style="color: red">*</span></label>
                        <textarea class="summernote" name="deskripsi" style="display: none;">{{ old('deskripsi', $paket_soal->deskripsi) }}</textarea>
                        @error('deskripsi')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="ketentuan" class="mb-2"><b>Ketentuan</b> <span style="color: red">*</span></label>
                        <textarea class="summernote" name="ketentuan" style="display: none;">{{ old('ketentuan', $paket_soal->ketentuan) }}</textarea>
                        @error('ketentuan')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('.summernote').summernote({
            tabsize: 2,
            height: 100
        });

        const $durasiJam = $('#durasi_jam');
        const $durasiMenit = $('#durasi_menit');
        const $durasi = $('#durasi');
        const $durasiPreview = $('#durasi_preview');
        const $durasiError = $('#durasi_error');

        function clampDuration($input) {
            const min = parseInt($input.attr('min'), 10);
            const max = parseInt($input.attr('max'), 10);
            let value = parseInt($input.val(), 10);

            if (isNaN(value) || value < min) {
                value = min;
            }

            if (value > max) {
                value = max;
            }

            $input.val(value);
            return value;
        }

        function padDuration(value) {
            return value.toString().padStart(2, '0');
        }

        function updateDurasi() {
            const jam = clampDuration($durasiJam);
            const menit = clampDuration($durasiMenit);
            const isValid = jam > 0 || menit > 0;
            const previewParts = [];

            if (jam > 0) {
                previewParts.push(jam + ' jam');
            }

            if (menit > 0) {
                previewParts.push(menit + ' menit');
            }

            $durasi.val(padDuration(jam) + ':' + padDuration(menit) + ':00');
            $durasiPreview.text('Total durasi: ' + (previewParts.length ? previewParts.join(' ') : '0 menit'));
            $durasiError.attr('style', isValid ? 'display:none !important;' : '');
            $durasiJam.toggleClass('is-invalid', !isValid);
            $durasiMenit.toggleClass('is-invalid', !isValid);

            return isValid;
        }

        $durasiJam.add($durasiMenit).on('input change', updateDurasi);

        $('.btn-durasi-cepat').on('click', function() {
            $durasiJam.val($(this).data('jam'));
            $durasiMenit.val($(this).data('menit'));
            updateDurasi();
        });

        $('#formAction').on('submit', function(event) {
            if (!updateDurasi()) {
                event.preventDefault();
                $durasiJam.focus();
            }
        });

        updateDurasi();
    });
</script>
