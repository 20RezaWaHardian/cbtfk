<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 11px;
            vertical-align: top;
        }
        thead th {
            background-color: #eee;
            text-align: center;
        }
        tr {
            page-break-inside: avoid;
        }
        tbody {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <h3 style="text-align: center;">Laporan Kuesioner</h3>
    <h4 style="text-align: center;">{{ $kue->judul_kuesioner ?? '' }}</h4>

    <table class="table table-bordered">
        <thead>
          <tr>
            <th scope="col" width="3px">No</th>
            <th scope="col">Pertanyaan</th>
            <th scope="col">Pilihan Jawaban</th>
            <th scope="col">Jumlah</th>
            <th scope="col">Persentase</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($kue->kategori_kuesioner as $item)
                <tr>
                    <td colspan="5" style="font-weight: bold">
                        {{ App\Helpers\MyHelpers::angkaKeRomawi($loop->iteration) }}. {{ $item->nama_kategori }}
                    </td>
                </tr>

                @foreach ($item->pertanyaan as $p)
                    @php
                        $rowspan = count($pil_jwb);
                        $jawabanPertanyaan = $jwb_kue->where('pertanyaan_kuesioner_id', $p->id_pertanyaan_kuesioner);
                        $totalJawaban = $jawabanPertanyaan->count();

                        // Total responden unik (semua peserta kuesioner)
                        $totalResponden = $jwb_kue->pluck('peserta_ujian_id')->unique()->count();
                    @endphp

                    {{-- Kalau jenis pertanyaan point --}}


                    @if($p->jenis_pertanyaan == 'point')
                        @foreach ($pil_jwb as $pj)
                            @php
                                $jumlah = $jawabanPertanyaan
                                            ->where('pil_jwb_kue_id', $pj->id_pilihan_jwb_kue)
                                            ->count();

                                // Rumus: jumlah pilihan ÷ total jawaban pertanyaan
                                $persentase = $totalJawaban > 0
                                    ? number_format(($jumlah / $totalJawaban) * 100, 0)
                                    : 0;
                            @endphp

                            <tr>
                                @if ($loop->first)
                                    <td rowspan="{{ $rowspan }}">{{ $loop->parent->iteration }}</td>
                                    <td rowspan="{{ $rowspan }}">{{ $p->pertanyaan }}</td>
                                @endif
                                <td>{{ $pj->nama_pilihan }}</td>
                                <td>{{ $jumlah }}</td>
                                <td>{{ $persentase }}%</td>
                            </tr>
                        @endforeach

                    {{-- Kalau jenis pertanyaan jawaban terbuka (text) --}}
                    @else
                        @php
                            // Rumus: jumlah yang isi ÷ total responden
                            $persentaseUmum = $totalResponden > 0
                                ? number_format(($totalJawaban / $totalResponden) * 100, 0)
                                : 0;
                        @endphp

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->pertanyaan }}</td>
                            <td>Jawaban Terbuka</td>
                            <td>{{ $totalJawaban }}</td>
                            <td>{{ $persentaseUmum }}%</td>
                        </tr>
                    @endif
                @endforeach
            @endforeach


        </tbody>
    </table>

</body>
</html>
