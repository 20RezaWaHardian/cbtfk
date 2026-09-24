<table>
    <tr>
        <th colspan="5">ANALISIS SOAL UJIAN {{ $jadwal->nama_ujian ?? '-' }}</th>
    </tr>
    <tr></tr>
    <tr>
        <th>No</th>
        <th>Soal</th>
        <th>Indeks Kesulitan (P)</th>
        <th>Kategori</th>
        <th>Daya Beda (D)</th>
    </tr>
    @forelse($hasilAnalisis as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ strip_tags($item['soal_id']) }}</td>
            <td>{{ number_format($item['difficulty'], 2) }}</td>
            <td>{{ strtoupper($item['label']) }}</td>
            <td>{{ number_format($item['discrimination'], 2) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5">Data analisis soal belum tersedia.</td>
        </tr>
    @endforelse
    <tr></tr>
    <tr>
        <td colspan="5">Keterangan:</td>
    </tr>
    <tr>
        <td colspan="5">Difficulty (P): &lt; 0.30 (Sulit), 0.30 - 0.70 (Sedang), &gt; 0.70 (Mudah).</td>
    </tr>
    <tr>
        <td colspan="5">Daya Beda (D): Nilai ideal adalah &gt; 0.30. Jika di bawah 0.20, soal perlu dievaluasi.</td>
    </tr>
</table>