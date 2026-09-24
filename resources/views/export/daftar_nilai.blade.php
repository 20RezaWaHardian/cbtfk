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
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/logos/logo_unja.png'))) }}"
                    style="width:100px">
            </td>
            <td align="center" style="font-size: 14px;">
                <H3 style="color:#eb5707">KEMENTERIAN PENDIDIKAN TINGGI, SAINS DAN TEKNOLOGI</H3>
                <H3 style="color:#eb5707">UIN STS Jambi</H3>
                <H3 style="color:#eb5707">Fakultas Kedokteran dan Ilmu Kesehatan</H3>
                <p style="color:#065ec2">Kampus Pinang Masak, Jl. Raya Jambi - Muara Bulian KM. 15, Mendalo Indah, Jambi.
                    Kode Pos 36361 Telp. (0741) 583377, 583111 </p>


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
                <td>{{$p->mhs_pt->no_mhs}}</td>
                <td>{{$p->mhs_pt->mahasiswa->nama_mahasiswa}}</td>
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
