<html>

<head>
    <style>
        /* #watermark { position: fixed;  width: 200px; height: 200px; opacity: .1; } */
        .garis {
            border-width: 500px;
            border: 1px solid black;
        }
    </style>
</head>

<body>



    <table width="100%">
        <tr>
            <td width="10%">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/logos/logouin.png'))) }}"
                    style="width:100px">
            </td>
            <td align="center" style="font-size: 14px;">
                <H3 style="color:#090401">KEMENTERIAN AGAMA</H3>
                <H3 style="color:#090401">UNIVERSITAS ISLAM NEGERI SULTAN THAHA SAIFUDDIN JAMBI</H3>
                <H3 style="color:#090401">FAKULTAS KEDOKTERAS</H3>
                <p style="color:#090401">Jalan Lintas Jambi - Muaro Bulian KM. 16 Simpang Sungai Duren Kab. Muaro Jambi 36363</p>


            </td>

        </tr>

        <tr>
            <td colspan="2">
                <hr style="color:Black">
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center">
                <h4 style="margin-bottom: 50px;"> <u>DAFTAR NILAI UJIAN {{$data['nama_ujian'] ?? '-'}}</u> </h4>
            </td>
        </tr>
    </table>

    <table width="100%" border="1">
        <tr>
            <th width="2px"> No </th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Jam Mulai</th>
            <th>Jam Selesai</th>
            <th>Durasi</th>
            <th>Nilai</th>
        </tr>
        @foreach($peserta as $p)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{$p->mahasiswa->nim}}</td>
                <td>{{$p->mahasiswa->nama}}</td>
                <td>{{ \Carbon\Carbon::parse($p->mulai_ujian)->format('H:i:s') }}</td>
                @if($p->waktu_berhenti)
                <td>{{ \Carbon\Carbon::parse($p->waktu_berhenti)->format('H:i:s') }}</td>
                @else
                    @php
                        $start = new DateTime($p->mulai_ujian);
                        list($jam, $menit, $detik) = explode(":", $p->ujian->paket_soal->durasi);
                        $interval = new DateInterval("PT{$jam}H{$menit}M{$detik}S");
                        $start->add($interval);
                    @endphp
                    <td>{{ $start->format("H:i:s") }}</td>

                @endif
                <td>{{ \Carbon\Carbon::parse($p->ujian->paket_soal->durasi)->format('H:i:s') }}</td>
                <td>{{ $p->total_nilai() }}</td>
            </tr>
        @endforeach
        
    </table>

    

</body>

</html>
