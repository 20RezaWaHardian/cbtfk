@extends('layouts.app')
@section('title', 'Bank Soal - Show Data Soal')
@section('contents')

    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Judul Paket : {{ $paket_soal->judul ?? '' }}</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create bank-soal/paket-soal'))
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('bank-soal.paket-soal.pilihSoal', $paket_soal->id_paket_soal) }}" class="btn btn-sm btn-primary">Tambah Soal</a>
                        {{-- <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#importSoalModal">Import Soal</a> --}}
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
    <div class="row">
        <div style="float:left">
            <a href="{{ route('bank-soal.paket-soal.index') }}" class="btn btn-sm btn-secondary mb-2"> Kembali</a>
            @if(count($paket_soal->soal) > 0)
                <a href="{{ route('bank-soal.paket-soal.validasiPoin',$paket_soal->id_paket_soal) }}" class="btn btn-sm btn-warning mb-2"> Validasi Poin</a>
            @endif
        </div>
        @if (count($soal) >= 1)
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-p">
                        <h5 class="card-title mb-9 fw-semibold">Soal</h5>
                        <div id="soal-content">
                            <!-- Konten soal akan ditampilkan di sini -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-semibold">Pilihan : </h5>
                        <div id="nomor-soal-buttons">

                            @foreach ($soal as $index => $s)
                                @php
                                    $buttonClass = 'btn btn-sm pr-5 pl-5 mr-5 ml-5 mb-2';
                                    if ($s->jenis_soal == 'pilgan' && $s->pertanyaan == '') {
                                        $buttonClass .= ' btn-danger'; // Merah jika jenis soal pilgan dan belum ada pertanyaan
                                    } elseif ($s->jenis_soal == 'essay' && $s->pertanyaan == '') {
                                        $buttonClass .= ' btn-danger'; // Merah jika jenis soal essay dan belum ada pertanyaan
                                    } else {
                                        $buttonClass .= ' btn-primary'; // Warna asli jika sudah ada pertanyaan
                                    }
                                @endphp
                                <button type="button" class="{{ $buttonClass }}" data-soal-id="{{ $s->id_soal }}" onclick="showSoal({{ $s->id_soal }})">
                                    {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <img src="{{ asset('assets/images/profile/user-1.jpg') }}" alt="modernize-img"
                                class="img-fluid mb-4" width="200">
                            <h5 class="fw-semibold fs-5 mb-2">Maaf Soal Tidak Tersedia!</h5>
                            <p class="mb-3 px-xl-5">Silahkan Tambahkan Soal Pada Paket ini.</p>
                            <a href=" {{ route('bank-soal.paket-soal.pilihSoal', $paket_soal->id_paket_soal) }}"
                                class="btn btn-sm btn-primary">Tambah
                                Soal Sekarang</a>
                        </div>
                    </div>
                </div>
            </div>

        @endif
    </div>
@endsection

@push('modal')
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog modal-md" role="document">

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="importSoalModal" tabindex="-1" aria-labelledby="importSoalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importSoalLabel">Import Soal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('bank-soal.paket-soal.importSoal', $paket_soal->id_paket_soal) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="alert alert-primary" role="alert">Format Import Soal (Excel) :
                                <a href="{{ route('bank-soal.paket-soal.downloadFormatSoal') }}"
                                    class="alert-link import-data" style="color:blue">Download</a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="file" class="form-label">Kategori Soal</label>
                           <select name="kategori_soal_id" class="form-control">
                                @foreach ($kategori_soal as $item)
                                    <option value="{{ $item->id_kategori_soal }}">{{ $item->nama_kategori }}</option>
                                @endforeach
                           </select>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">Pilih file untuk diimpor</label>
                            <input class="form-control" type="file" name="file" id="file" required>

                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let soalList = @json($soal);
            let activeSoalId = null;
            const soalContent = document.getElementById('soal-content');

            function soalHasEmptyQuestion(soal) {
                return soal.pertanyaan == null || soal.pertanyaan === '';
            }

            function setSoalButtonColor(button, soal, isActive = false) {
                button.classList.remove('btn-primary', 'btn-danger', 'btn-warning', 'text-dark', 'fw-bold');

                if (isActive) {
                    button.classList.add('btn-warning', 'text-dark', 'fw-bold');
                    return;
                }

                if (soalHasEmptyQuestion(soal)) {
                    button.classList.add('btn-danger');
                } else {
                    button.classList.add('btn-primary');
                }
            }

            function setActiveSoalButton(id) {
                document.querySelectorAll('#nomor-soal-buttons button[data-soal-id]').forEach(button => {
                    const soalId = parseInt(button.dataset.soalId);
                    const soal = soalList.find(item => item.id_soal === soalId);

                    if (soal) {
                        setSoalButtonColor(button, soal, soalId === id);
                    }
                });
            }

            function showSoal(id) {
                const soal = soalList.find(s => s.id_soal === id);
                if (soal) {
                    activeSoalId = id;
                    setActiveSoalButton(id);

                    if (soal.jenis_soal === 'pilgan') {
                        if (soal.pertanyaan) {
                            const pilihan = soal.soal_pilgan;

                            let optionsHtml = '';
                            pilihan.forEach((optionCol, index) => {
                                let option = optionCol;
                                optionsHtml += `
                                            <div>
                                                <label for="option${index + 1}">${option.kode}. ${option.teks}</label>
                                            </div>
                                        `;
                            });

                            soalContent.innerHTML = `
                                <div class="" style="float:right">
                                    <button class="btn btn-sm btn-danger" onclick="hapusPertanyaan('${soal.pivot.paket_soal_id}${soal.id_soal}')">Hapus</button>
                                    <form id="delete-form-${soal.pivot.paket_soal_id}${soal.id_soal}" action="/bank-soal/paket-soal/${soal.pivot.paket_soal_id}/soal/${soal.id_soal}/delete" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                                <p>${soal.pertanyaan}</p>
                                ${optionsHtml}
                            `;
                        }
                    } else if (soal.jenis_soal === 'essay') {
                        if (soal.pertanyaan) {
                            const question = soal;
                            soalContent.innerHTML = `
                             <div class="" style="float:right">
                                    <button class="btn btn-sm btn-danger" onclick="hapusPertanyaan('${question.pivot.paket_soal_id}${question.id_soal}')">Hapus</button>
                                    <form id="delete-form-${question.pivot.paket_soal_id}${question.id_soal}" action="/bank-soal/paket-soal/${question.pivot.paket_soal_id}/soal/${question.id_soal}/delete" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                </div>
                            <p>${question.pertanyaan}</p>
                            `;
                        }
                    }
                } else {
                    const soal =
                        soalContent.innerHTML = `
                                <p>Silahkan Pilih Nomor Soal</p>
                            `;
                }
            }

            function store() {
                $('#formAction').on('submit', function(e) {
                    e.preventDefault();
                    const _form = this;
                    const formData = new FormData(_form);
                    const url = $('#formAction').attr('action');
                    $.ajax({
                        method: 'POST',
                        url: url,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            const idPaketSoal = {{ $paket_soal->id_paket_soal }};
                            fetchSoalData(idPaketSoal);
                            $('#modalAction').modal('hide');
                        },
                        error: function(res) {
                            let errors = res.responseJSON?.errors;
                            $(_form).find('.text-danger.text-small').remove();
                            if (errors) {
                                for (const [key, value] of Object.entries(errors)) {
                                    $(`[name='${key}']`).parent().append(
                                        `<span class="text-danger text-small"> ${value} </span>`
                                    );
                                }
                            }
                        }
                    });
                });
            }

            function fetchSoalData(idPaketSoal) {
                $.ajax({
                    url: '/bank-soal/paket-soal/get-soal/' + idPaketSoal,
                    method: 'GET',
                    success: function(res) {
                        soalList = res;
                        reloadSoalChoices();
                        if (soalList.length > 0) {
                            showSoal(soalList[soalList.length - 1].id_soal);
                        }
                    }
                });
            }

            function reloadSoalChoices() {
                const soalChoices = document.getElementById('nomor-soal-buttons');
                if (soalChoices) {
                    soalChoices.innerHTML = '';
                    soalList.forEach((soal, index) => {

                        const button = document.createElement('button');
                        button.type = 'button';
                        button.textContent = index + 1;
                        button.className = 'btn btn-sm';
                        button.dataset.soalId = soal.id_soal;

                        setSoalButtonColor(button, soal, soal.id_soal === activeSoalId);
                        button.style.padding = '5px 7px';
                        button.style.marginRight = '2px';
                        button.style.marginLeft = '2px';
                        button.style.marginBottom = '5px';
                        button.onclick = () => showSoal(soal.id_soal);
                        soalChoices.appendChild(button);
                    });

                } else {
                    console.error("Element with ID 'nomor-soal-buttons' is not found.");
                }
            }




            // load content disini
            if (soalContent) {
                if (soalList.length > 0) {
                    showSoal(soalList[soalList.length - 1].id_soal);
                } else {
                    console.error("Tidak ada pertanyaan yang dapat ditampilkan dari varible soalList.");
                }
            } else {
                console.error("element dengan id soalContent tidak ada bro.");
            }

            window.showSoal = showSoal;
            window.store = store;
        });


        $('.tambah-data').on('click', function() {
            var idPaketSoal = $(this).data('id-paket-soal');
            $.ajax({
                method: 'get',
                url: '{{ route('bank-soal.soal.createJenisSoal', ['id' => ':id']) }}'
                    .replace(':id', idPaketSoal),
                success: function(res) {
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    store();
                }
            });
        });

        function hapusPertanyaan(id) {
            console.log(id);
            if (confirm('Apakah Anda yakin ingin menghapus soal ini?')) {
                const form = document.getElementById(`delete-form-${id}`);
                console.log(form); // This will log null if the form is not found
                if (form) {
                    form.submit();
                } else {
                    console.error(`Form with id delete-form-${id} not found`);
                }
            }
        }
    </script>
@endpush
