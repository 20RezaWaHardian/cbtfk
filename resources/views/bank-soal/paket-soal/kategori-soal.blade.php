@extends('layouts.app')

@section('title', 'Bank Soal - Pilih Soal')

@push('style')
    <style>
        .kategori-list,
        .sub-kategori-list {
            max-height: 520px;
            overflow-y: auto;
        }

        .kategori-link,
        .sub-kategori-link {
            border: 1px solid #edf2f7;
            border-radius: 12px;
            color: #2d3748;
            margin-bottom: 8px;
            padding: 12px 14px;
            text-align: left;
            transition: all .2s ease;
        }

        .kategori-link:hover,
        .kategori-link.active,
        .sub-kategori-link:hover,
        .sub-kategori-link.active {
            background: #e8f3ff;
            border-color: #5d87ff;
            color: #1e4ed8;
        }

        .soal-panel {
            min-height: 360px;
        }

        .empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 36px 18px;
            text-align: center;
        }
    </style>
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <h4 class="fw-semibold mb-2">Pilih Soal</h4>
                    <p class="mb-0 text-muted">
                        Pilih kategori, pilih sub kategori, lalu tambahkan soal ke paket
                        <span class="fw-semibold text-dark">{{ $paket_soal->judul }}</span>.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('bank-soal.paket-soal.showSoalByPaketSoal', encrypt($paket_soal->id_paket_soal)) }}"
                        class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali ke Paket
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-1">Kategori Soal</h5>
                    <small class="text-muted d-block mb-3">{{ count($kategori_soal) }} kategori tersedia</small>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="kategoriSearch" placeholder="Cari kategori...">
                    </div>

                    @if (count($kategori_soal) > 0)
                        <div class="nav flex-column kategori-list" id="kategoriList">
                            @foreach ($kategori_soal as $kategori)
                                <button type="button"
                                    class="nav-link kategori-link {{ $loop->first ? 'active' : '' }}"
                                    id="tab-kategori-{{ $kategori->id_kategori_soal }}"
                                    data-kategori-id="{{ $kategori->id_kategori_soal }}"
                                    data-kategori-name="{{ strtolower($kategori->nama_kategori) }}"
                                    onclick="renderSubKategori({{ $kategori->id_kategori_soal }})">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary rounded-pill">{{ $loop->iteration }}</span>
                                        <span class="fw-semibold">{{ $kategori->nama_kategori }}</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">{{ $kategori->sub_kategori_soal->count() }} sub kategori</small>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state text-muted">
                            <i class="ti ti-folder-off fs-8 d-block mb-2"></i>
                            Kategori soal belum tersedia.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-1">Sub Kategori Soal</h5>
                    <small class="text-muted d-block mb-3" id="selectedKategoriName">Pilih kategori terlebih dahulu.</small>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="subKategoriSearch" placeholder="Cari sub kategori...">
                    </div>

                    <div class="nav flex-column sub-kategori-list" id="subKategoriList">
                        <div class="empty-state text-muted">
                            <i class="ti ti-click fs-8 d-block mb-2"></i>
                            Pilih kategori.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card soal-panel">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1" id="selectedSubKategoriTitle">Daftar Soal Tersedia</h5>
                            <small class="text-muted">Soal yang sudah masuk paket tidak ditampilkan lagi.</small>
                        </div>
                    </div>

                    <div id="loading-indicator" class="empty-state text-muted" style="display:none;">
                        <div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
                        <div class="fw-semibold">Memuat soal...</div>
                        <small>Mohon tunggu sebentar.</small>
                    </div>

                    <div id="soal-content-wrapper">
                        <div class="empty-state text-muted">
                            <i class="ti ti-click fs-8 d-block mb-2"></i>
                            Pilih sub kategori untuk melihat soal.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const kategoriList = @json($kategori_soal->values());
        const paketSoalId = '{{ $paket_soal->id_paket_soal }}';

        function showToast(icon, title) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: title,
                showConfirmButton: false,
                timer: 1800,
                timerProgressBar: true
            })
        }

        function setActiveKategori(kategoriSoalId) {
            $('.kategori-link').removeClass('active')
            $('#tab-kategori-' + kategoriSoalId).addClass('active')
        }

        function setActiveSubKategori(subKategoriSoalId) {
            $('.sub-kategori-link').removeClass('active')
            $('#tab-sub-kategori-' + subKategoriSoalId).addClass('active')
        }

        function escapeHtml(text) {
            return $('<div>').text(text ?? '').html()
        }

        function renderSubKategori(kategoriSoalId) {
            const kategori = kategoriList.find(item => Number(item.id_kategori_soal) === Number(kategoriSoalId))
            setActiveKategori(kategoriSoalId)
            $('#soal-content-wrapper').html(`
                <div class="empty-state text-muted">
                    <i class="ti ti-click fs-8 d-block mb-2"></i>
                    Pilih sub kategori untuk melihat soal.
                </div>
            `).show()

            if (!kategori) {
                $('#selectedKategoriName').text('Kategori tidak ditemukan.')
                $('#subKategoriList').html(`
                    <div class="empty-state text-muted">
                        <i class="ti ti-folder-off fs-8 d-block mb-2"></i>
                        Sub kategori tidak tersedia.
                    </div>
                `)
                return
            }

            $('#selectedKategoriName').text('Kategori: ' + kategori.nama_kategori)

            if (!kategori.sub_kategori_soal || kategori.sub_kategori_soal.length === 0) {
                $('#subKategoriList').html(`
                    <div class="empty-state text-muted">
                        <i class="ti ti-folder-off fs-8 d-block mb-2"></i>
                        Sub kategori tidak tersedia.
                    </div>
                `)
                return
            }

            let html = ''
            kategori.sub_kategori_soal.forEach(function(subKategori, index) {
                const subKategoriNama = escapeHtml(subKategori.nama_kategori)
                const subKategoriNamaLower = escapeHtml(String(subKategori.nama_kategori).toLowerCase())

                html += `
                    <button type="button"
                        class="nav-link sub-kategori-link ${index === 0 ? 'active' : ''}"
                        id="tab-sub-kategori-${subKategori.id_sub_kategori_soal}"
                        data-sub-kategori-id="${subKategori.id_sub_kategori_soal}"
                        data-sub-kategori-title="${subKategoriNama}"
                        data-sub-kategori-name="${subKategoriNamaLower}"
                        onclick="getDataSoal(${subKategori.id_sub_kategori_soal})">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info rounded-pill">${index + 1}</span>
                            <span class="fw-semibold">${subKategoriNama}</span>
                        </div>
                    </button>
                `
            })

            $('#subKategoriList').html(html)
            getDataSoal(kategori.sub_kategori_soal[0].id_sub_kategori_soal, kategori.sub_kategori_soal[0].nama_kategori)
        }

        function getDataSoal(subKategoriSoalId, subKategoriNama = '') {
            setActiveSubKategori(subKategoriSoalId)
            if (!subKategoriNama) {
                subKategoriNama = $('#tab-sub-kategori-' + subKategoriSoalId).data('sub-kategori-title') || ''
            }
            $('#selectedSubKategoriTitle').text(subKategoriNama ? 'Sub Kategori: ' + subKategoriNama : 'Daftar Soal Tersedia')
            $('#soal-content-wrapper').hide().empty()
            $('#loading-indicator').show()

            $.ajax({
                url: '/get-soal/sub-kategori/' + subKategoriSoalId + '/paket-soal/' + paketSoalId,
                method: 'GET',
                success: function(response) {
                    $('#soal-content-wrapper').html(response).show()
                },
                error: function(xhr) {
                    $('#soal-content-wrapper').html(`
                        <div class="empty-state text-danger">
                            <i class="ti ti-alert-triangle fs-8 d-block mb-2"></i>
                            <div class="fw-semibold">Gagal memuat soal.</div>
                            <small>Silakan pilih sub kategori lagi atau muat ulang halaman.</small>
                        </div>
                    `).show()
                    showToast('error', 'Gagal memuat soal')
                    console.error('Error loading soal', xhr)
                },
                complete: function() {
                    $('#loading-indicator').hide()
                }
            })
        }

        $(document).ready(function() {
            $('#kategoriSearch').on('input', function() {
                const keyword = $(this).val().toLowerCase()
                $('.kategori-link').each(function() {
                    const name = $(this).data('kategori-name') || ''
                    $(this).toggle(String(name).includes(keyword))
                })
            })

            $('#subKategoriSearch').on('input', function() {
                const keyword = $(this).val().toLowerCase()
                $('.sub-kategori-link').each(function() {
                    const name = $(this).data('sub-kategori-name') || ''
                    $(this).toggle(String(name).includes(keyword))
                })
            })

            if (kategoriList.length > 0) {
                renderSubKategori(kategoriList[0].id_kategori_soal)
            }
        })
    </script>
@endpush