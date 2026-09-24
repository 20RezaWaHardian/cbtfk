@extends('layouts.app')
@section('title', 'Daftar Soal')

@push('style')
    <style>
        .sub-kategori-card { border: 1px solid #eef2f7; border-radius: 12px; background: #fff; }
        .sub-kategori-card.active { border-color: #0d6efd; box-shadow: 0 8px 18px rgba(13, 110, 253, .12); }
        .soal-empty-state { padding: 1.25rem; border: 1px dashed #cfd8e3; border-radius: 14px; background: #f8fafc; }
        .nomor-soal-btn { min-width: 38px; height: 38px; margin: 0 .35rem .5rem 0; border-radius: 10px; font-weight: 700; }
        .nomor-soal-btn.active-soal { background: #1e3a8a !important; border-color: #1e3a8a !important; color: #fff !important; }
        .soal-view-card { border: 1px solid #eef2f7; border-radius: 14px; background: #fff; }
        .soal-type-badge { display: inline-flex; padding: .35rem .7rem; border-radius: 999px; background: #edf6ff; color: #0d6efd; font-size: .8rem; font-weight: 700; text-transform: uppercase; }
        .soal-option-item { display: flex; gap: .75rem; padding: .85rem 1rem; border: 1px solid #e9eef5; border-radius: 12px; background: #fbfcfe; margin-bottom: .5rem; }
        .soal-option-code { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; background: #eef4ff; color: #3b6ea8; font-weight: 700; }
    </style>
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            @include('components.alert')
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="fw-semibold mb-1">Kategori: {{ $kategori_soal->nama_kategori ?? '' }}</h4>
                    <p class="mb-0 text-muted">Urutan: Kategori ? Sub Kategori ? Soal</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('bank-soal.soal.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Tambah Sub Kategori</h5>
                    @if (auth()->user()->can('create bank-soal/soal'))
                        <form action="{{ route('bank-soal.soal.sub-kategori.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_kategori_soal" value="{{ $kategori_soal->id_kategori_soal }}">
                            <input type="hidden" name="id_kelas" value="{{ $kategori_soal->id_kelas }}">
                            <div class="mb-3">
                                <label class="form-label">Nama Sub Kategori</label>
                                <input type="text" name="nama_kategori" class="form-control @error('nama_kategori') is-invalid @enderror" value="{{ old('nama_kategori') }}" required>
                                @error('nama_kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary">Simpan Sub Kategori</button>
                        </form>
                    @else
                        <p class="text-muted mb-0">Anda tidak memiliki akses membuat sub kategori.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Daftar Sub Kategori</h5>
                    @forelse ($sub_kategori_soal as $subKategori)
                        <div class="sub-kategori-card p-3 mb-3" data-sub-kategori-id="{{ $subKategori->id_sub_kategori_soal }}">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <h6 class="fw-semibold mb-1">{{ $subKategori->nama_kategori }}</h6>
                                    <span class="badge bg-info">{{ $subKategori->soal->count() }} Soal</span>
                                    <span class="badge {{ $subKategori->status ? 'bg-success' : 'bg-secondary' }}">{{ $subKategori->status ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary pilih-sub-kategori" data-sub-kategori-id="{{ $subKategori->id_sub_kategori_soal }}">Lihat</button>
                            </div>
                            <div class="mt-3 d-flex flex-wrap gap-1">
                                @if (auth()->user()->can('create bank-soal/soal'))
                                    <button type="button" class="btn btn-sm btn-primary tambah-data" data-id-kategori-soal="{{ $kategori_soal->id_kategori_soal }}" data-id-sub-kategori-soal="{{ $subKategori->id_sub_kategori_soal }}">Tambah Soal</button>
                                @endif
                                @if (auth()->user()->can('update bank-soal/soal'))
                                    <button type="button" class="btn btn-sm btn-warning edit-sub-kategori" data-id="{{ $subKategori->id_sub_kategori_soal }}" data-nama="{{ $subKategori->nama_kategori }}" data-status="{{ $subKategori->status }}">Edit</button>
                                @endif
                                @if (auth()->user()->can('delete bank-soal/soal'))
                                    <form action="{{ route('bank-soal.soal.sub-kategori.destroy', $subKategori->id_sub_kategori_soal) }}" method="POST" onsubmit="return confirm('Hapus sub kategori ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="soal-empty-state text-center">Belum ada sub kategori. Tambahkan sub kategori dulu.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-semibold mb-1" id="judul-sub-kategori">Pilih Sub Kategori</h5>
                            <p class="text-muted mb-0">Nomor soal tampil sesuai sub kategori.</p>
                        </div>
                        @if (auth()->user()->can('create bank-soal/soal'))
                            <button type="button" class="btn btn-sm btn-success unggah-excel" data-id-kategori-soal="{{ $kategori_soal->id_kategori_soal }}"><i class='fas fa-file-import'></i> Unggah Soal</button>
                        @endif
                    </div>
                    <div id="nomor-soal-buttons" class="mb-3"></div>
                    <div id="soal-content" class="soal-empty-state text-center">Silahkan pilih sub kategori.</div>
                </div>
            </div>

            @if ($soal_tanpa_sub_kategori->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3">Soal Tanpa Sub Kategori</h5>
                        @foreach ($soal_tanpa_sub_kategori as $s)
                            <button class="btn btn-sm btn-outline-secondary nomor-soal-btn" onclick="showSoal({{ $s->id_soal }})">{{ $loop->iteration }}</button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalEditSubKategori" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditSubKategori" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sub Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_kategori_soal" value="{{ $kategori_soal->id_kategori_soal }}">
                        <input type="hidden" name="id_kelas" value="{{ $kategori_soal->id_kelas }}">
                        <div class="mb-3">
                            <label class="form-label">Nama Sub Kategori</label>
                            <input type="text" class="form-control" id="edit_nama_kategori" name="nama_kategori" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" id="edit_status" name="status">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('modal')
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog modal-md" role="document"></div>
    </div>
@endpush

@push('scripts')
    <script>
        const soalList = @json($soal->values());
        const subKategoriList = @json($sub_kategori_soal->values());
        let activeSubKategoriId = null;

        function showSoal(id) {
            const soal = soalList.find(item => Number(item.id_soal) === Number(id));
            const content = document.getElementById('soal-content');
            if (!soal) {
                content.innerHTML = '<div class="soal-empty-state text-center">Soal tidak ditemukan.</div>';
                return;
            }

            document.querySelectorAll('.nomor-soal-btn').forEach(btn => btn.classList.remove('active-soal'));
            document.querySelectorAll(`.nomor-soal-btn[data-id-soal="${id}"]`).forEach(btn => btn.classList.add('active-soal'));

            let actionHtml = '';
            if (soal.jenis_soal === 'pilgan') {
                actionHtml = soal.pertanyaan
                    ? `<a href="/bank-soal/soal/${soal.id_soal}/pilgan/edit" class="btn btn-sm btn-warning">Edit</a>`
                    : `<a href="/bank-soal/soal/${soal.id_soal}/pilgan/create" class="btn btn-sm btn-primary">Masukkan Pertanyaan</a>`;
            } else {
                actionHtml = soal.pertanyaan
                    ? `<a href="/bank-soal/soal/${soal.id_soal}/essay/edit" class="btn btn-sm btn-warning">Edit</a>`
                    : `<a href="/bank-soal/soal/${soal.id_soal}/essay/create" class="btn btn-sm btn-primary">Masukkan Pertanyaan</a>`;
            }

            const pilihanHtml = soal.jenis_soal === 'pilgan' && soal.soal_pilgan
                ? soal.soal_pilgan.map(pilihan => `<div class="soal-option-item"><span class="soal-option-code">${pilihan.kode}</span><div>${pilihan.teks}</div></div>`).join('')
                : '';

            const kunciHtml = soal.kunci ? `<div class="alert alert-success mt-3"><b>Kunci:</b> ${soal.kunci}</div>` : '';

            content.innerHTML = `
                <div class="soal-view-card p-4">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                        <span class="soal-type-badge">${soal.jenis_soal}</span>
                        <div class="d-flex gap-1">
                            ${actionHtml}
                            <button class="btn btn-sm btn-outline-danger" onclick="hapusSoal(${soal.id_soal})">Hapus</button>
                        </div>
                    </div>
                    <div class="mb-3">${soal.pertanyaan || '<i>Pertanyaan belum ditambahkan.</i>'}</div>
                    ${pilihanHtml}
                    ${kunciHtml}
                    <form id="delete-form-${soal.id_soal}" action="/bank-soal/soal/${soal.id_soal}/kategori-soal/jenis-soal/delete" method="POST" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>`;
        }

        function renderSubKategori(id) {
            activeSubKategoriId = Number(id);
            const subKategori = subKategoriList.find(item => Number(item.id_sub_kategori_soal) === activeSubKategoriId);
            const filteredSoal = soalList.filter(item => Number(item.sub_kategori_soal_id) === activeSubKategoriId);

            document.querySelectorAll('.sub-kategori-card').forEach(card => {
                card.classList.toggle('active', Number(card.dataset.subKategoriId) === activeSubKategoriId);
            });

            document.getElementById('judul-sub-kategori').innerText = subKategori ? subKategori.nama_kategori : 'Sub Kategori';
            document.getElementById('nomor-soal-buttons').innerHTML = filteredSoal.map((soal, index) => {
                const btnClass = soal.pertanyaan ? 'btn-primary' : 'btn-danger';
                return `<button class="btn btn-sm ${btnClass} nomor-soal-btn" data-id-soal="${soal.id_soal}" onclick="showSoal(${soal.id_soal})">${index + 1}</button>`;
            }).join('');

            if (filteredSoal.length > 0) {
                showSoal(filteredSoal[0].id_soal);
            } else {
                document.getElementById('soal-content').innerHTML = '<div class="soal-empty-state text-center">Belum ada soal di sub kategori ini.</div>';
            }
        }

        function hapusSoal(id) {
            if (confirm('Hapus soal ini?')) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        }

        $(document).on('click', '.pilih-sub-kategori', function() {
            renderSubKategori($(this).data('sub-kategori-id'));
        });

        $(document).on('click', '.edit-sub-kategori', function() {
            const id = $(this).data('id');
            $('#edit_nama_kategori').val($(this).data('nama'));
            $('#edit_status').val($(this).data('status'));
            $('#formEditSubKategori').attr('action', `/bank-soal/soal/sub-kategori/${id}/update`);
            $('#modalEditSubKategori').modal('show');
        });

        $('.tambah-data').on('click', function() {
            const idKategori = $(this).data('id-kategori-soal');
            const idSubKategori = $(this).data('id-sub-kategori-soal') || activeSubKategoriId || '';
            $.ajax({
                method: 'get',
                url: `/bank-soal/soal/${idKategori}/kategori-soal/jenis-soal/create?sub_kategori_soal_id=${idSubKategori}`,
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res)
                    $('#modalAction').modal('show')
                    storeJenisSoal()
                }
            })
        })

        function storeJenisSoal() {
            $('#formAction').off('submit').on('submit', function(e) {
                e.preventDefault()
                const formData = new FormData(this)
                const url = $('#formAction').attr('action')
                $.ajax({
                    method: 'POST',
                    url: url,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() { window.location.reload() },
                    error: function(res) {
                        let errors = res.responseJSON?.errors
                        $('#formAction').find('.text-danger.text-small').remove()
                        if (errors) {
                            for (const [key, value] of Object.entries(errors)) {
                                $(`[name='${key}']`).parent().append(`<span class="text-danger text-small"> ${value} </span>`)
                            }
                        }
                    }
                })
            })
        }

        $('.unggah-excel').on('click', function() {
            const idKategori = $(this).data('id-kategori-soal');
            $.ajax({
                method: 'get',
                url: `/bank-soal/soal/${idKategori}/kategori-soal/jenis-soal/excel-soal`,
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res)
                    $('#modalAction').modal('show')
                }
            })
        })

        if (subKategoriList.length > 0) {
            renderSubKategori(subKategoriList[0].id_sub_kategori_soal);
        }
    </script>
@endpush
