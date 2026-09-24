<div class="col-sm-12 col-lg-8">
    <div class="card mt-1">
        <div class="card-body" id="soalContainer">
            <?php $i = 1; ?>
            @foreach($soal_satuan as $item)
                <div class="container row">
                    <div class="col-12"><h6>Soal No. {{$soal_satuan->perPage()*($soal_satuan->currentPage()-1)+$i}}</h6></div>
                </div>
                <hr>
                <div class="container">
                    <table>
                        @if($item->jenis_soal == "essay")
                            @php
                                $jawabanEssay = $jawaban_essay->where('soal_id', $item->id_soal)->first();
                            @endphp
                            {!! $item->pertanyaan !!}
                            <div class="mt-2">
                                <b> Jawaban : </b>
                                <textarea class="form-control" name="jawab" id="jawaban_essay_{{$item->soal_essay->id_essay}}" rows="3">{{ $jawabanEssay ? $jawabanEssay->jawab : '' }}</textarea>
                                <input type="hidden" id="essay_id_{{$item->soal_essay->id_essay}}" value="{{$item->soal_essay->id_essay }}">
                                <input type="hidden" id="soal_id_{{$item->soal_essay->id_essay}}" value="{{ $item->id_soal }}">
                                <input type="hidden" id="peserta_ujian_id" value="{{$peserta->id_peserta_ujian}}">
                                <input type="hidden" id="paket_soal_id" value="{{$paket_soal_id}}">
                            </div>

                        @elseif($item->jenis_soal == "pilgan")
                            <tr>
                                <td><p>{!! $item->pertanyaan !!}</p></td>
                            </tr>
                            <tr>
                                <td>
                                    @foreach ($item->soal_pilgan as $index => $pilgan)
                                        @php
                                            $jawaban = $jawaban_pilgan->where('soal_id', $item->id_soal)->first();
                                        @endphp

                                        <input type="radio" class="pilihan" name="pilihan_{{$item->id}}" value="{{ $pilgan->kode }}" id="pilihan_{{$item->id}}_{{$index}}"
                                        @if ($jawaban && $jawaban->jawab == $pilgan->kode)
                                            checked
                                        @endif
                                        >
                                        {{$pilgan->kode}}. {{$pilgan->teks}} <br>
                                        <input type="hidden" class="soal_id" value="{{ $item->id_soal }}">
                                        <input type="hidden" id="pilgan_id_{{$item->id}}_{{$index}}" value="{{ $pilgan->id_pilgan }}">
                                        <input type="hidden" id="paket_soal_id" value="{{$paket_soal_id}}">
                                        <input type="hidden" id="kunci" value="{{$item->kunci}}">
                                        <input type="hidden" id="peserta_ujian_id" value="{{$peserta->id_peserta_ujian}}">
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
                <?php $i++; ?>
            @endforeach
        </div>
    </div>

    <!-- Navigasi Soal -->
    <div class="row mt-3">
        <div class="col-6 text-start">
            <a href="{{ $soal_satuan->previousPageUrl() }}" class="btn btn-secondary prev-page" @if ($soal_satuan->currentPage() == 1) style="display:none" @endif>
                Sebelumnya
            </a>
        </div>
        <div class="col-6 text-end">
            <a href="{{ $soal_satuan->nextPageUrl() }}" class="btn btn-secondary next-page" @if (!$soal_satuan->hasMorePages()) style="display:none" @endif>
                Selanjutnya
            </a>
        </div>
    </div>
</div>

<!-- Nomor Soal Container -->
 <div class="col-sm-12 col-lg-4">
    <div class="card mt-1">
        <div class="card-body" id="nomorSoalContainer">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        {!! $soal_satuan->links('vendor.pagination.ujian-pagination') !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- <div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($soal_satuan as $index => $soal)
                            @php
                                // Hitung nomor soal sesuai urutan
                                $nomorSoal = $soal_satuan->firstItem() + $index;
                                // Periksa apakah soal ini sudah dijawab
                                $isAnswered = $soal_terjawab[$soal->id_soal] ?? false;
                            @endphp

                            <li class="list-group-item d-flex justify-content-between align-items-center
                                {{ $isAnswered ? 'bg-success text-white' : 'bg-danger text-white' }}">
                                <span>Soal No. {{ $nomorSoal }} - {{ $soal->jenis_soal }}</span>
                                <a href="#" class="btn btn-light btn-sm">
                                    Lihat Soal
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Paginasi -->
                    <div class="mt-3">
                        {{ $soal_satuan->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> --}}





<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Pengaturan JS untuk simpan jawaban essay
    $(document).on('change', 'textarea[id^="jawaban_essay"]', function(){
        var jawaban_essay = $(this).val();
        var essay_id = $(this).attr('id').split('_')[2];
        var peserta_ujian_id = $("#peserta_ujian_id").val();
        const ujian_id = $('#ujian_id').val();
        var id_soal = $('#soal_id_' + essay_id).val();

        $.ajax({
            url: "{{ url('jawab/soal/essay') }}",
            type: "GET",
            dataType: 'json',
            data: {
                jawaban_essay: jawaban_essay,
                id_soal: id_soal,
                essay_id: essay_id,
                peserta_ujian_id: peserta_ujian_id,
                ujian_id: ujian_id
            },
            success: function(data) {
                const totalSoal = data.totalSoal;
                const totalJawaban = data.totalJawaban;
                if (totalSoal == totalJawaban ) {
                    $('#akhiriUjian').show();
                }
            }
        });
    });

    // Pengaturan JS untuk simpan jawaban pilgan
    $(document).on('click', 'input[type=radio][name^="pilihan_"]', function() {
        var pilihanName = $(this).attr('name');
        var jawab_pilgan = this.value;
        var soal_id = pilihanName.split('_')[1];
        var pilgan_id = $("#pilgan_id_" + soal_id + "_0").val();
        var peserta_ujian_id = $("#peserta_ujian_id").val();
        const ujian_id = $('#ujian_id').val();
        var id_soal = $('.soal_id').val();
        var paket_soal_id = $('#paket_soal_id').val();

        // var poin = $("#poin").val();
        var kunci = $("#kunci").val();
        // var score = (jawab_pilgan === kunci) ? poin : 0;
        var status = (jawab_pilgan === kunci) ? "T" : "F";

        $.ajax({
            url: "{{ url('jawab/soal/pilgan') }}",
            type: "GET",
            dataType: 'json',
            data: {
                jawab_pilgan: jawab_pilgan,
                id_soal: id_soal,
                pilgan_id: pilgan_id,
                peserta_ujian_id: peserta_ujian_id,
                // score: score,
                paket_soal_id: paket_soal_id,
                status: status,
                ujian_id: ujian_id
            },
            error: function(xhr, status, error) {
                console.error("Error:", xhr.responseText);
            },
            success: function(data) {
                // console.log(data.totalSoal, data.totalJawaban)
                const totalSoal = data.totalSoal;
                const totalJawaban = data.totalJawaban;
                if(totalSoal == totalJawaban){
                    $('#akhiriUjian').show();
                }
            }
        });
    });

    // Akhiri Ujian
    // akhiriUjian.addEventListener("click", function() {
    //     var ujianId = '{{ encrypt($ujian->id_ujian) }}';
    //     var pesertaId = '{{ encrypt($peserta->id_peserta_ujian) }}';

    //     event.preventDefault();
    //     Swal.fire({
    //         title: 'Konfirmasi',
    //         text: 'Anda yakin ingin mengakhiri ujian?',
    //         icon: 'question',
    //         showCancelButton: true,
    //         confirmButtonColor: '#3085d6',
    //         cancelButtonColor: '#d33',
    //         confirmButtonText: 'Ya, Akhiri Ujian'
    //     }).then((result) => {
    //         if (result.isConfirmed) {
    //             $.ajax({
    //                 url: '/exam24/finish/' + ujianId + '/participant/' + pesertaId,
    //                 type: 'GET',
    //                 success: function(response) {
    //                     Swal.fire({
    //                         title: 'Success',
    //                         text: response.message,
    //                         icon: 'success',
    //                     }).then(() => {
    //                         window.location.href = response.redirect;
    //                     });
    //                 },
    //                 error: function(response) {

    //                     Swal.fire({
    //                         title: 'Error',
    //                         text: response.message,
    //                         icon: 'error',
    //                         confirmButtonText: 'OK'
    //                     });
    //                 }
    //             });
    //         }
    //     });

    // });

</script>
