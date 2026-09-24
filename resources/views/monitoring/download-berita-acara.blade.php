<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <!-- Halaman 1: Daftar Mahasiswa -->
    <h3 class="text-align-center">Daftar Hadir Peserta Ujian</h3>
    <p>{{ $ujian->nama_ujian }} diselenggarakan pada {{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta_ujian as $index => $peserta)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $peserta->mhs_pt->no_mhs ?? '-' }}</td>
                <td>{{ $peserta->mhs_pt->mahasiswa->nama_mahasiswa ?? '-' }}</td>
                @if(isset($peserta->mulai_ujian))
                    <td>{{ $peserta->kehadiran == 0 ? 'Hadir' : 'Tidak Hadir' }}</td>
                @else
                <td> Belum Ujian</td>

                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Halaman 2: Berita Acara -->
    <div class="page-break"></div>
    <h3>Berita Acara</h3>
    <p>{{ $ujian->nama_ujian }} diselenggarakan pada {{ \Carbon\Carbon::parse($ujian->tanggal_ujian)->format('Y-m-d') }}
    <p>Berikut adalah laporan berita acara:</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Foto Face Register Tidak Sama Dengan Foto Siakad</th>
                <th>Menggunakan Alat Yang Dilarang/Menyalin dan Merekam Soal Ujian Dengan Media Apapun</th>
                <th>Menyontek/Membawa Catatan</th>
                <th>Berbicara Dengan Peserta Lain</th>
            </tr>
        </thead>
        <tbody>
            @foreach($peserta_ujian_ba as $index => $peserta)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $peserta->mhs_pt->no_mhs ?? '-' }}</td>
                <td>{{ $peserta->mhs_pt->mahasiswa->nama_mahasiswa ?? '-' }}</td>
                <td>{{ $peserta->kesamaan_foto == 0 ? 'Tidak' : 'Ya' }}</td>
                <td>{{ $peserta->alat_bantu == 0 ? 'Tidak' : 'Ya' }}</td>
                <td>{{ $peserta->menyontek == 0 ? 'Tidak' : 'Ya' }}</td>
                <td>{{ $peserta->berbicara == 0 ? 'Tidak' : 'Ya' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <ul>
        <li>Total Peserta: {{ $peserta_ujian->count() }}</li>
        <li>Peserta Hadir: {{ $peserta_ujian->where('kehadiran', 0)->count() }}</li>
        <li>Peserta Tidak Hadir: {{ $peserta_ujian->where('kehadiran', 1)->count() }}</li>
    </ul>
</body>
</html>
