{{-- Soal Satuan --}}
<div class="col-sm-12 col-lg-8">
    <div class="card mt-1">
        <div class="card-body" id="soalContainer">
            <div class="container row">
                <div class="col-12"><h6>Soal No.
                    @php
                    $nomor_soal = 1;
                    $activeSoal = $active_soalId;
                @endphp

                @foreach ($pagination_soal as $soalItem)
                        @if ($soalItem->soal_id == $activeSoal)
                            {{ $nomor_soal }}
                        @endif
                    @php
                        $nomor_soal++;
                    @endphp
                @endforeach

                    <h6>  </div>



            </div>
            <hr>
                <div class="container">
                    <table>
                        <input type="hidden" id="paket_soal_id" value="{{encrypt($paket_has_soal->paket_soal_id)}}">
                        @if($paket_has_soal->soal->jenis_soal == "essay")

                            {!! $paket_has_soal->soal->pertanyaan !!}
                            <div class="mt-2">
                                <b> Jawaban : </b>
                                <textarea class="form-control" name="jawab" id="jawaban_essay_{{$paket_has_soal->soal->soal_essay->id_essay}}" rows="3">{{ $jawaban ? $jawaban->jawab : '' }}</textarea>
                                <input type="hidden" id="essay_id" value="{{ encrypt($paket_has_soal->soal->soal_essay->id_essay) }}">
                                <input type="hidden" id="soal_id" value="{{ encrypt($paket_has_soal->soal_id) }}">
                                <input type="hidden" id="peserta_ujian_id" value="{{ encrypt($peserta->id_peserta_ujian) }}">
                            </div>

                        @elseif($paket_has_soal->soal->jenis_soal == "pilgan")
                            <tr>
                                <td><p>{!! $paket_has_soal->soal->pertanyaan !!}</p></td>
                            </tr>
                            <tr>
                                <td>
                                    @foreach ($paket_has_soal->soal->soal_pilgan as $index => $pilgan)
                                    <label for="pilihan_{{$paket_has_soal->soal_id}}_{{$index}}">
                                        <input type="radio" class="pilihan" name="pilihan_{{$paket_has_soal->soal_id}}" value="{{ $pilgan->kode }}" id="pilihan_{{$paket_has_soal->soal_id}}_{{$index}}"
                                        @if ($jawaban && $jawaban->jawab == $pilgan->kode)
                                            checked
                                        @endif
                                        >
                                        {{$pilgan->kode}}. {{$pilgan->teks}}
                                    </label><br>
                                        <input type="hidden" class="soal_id" value="{{ encrypt($paket_has_soal->soal_id )}}">
                                        <input type="hidden" id="pilgan_id_{{$paket_has_soal->soal_id}}_{{$index}}" value="{{ encrypt($pilgan->id_pilgan) }}">
                                        <input type="hidden" id="peserta_ujian_id" value="{{encrypt($peserta->id_peserta_ujian)}}">
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
        </div>
    </div>

<!-- Navigasi Soal -->
<div class="row mt-3">
    <div class="col-6 text-start">
        <a class="btn btn-primary"
           href="javascript:void(0);" id="prev">
            Sebelumnya
        </a>
    </div>
    <div class="col-6 text-end">
        @php
            $btn_warna = 'btn-light';
            $nama_btn = 'Ragu-Ragu';
            $value_jwb_ragu = 2;
            if($jawaban)
            {
                if($jawaban->jenis_jwb == 2)
                {
                    $btn_warna = 'btn-warning';
                    if(isset($jawaban->pilgan_id))
                    {
                        $value_jwb_ragu = 1;
                    }else{
                        $value_jwb_ragu = 3;
                    }
                }
            }
        @endphp
        <a class="btn {{$btn_warna}}"
           href="javascript:void(0);" id="ragu" onclick="jwbragu('{{$paket_has_soal->soal_id}}','{{encrypt($peserta->id_peserta_ujian)}}','{{$value_jwb_ragu}}','{{$paket_has_soal->soal->jenis_soal}}')">
            {{$nama_btn}}
        </a>
        <a class="btn btn-primary"
           href="javascript:void(0);" id="next">
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
                    <ul class="pagination">
                        @php
                            $no = 1;
                            $jawabanIds = $pagination_jawaban->where('jenis_jwb',1)->pluck('soal_id')->toArray();
                            $jawabanRagu = $pagination_jawaban->where('jenis_jwb',2)->pluck('soal_id')->toArray();
                            $jawabanRaguKosong = $pagination_jawaban->where('jenis_jwb',3)->pluck('soal_id')->toArray();
                            $activeSoal = $active_soalId;
                        @endphp

                        @foreach ($pagination_soal as $soalItem)
                            @php
                                $isAnswered = in_array($soalItem->soal_id, $jawabanIds);
                                $isRaguAnswered = in_array($soalItem->soal_id, $jawabanRagu);
                                $isEmptyRagu = in_array($soalItem->soal_id, $jawabanRaguKosong);
                                // if($jawaban)
                                // {
                                //     if($jawaban->pilgan_id)
                                //     {
                                //         $isAnswered = true
                                //     }
                                // }
                            @endphp
                            <li class="page-item">
                                <a class="page-link soal-box
                                    @if ($soalItem->soal_id == $activeSoal)
                                        active-soal
                                    @elseif ($isAnswered)
                                            answered-soal
                                            
                                    @elseif ($isRaguAnswered )

                                        ragu
                                    @else
                                        unanswerd-soal
                                    @endif"

                                    id="soal_{{ $soalItem->soal_id }}" data-soal-id="{{ $soalItem->soal_id }}"

                                    style="cursor: pointer; border-radius: 8px;">
                                    {{ $no }}
                                </a>
                            </li>

                            @php
                                $no++;
                            @endphp
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

