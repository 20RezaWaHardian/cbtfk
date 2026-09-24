@extends('layouts.app')

@section('title','Nilai OSCE Antrian')

@push('style')
<style>
    .osce-score-row { border: 1px solid #e5eaef; border-radius: 12px; transition: .2s; background: #fff; }
    .osce-score-row.is-saved { border-color: #13deb9; background: #f2fffb; }
    .instrument-option { border: 1px solid #e5eaef; border-radius: 10px; padding: 14px; cursor: pointer; transition: .2s; }
    .instrument-option:hover, .instrument-option.active { border-color: #5d87ff; background: #f4f7ff; }
    .instrument-option input { transform: scale(1.25); }
    .score-value { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; background: #eef3ff; color: #5d87ff; }
    .save-status { min-height: 20px; }
</style>
@endpush

@section('contents')
<div class="card bg-info-subtle shadow-none mb-4">
    <div class="card-body">
        <h4 class="fw-semibold mb-1">Nilai {{ $pesertaStation->nama_mahasiswa }}</h4>
        <p class="mb-0">{{ $pesertaStation->no_mhs }} - {{ $pesertaStation->nama_jenis_osce }}</p>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    $jumlahKomponen = $komponen->filter(fn($item) => $item->instrumenNilai->isNotEmpty())->count();
    $jumlahTerisi = $komponen->filter(fn($item) => $item->instrumenNilai->isNotEmpty() && $item->nilai !== null)->count();
@endphp

<form method="post" action="{{ route('osce-antrian.simpan-nilai') }}">
    @csrf
    <input type="hidden" name="id_peserta_station_osce" value="{{ $pesertaStation->id_peserta_station_osce }}">
    <input type="hidden" name="id_jadwal_osce" value="{{ $pesertaStation->id_jadwal_osce }}">

    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h5 class="mb-1">Progress Penilaian</h5>
                <div class="text-muted"><span id="jumlahTerisi">{{ $jumlahTerisi }}</span>/<span id="jumlahKomponen">{{ $jumlahKomponen }}</span> komponen sudah dinilai</div>
            </div>
            <span class="badge {{ $jumlahTerisi == $jumlahKomponen && $jumlahKomponen > 0 ? 'bg-success' : 'bg-warning' }} fs-3" id="progressBadge">
                {{ $jumlahTerisi == $jumlahKomponen && $jumlahKomponen > 0 ? 'Lengkap' : 'Belum Lengkap' }}
            </span>
        </div>
    </div>

    <div class="d-flex flex-column gap-3 mb-4">
        @forelse($komponen as $k)
            @php
                $selectedNilai = old('nilai_mhs.' . $k->id_komponen_nilai_osce, $k->nilai);
                $selectedInstrumen = $k->instrumenNilai->firstWhere('nilai', $selectedNilai);
                $hasInstrumen = $k->instrumenNilai->isNotEmpty();
            @endphp
            <div class="osce-score-row {{ $selectedNilai !== null ? 'is-saved' : '' }} p-3" id="cardKomponen{{ $k->id_komponen_nilai_osce }}">
                <div class="row align-items-center g-3">
                    <div class="col-lg-5">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="score-value flex-shrink-0">{{ $loop->iteration }}</div>
                            <div>
                                <h5 class="mb-1">{{ $k->nama_komponen ?? $k->komponen_nilai_osce ?? 'Komponen '.$k->id_komponen_nilai_osce }}</h5>
                                <div class="text-muted small">Bobot: {{ $k->bobot_nilai ?? 0 }}</div>
                                <div class="save-status small mt-1" id="statusKomponen{{ $k->id_komponen_nilai_osce }}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="fw-semibold small text-muted mb-1">Nilai dipilih</div>
                        <div id="nilaiText{{ $k->id_komponen_nilai_osce }}">
                            @if($selectedNilai !== null)
                                <span class="score-value me-2">{{ $selectedNilai }}</span><span>{{ $selectedInstrumen->keterangan ?? '-' }}</span>
                            @else
                                <span class="text-muted">Belum ada nilai</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-3 text-lg-end">
                        <span class="badge {{ $selectedNilai !== null ? 'bg-success' : 'bg-secondary' }} me-2 mb-2" id="badgeKomponen{{ $k->id_komponen_nilai_osce }}">
                            {{ $selectedNilai !== null ? 'Tersimpan' : 'Belum dinilai' }}
                        </span>
                        @if($hasInstrumen)
                            <button type="button" class="btn {{ $selectedNilai !== null ? 'btn-outline-primary' : 'btn-primary' }} mb-2" data-bs-toggle="modal" data-bs-target="#modalNilai{{ $k->id_komponen_nilai_osce }}" id="btnKomponen{{ $k->id_komponen_nilai_osce }}">
                                {{ $selectedNilai !== null ? 'Ubah Nilai' : 'Beri Nilai' }}
                            </button>
                        @else
                            <div class="alert alert-warning mb-0 py-2">Instrumen nilai belum diinput.</div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-warning">Belum ada komponen nilai pada station ini.</div>
        @endforelse
    </div>

    <div class="card">
        <div class="card-body">
            <label class="form-label">Station Berikutnya</label>
            <select class="form-control" name="id_station_berikutnya">
                <option value="">-- pilih saat selesai --</option>
                @foreach($stationBerikutnya as $s)
                    <option value="{{ $s->id_jenis_osce }}">{{ $s->nama_jenis_osce }}</option>
                @endforeach
                <option value="selesai_ujian">Selesai Ujian</option>
            </select>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" name="tombol" value="simpan">Kembali/Simpan</button>
                <button class="btn btn-success" name="tombol" value="selesai" onclick="return confirm('Peserta selesai dinilai dan akan dipindahkan sesuai station berikutnya?')">Selesai Dinilai & Pindah</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('modal')
    @foreach($komponen as $k)
        @php $selectedNilai = old('nilai_mhs.' . $k->id_komponen_nilai_osce, $k->nilai); @endphp
        <div class="modal fade" id="modalNilai{{ $k->id_komponen_nilai_osce }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title">{{ $k->nama_komponen ?? $k->komponen_nilai_osce ?? 'Komponen '.$k->id_komponen_nilai_osce }}</h5>
                            <small class="text-muted">Pilih instrumen. Nilai otomatis tersimpan.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @forelse($k->instrumenNilai as $instrumen)
                            <label class="instrument-option d-flex gap-3 mb-3 {{ (string) $selectedNilai === (string) $instrumen->nilai ? 'active' : '' }}" for="nilai_{{ $k->id_komponen_nilai_osce }}_{{ $loop->index }}">
                                <input type="radio"
                                    class="form-check-input nilai-komponen mt-2"
                                    id="nilai_{{ $k->id_komponen_nilai_osce }}_{{ $loop->index }}"
                                    name="nilai_mhs[{{ $k->id_komponen_nilai_osce }}]"
                                    value="{{ $instrumen->nilai }}"
                                    data-id-komponen="{{ $k->id_komponen_nilai_osce }}"
                                    data-nilai="{{ $instrumen->nilai }}"
                                    data-keterangan="{{ $instrumen->keterangan }}"
                                    data-previous="{{ $selectedNilai }}"
                                    {{ (string) $selectedNilai === (string) $instrumen->nilai ? 'checked' : '' }}>
                                <span>
                                    <span class="score-value mb-2">{{ $instrumen->nilai }}</span>
                                    <span class="d-block fw-semibold mt-2">{{ $instrumen->keterangan }}</span>
                                </span>
                            </label>
                        @empty
                            <div class="alert alert-warning mb-0">Instrumen nilai belum diinput.</div>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endpush

@push('scripts')
<script>
    $(function () {
        const saveUrl = "{{ route('osce-antrian.simpan-nilai-komponen') }}";
        const pesertaStationId = "{{ $pesertaStation->id_peserta_station_osce }}";
        const csrfToken = "{{ csrf_token() }}";

        function updateProgress(jumlahTerisi, jumlahKomponen) {
            $('#jumlahTerisi').text(jumlahTerisi);
            $('#jumlahKomponen').text(jumlahKomponen);
            const badge = $('#progressBadge');
            if (parseInt(jumlahTerisi) === parseInt(jumlahKomponen) && parseInt(jumlahKomponen) > 0) {
                badge.removeClass('bg-warning').addClass('bg-success').text('Lengkap');
            } else {
                badge.removeClass('bg-success').addClass('bg-warning').text('Belum Lengkap');
            }
        }

        $('.nilai-komponen').on('change', function () {
            const radio = $(this);
            const idKomponen = radio.data('id-komponen');
            const nilai = radio.data('nilai');
            const keterangan = radio.data('keterangan');
            const status = $('#statusKomponen' + idKomponen);

            $('input[name="nilai_mhs[' + idKomponen + ']"]').prop('disabled', true);
            status.removeClass('text-success text-danger').addClass('text-muted').text('Menyimpan...');

            $.ajax({
                url: saveUrl,
                method: 'POST',
                data: {
                    _token: csrfToken,
                    id_peserta_station_osce: pesertaStationId,
                    id_komponen_nilai_osce: idKomponen,
                    nilai: nilai
                },
                success: function (response) {
                    $('input[name="nilai_mhs[' + idKomponen + ']"]').data('previous', nilai);
                    $('input[name="nilai_mhs[' + idKomponen + ']"]').closest('.instrument-option').removeClass('active');
                    radio.closest('.instrument-option').addClass('active');

                    $('#cardKomponen' + idKomponen).addClass('is-saved');
                    $('#badgeKomponen' + idKomponen).removeClass('bg-secondary bg-danger').addClass('bg-success').text('Tersimpan');
                    $('#nilaiText' + idKomponen).html('<span class="score-value me-2">' + response.nilai + '</span><span>' + (response.keterangan || keterangan || '-') + '</span>');
                    $('#btnKomponen' + idKomponen).removeClass('btn-primary').addClass('btn-outline-primary').text('Ubah Nilai');
                    status.removeClass('text-muted text-danger').addClass('text-success').text('Nilai tersimpan.');
                    updateProgress(response.jumlah_terisi, response.jumlah_komponen);

                    if (typeof alertify !== 'undefined') {
                        alertify.success('Nilai tersimpan');
                    }
                },
                error: function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Nilai gagal tersimpan.';
                    const previous = radio.data('previous');
                    if (previous !== undefined && previous !== '') {
                        $('input[name="nilai_mhs[' + idKomponen + ']"][value="' + previous + '"]').prop('checked', true);
                    } else {
                        radio.prop('checked', false);
                    }
                    $('#badgeKomponen' + idKomponen).removeClass('bg-success bg-secondary').addClass('bg-danger').text('Gagal tersimpan');
                    status.removeClass('text-muted text-success').addClass('text-danger').text(message);
                    if (typeof alertify !== 'undefined') {
                        alertify.error(message);
                    }
                },
                complete: function () {
                    $('input[name="nilai_mhs[' + idKomponen + ']"]').prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush