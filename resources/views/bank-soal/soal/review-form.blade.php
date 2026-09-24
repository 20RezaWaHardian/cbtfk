<div class="alert alert-light">
    @include('bank-soal.soal.status-validasi')
    @if ($soal->status_validasi === 2)
        <p class="mt-2">Simpan perbaikan terlebih dahulu. Status tetap ditolak sampai tombol Ajukan Ulang diklik.</p>
        @if ($soal->milikPembuat(auth()->user()) && auth()->user()->can('update soal'))
            <form method="POST" action="{{ route('bank-soal.soal.ajukanUlang', $soal->id_soal) }}">
                @csrf
                <button class="btn btn-primary" @disabled(! $soal->dapatDiajukanUlang())>Ajukan Ulang</button>
            </form>
        @endif
    @endif
</div>