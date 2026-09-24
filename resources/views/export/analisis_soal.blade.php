<html>

<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background: #eeeeee;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .no-border td {
            border: 0;
        }

        .badge {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table width="100%" class="no-border">
        <tr>
            <td width="10%">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width:90px">
                @endif
            </td>
            <td align="center" style="font-size: 13px;">
                <h3 style="color:#eb5707; margin: 0;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS DAN TEKNOLOGI</h3>
                <h3 style="color:#eb5707; margin: 0;">UIN STS Jambi</h3>
                <h3 style="color:#eb5707; margin: 0;">Fakultas Kedokteran dan Ilmu Kesehatan</h3>
                <p style="color:#065ec2; margin: 4px 0;">Kampus Pinang Masak, Jl. Raya Jambi - Muara Bulian KM. 15, Mendalo Indah, Jambi.
                    Kode Pos 36361 Telp. (0741) 583377, 583111</p>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <hr style="color:Black">
            </td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <h3><u>ANALISIS SOAL UJIAN {{ $jadwal->nama_ujian ?? '-' }}</u></h3>
            </td>
        </tr>
    </table>

    <table width="100%">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th>Soal</th>
                <th width="15%">Indeks Kesulitan (P)</th>
                <th width="12%">Kategori</th>
                <th width="12%">Daya Beda (D)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hasilAnalisis as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{!! $item['soal_id'] !!}</td>
                    <td class="text-center">{{ number_format($item['difficulty'], 2) }}</td>
                    <td class="text-center badge">{{ strtoupper($item['label']) }}</td>
                    <td class="text-center">{{ number_format($item['discrimination'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data analisis soal belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p><strong>Keterangan:</strong></p>
    <ul>
        <li><strong>Difficulty (P):</strong> &lt; 0.30 (Sulit), 0.30 - 0.70 (Sedang), &gt; 0.70 (Mudah).</li>
        <li><strong>Daya Beda (D):</strong> Nilai ideal adalah &gt; 0.30. Jika di bawah 0.20, soal perlu dievaluasi.</li>
    </ul>
</body>

</html>