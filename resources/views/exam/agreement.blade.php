@extends('layouts.exam.app')

@section('title', 'Ujian - Peserta Ujian')

@push('style')

@endpush

@section('contents')
    <div class="card bg-info-subtle position-relative overflow-hidden mx-auto text-center"
        style="max-width: 300px;  z-index:2">
        <div class="card-body px-4 py-3">
            <div class="row">
                <div class="col-12 align-items-center">
                    <h5 class="fw-semibold">Pernyataan</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card position-relative overflow-hidden mb-6" style="margin-top: -60px; ">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12 mt-2">
                    <p class="fw-semibold mt-2">Berikut pernyataan persetujuan:</p>
                    <ul class="pl-3">
                        <li> 1.  Ujian dapat di mulai ketika telah memasuki <b>waktu mulai ujian.</b></li>
                        <li> 2. <b>Kamera</b> peserta akan selalu aktif selama pengerjaan ujian.</li>
                        <li> 3. Tidak diperkenankan keluar dari <b>aplikasi</b> jika ujian belum diselesaikan.</li>
                        <li> 4. Tidak diperkenankan membuka tab baru.</li>
                        <li> 5.<b>Jika keluar dari sistem, Jawaban akan tersimpan, dan ujian tidak bisa diulangi</b> </li>
                      </ul>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="agreementCheckbox">
                        <label class="form-check-label" for="agreementCheckbox">
                            Saya setuju dengan pernyataan di atas
                        </label>
                    </div>
                    <button class="btn btn-info mt-2"  id="submitButton"  style="display:none">Mulai Ujian</button>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('modal')
@endpush

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('agreementCheckbox').addEventListener('change', function() {
            var submitButton = document.getElementById('submitButton');
            if (this.checked) {
                submitButton.style.display = 'block';
            } else {
                submitButton.style.display = 'none';
            }

            submitButton.addEventListener("click", function() {
                var ujianId = '{{ encrypt($ujian->id_ujian) }}';
                var pesertaId = '{{ encrypt($peserta->id_peserta_ujian) }}';

                event.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Anda yakin ingin mensubmit dan memulai ujian?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Mulai Ujian'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/pre-exam24/confirm/' + ujianId + '/' + pesertaId,
                            type: 'GET',
                            success: function(response) {
                                Swal.fire({
                                    title: 'Success',
                                    text: response.message,
                                    icon: 'success',
                                }).then(() => {
                                    window.location.href = response.redirect;

                                });
                            },
                            error: function(response) {

                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });

            });
        });

    </script>
@endpush
