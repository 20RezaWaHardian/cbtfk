@php
    use App\Helpers\MyHelpers;

    $soalPilgan = $soal->where('jenis_soal', 'pilgan')->where('pertanyaan', '!=', '');
    $soalEssay = $soal->where('jenis_soal', 'essay')->where('pertanyaan', '!=', '');
@endphp

@if (count($soal) >= 1 && ($soalPilgan->count() || $soalEssay->count()))
    <style>
        .question-card {
            border: 1px solid #edf2f7;
            border-radius: 14px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, .04);
            margin-bottom: 14px;
            transition: all .2s ease;
        }

        .question-card:hover {
            border-color: #5d87ff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }

        .question-text img,
        .option-list img {
            height: auto;
            max-width: 100%;
        }

        .option-list {
            margin-bottom: 0;
            padding-left: 18px;
        }

        .section-divider {
            align-items: center;
            display: flex;
            gap: 10px;
            margin: 18px 0 12px;
        }

        .section-divider:after {
            background: #e2e8f0;
            content: '';
            flex: 1;
            height: 1px;
        }

        .bulk-action-bar {
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: space-between;
            margin-bottom: 14px;
            padding: 10px 12px;
        }
    </style>

    <div class="bulk-action-bar">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="selectAllSoal" onchange="toggleSelectAllSoal(this)">
            <label class="form-check-label fw-semibold" for="selectAllSoal">Pilih semua soal tampil</label>
        </div>
        <button type="button" class="btn btn-sm btn-primary" id="addSelectedSoalButton" onclick="addSelectedQuestions(this)" disabled>
            <i class="ti ti-plus me-1"></i> Tambahkan Terpilih (<span id="selectedSoalCount">0</span>)
        </button>
    </div>

    @if ($soalPilgan->count())
        <div class="section-divider">
            <span class="badge bg-primary">Pilihan Ganda</span>
            <small class="text-muted">{{ $soalPilgan->count() }} soal tersedia</small>
        </div>

        @foreach ($soalPilgan as $data)
            <div class="card question-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <input class="form-check-input soal-checkbox" type="checkbox" value="{{ $data->id_soal }}" onchange="updateBulkSoalState()">
                            <span class="badge bg-primary">Pilgan</span>
                            <span class="badge bg-light text-dark">Poin: {{ $data->poin ?? 0 }}</span>
                        </div>
                        <span class="badge bg-success">Pembuat tidak tersedia</span>
                    </div>

                    <div class="question-text mb-3">
                        <div class="fw-semibold mb-1">Soal {{ $loop->iteration }}</div>
                        {!! $data->pertanyaan !!}
                    </div>

                    @if ($data->soal_pilgan && $data->soal_pilgan->count())
                        <ol class="option-list mb-3">
                            @foreach ($data->soal_pilgan as $pilgan)
                                <li><span class="fw-semibold">{{ $pilgan->kode }}.</span> {!! $pilgan->teks !!}</li>
                            @endforeach
                        </ol>
                    @endif

                    <div class="text-end">
                        <button type="button" onclick="addQuestion({{ $data->id_soal }}, {{ $paket_soal->id_paket_soal }}, this)"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Tambahkan Soal
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if ($soalEssay->count())
        <div class="section-divider">
            <span class="badge bg-warning text-dark">Essay</span>
            <small class="text-muted">{{ $soalEssay->count() }} soal tersedia</small>
        </div>

        @foreach ($soalEssay as $data)
            <div class="card question-card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <input class="form-check-input soal-checkbox" type="checkbox" value="{{ $data->id_soal }}" onchange="updateBulkSoalState()">
                            <span class="badge bg-warning text-dark">Essay</span>
                            <span class="badge bg-light text-dark">Poin: {{ $data->poin ?? 0 }}</span>
                        </div>
                        <span class="badge bg-success">Pembuat tidak tersedia</span>
                    </div>

                    <div class="question-text mb-3">
                        <div class="fw-semibold mb-1">Soal {{ $loop->iteration }}</div>
                        {!! $data->pertanyaan !!}
                    </div>

                    <div class="text-end">
                        <button type="button" onclick="addQuestion({{ $data->id_soal }}, {{ $paket_soal->id_paket_soal }}, this)"
                            class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Tambahkan Soal
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@else
    <div class="empty-state text-muted">
        <i class="ti ti-notes-off fs-8 d-block mb-2"></i>
        <div class="fw-semibold">Soal tidak tersedia</div>
        <small>Semua soal pada kategori ini sudah masuk paket, belum lengkap, atau belum divalidasi.</small>
    </div>
@endif

<script>
    function getSelectedSoalIds() {
        return $('.soal-checkbox:checked').map(function() {
            return $(this).val()
        }).get()
    }

    function updateBulkSoalState() {
        const selectedCount = getSelectedSoalIds().length
        const totalCount = $('.soal-checkbox').length

        $('#selectedSoalCount').text(selectedCount)
        $('#addSelectedSoalButton').prop('disabled', selectedCount === 0)
        $('#selectAllSoal').prop('checked', totalCount > 0 && selectedCount === totalCount)
    }

    function toggleSelectAllSoal(checkbox) {
        $('.soal-checkbox').prop('checked', $(checkbox).is(':checked'))
        updateBulkSoalState()
    }

    function reloadActiveSubKategoriSoal() {
        const activeSubKategoriId = $('.sub-kategori-link.active').data('sub-kategori-id')
        const activeSubKategoriName = $('.sub-kategori-link.active').find('.fw-semibold').text().trim()
        getDataSoal(activeSubKategoriId, activeSubKategoriName)
    }

    function addSelectedQuestions(button) {
        const idSoal = getSelectedSoalIds()
        const $button = $(button)
        const buttonText = $button.html()

        if (idSoal.length === 0) {
            showToast('warning', 'Pilih soal terlebih dahulu')
            return
        }

        $button.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menambahkan...'
        )

        $.ajax({
            url: "{{ route('bank-soal.paket-soal.storeSoal') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id_soal: idSoal,
                id_paket_soal: {{ $paket_soal->id_paket_soal }}
            },
            success: function(response) {
                showToast('success', response.message || 'Soal berhasil ditambahkan')
                reloadActiveSubKategoriSoal()
            },
            error: function(err) {
                $button.prop('disabled', false).html(buttonText)
                showToast('error', 'Gagal menambahkan soal')
                console.error('Error adding selected questions', err)
            }
        })
    }

    function addQuestion(id_soal, id_paket_soal, button) {
        const $button = $(button)
        const buttonText = $button.html()

        $button.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Menambahkan...'
        )

        $.ajax({
            url: "{{ route('bank-soal.paket-soal.storeSoal') }}",
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id_soal: id_soal,
                id_paket_soal: id_paket_soal
            },
            success: function(response) {
                $button.removeClass('btn-primary').addClass('btn-success').html('<i class="ti ti-check me-1"></i> Ditambahkan')
                showToast('success', response.message || 'Soal berhasil ditambahkan')

                reloadActiveSubKategoriSoal()
            },
            error: function(err) {
                $button.prop('disabled', false).html(buttonText)
                showToast('error', 'Gagal menambahkan soal')
                console.error('Error adding question', err)
            }
        })
    }
</script>