<span class="badge text-bg-{{ [0 => 'warning', 1 => 'success', 2 => 'danger'][$soal->status_validasi] ?? 'secondary' }}">
    {{ [0 => 'Belum validasi', 1 => 'Diterima', 2 => 'Ditolak'][$soal->status_validasi] ?? 'Status tidak dikenal' }}
</span>
@if ($soal->diajukan_ulang_at)
    <div>Pengajuan ulang: {{ $soal->diajukan_ulang_at->format('d-m-Y H:i') }}</div>
@endif
@if ($soal->komentar_validasi)
    <div style="white-space: pre-wrap">Komentar penolakan terakhir: {{ $soal->komentar_validasi }}</div>
@endif