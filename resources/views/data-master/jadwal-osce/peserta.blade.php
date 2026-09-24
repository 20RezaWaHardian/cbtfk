@extends('layouts.app')
@section('title', 'Peserta & Pembagian Stase OSCE')
@section('contents')
<div class="card"><div class="card-body">
    <h4>Peserta & Pembagian Stase — {{ $jadwal_osce->keterangan }}</h4>
    <p>Blok: {{ $jadwal_osce->blok_id }} | Calon: {{ $calon->whereNotIn('id_mhs_pt', $peserta->pluck('id_mhs_pt'))->count() }} | Terdaftar: {{ $peserta->count() }} | Belum ditempatkan: {{ $peserta->whereNotIn('id_mhs_pt', $riwayat->keys())->count() }}</p>
    <a href="{{ route('data-master.jadwal-osce.index') }}">Kembali ke Jadwal OSCE</a>
    <p class="mt-2 mb-0">Daftarkan mahasiswa sekali pada jadwal ini. Set stase awal agar peserta masuk antrean pengawas.</p>
</div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row">
@can('create data-master/jadwal-osce')
<div class="col-lg-4">
<div class="card"><div class="card-body">
    <h5>Tambah Peserta dari Blok Jadwal</h5>
    <form method="post" action="{{ route('data-master.storePesertaOsce') }}">
        @csrf
        <input type="hidden" name="id_jadwal_osce" value="{{ $id_jadwal_osce }}">
        <input id="cari-calon" class="form-control mb-2" placeholder="Cari nama atau NIM" aria-label="Cari calon peserta">
        <label class="d-block mb-2"><input type="checkbox" id="pilih-calon"> Pilih semua hasil pencarian</label>
        <div id="daftar-calon" class="mb-3 overflow-auto" style="max-height: 420px">
            @foreach($calon->whereNotIn('id_mhs_pt', $peserta->pluck('id_mhs_pt')) as $mhs)
                <label class="calon-item d-block border-bottom py-2"><input type="checkbox" name="id_mhs_pt[]" value="{{ $mhs->id_mhs_pt }}" @checked(in_array($mhs->id_mhs_pt, old('id_mhs_pt', [])))> {{ $mhs->no_mhs }} — {{ $mhs->nama_mahasiswa }}</label>
            @endforeach
        </div>
        <p id="jumlah-calon" aria-live="polite"></p>
        <button type="button" id="batal-calon" class="btn btn-light mb-2">Batal pilih</button>
        <button id="tambah-calon" class="btn btn-primary mb-2">Tambahkan Peserta Terpilih</button>
    </form>
</div></div>
</div>
@endcan
<div class="col">
<div class="card"><div class="card-body table-responsive">
    <h5>Peserta Terdaftar ({{ $peserta->count() }})</h5>
    @can('create data-master/jadwal-osce')
    <button type="button" class="btn btn-primary mb-3" id="atur-massal" data-bs-toggle="modal" data-bs-target="#modal-pembagian">Atur Stase Awal Terpilih</button>
    @endcan
    <table id="peserta-osce" class="table table-bordered"><thead><tr><th>Pilih</th><th>NIM</th><th>Nama</th><th>Stase Awal</th><th>Urutan Awal</th><th>Stase Aktif / Terakhir</th><th>Status</th><th>Pembagian / Aksi</th></tr></thead><tbody>
    @forelse($peserta as $p)
        @php
            $rows = $riwayat->get($p->id_mhs_pt, collect());
            $awal = $rows->first();
            $aktif = $rows->first(fn($r) => in_array($r->status, ['menunggu', 'sedang_dinilai'])) ?? $rows->last();
            $bolehUbah = $rows->count() <= 1 && (!$awal || ($awal->status === 'menunggu' && !$awal->waktu_mulai));
        @endphp
        <tr>
            <td>@can('create data-master/jadwal-osce') @if($bolehUbah)<input type="checkbox" class="pilih-peserta" value="{{ $p->id_mhs_pt }}" aria-label="Pilih {{ $p->no_mhs }}">@endif @endcan</td>
            <td>{{ $p->no_mhs }}</td><td>{{ $p->nama_mahasiswa }}</td>
            <td>{{ $station->firstWhere('id_jenis_osce', $awal->id_jenis_osce ?? null)->nama_jenis_osce ?? 'Belum diset' }}</td>
            <td>{{ $awal->urutan_antrian ?? '—' }}</td>
            <td>{{ $station->firstWhere('id_jenis_osce', $aktif->id_jenis_osce ?? null)->nama_jenis_osce ?? '—' }}</td>
            <td>{{ $aktif->status ?? 'Belum ditempatkan' }}</td>
            <td>
            @if($bolehUbah)
                @can('create data-master/jadwal-osce')
                <button type="button" class="btn btn-sm btn-primary mb-2 atur-peserta" data-id="{{ $p->id_mhs_pt }}" data-bs-toggle="modal" data-bs-target="#modal-pembagian">Atur Stase Awal</button>
                @endcan
                @can('delete data-master/jadwal-osce')
                <form method="post" action="{{ route('data-master.hapusPeserta', encrypt($p->id_jadwal_has_mhs)) }}" onsubmit="return confirm('Hapus peserta dan antrean awal dari jadwal ini?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-danger">Hapus Peserta</button>
                </form>
                @endcan
            @else
                <span class="text-muted">Pembagian dikunci: sudah mulai dinilai.</span>
            @endif
            </td>
        </tr>
    @empty @endforelse
    </tbody></table>
</div></div>
</div></div>
@can('create data-master/jadwal-osce')
<div class="modal fade" id="modal-pembagian" tabindex="-1" aria-labelledby="judul-pembagian" aria-hidden="true">
    <div class="modal-dialog"><form class="modal-content" method="post" action="{{ route('data-master.storePesertaOsce') }}">
        @csrf
        <input type="hidden" name="id_jadwal_osce" value="{{ $id_jadwal_osce }}">
        <div class="modal-header"><h5 id="judul-pembagian">Atur Stase Awal</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <div id="pilihan-pembagian"></div><p id="jumlah-pembagian"></p>
            <label class="form-label" for="stase-pembagian">Stase awal</label>
            <select id="stase-pembagian" name="id_jenis_osce" class="form-select mb-3" required>
                <option value="">Pilih stase</option>
                @foreach($station as $s)<option value="{{ $s->id_jenis_osce }}">{{ $s->nama_jenis_osce }}</option>@endforeach
            </select>
            <label class="form-label" for="urutan-pembagian">Urutan mulai (kosongkan untuk otomatis)</label>
            <input id="urutan-pembagian" name="urutan_antrian" type="number" min="1" class="form-control">
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button id="simpan-pembagian" class="btn btn-primary">Simpan Pembagian</button></div>
    </form></div>
</div>
@endcan
@endsection
@push('style')
<link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet">
@endpush
@push('scripts')
<script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = $('#peserta-osce').DataTable({order: [[1, 'asc']], columnDefs: [{orderable: false, targets: [0, 7]}]});
    const search = document.getElementById('cari-calon');
    if (!search) return;
    const items = Array.from(document.querySelectorAll('.calon-item'));
    const all = document.getElementById('pilih-calon');
    function refresh() {
        const visible = items.filter(item => !item.hidden);
        const count = items.filter(item => item.querySelector('input').checked).length;
        const checked = visible.filter(item => item.querySelector('input').checked).length;
        all.checked = visible.length > 0 && checked === visible.length;
        all.indeterminate = checked > 0 && checked < visible.length;
        document.getElementById('jumlah-calon').textContent = count + ' dipilih; ' + visible.length + ' calon ditampilkan.';
        document.getElementById('tambah-calon').disabled = count === 0;
    }
    search.addEventListener('input', function () {
        items.forEach(item => {
            item.hidden = !item.textContent.toLowerCase().includes(search.value.toLowerCase());
            item.classList.toggle('d-block', !item.hidden);
        });
        refresh();
    });
    all.addEventListener('change', function () { items.filter(item => !item.hidden).forEach(item => item.querySelector('input').checked = all.checked); refresh(); });
    document.getElementById('daftar-calon').addEventListener('change', refresh);
    document.getElementById('batal-calon').addEventListener('click', function () { items.forEach(item => item.querySelector('input').checked = false); refresh(); });
    document.getElementById('modal-pembagian').addEventListener('show.bs.modal', function (event) {
        const id = event.relatedTarget?.dataset.id;
        const ids = id ? [id] : table.$('.pilih-peserta:checked').map(function () { return this.value; }).get();
        const container = document.getElementById('pilihan-pembagian');
        container.replaceChildren();
        ids.forEach(value => { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'id_mhs_pt[]'; input.value = value; container.appendChild(input); });
        document.getElementById('jumlah-pembagian').textContent = ids.length + ' peserta dipilih.';
        document.getElementById('simpan-pembagian').disabled = ids.length === 0;
        document.getElementById('stase-pembagian').value = '';
        document.getElementById('urutan-pembagian').value = '';
    });
    refresh();
});
</script>
@endpush