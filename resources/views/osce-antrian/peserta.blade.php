@extends('layouts.app')
@section('title','Peserta Station OSCE')
@section('contents')
<div class="card bg-info-subtle shadow-none mb-4"><div class="card-body"><h4 class="fw-semibold mb-0">{{ $station->nama_jenis_osce }} - {{ $jadwal->keterangan }}</h4><a href="{{ route('osce-antrian.station', encrypt($jadwal->id_jadwal_osce)) }}">Kembali</a></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Urutan</th><th>NIM</th><th>Nama</th><th>Status</th><th>Nilai</th><th>Aksi</th></tr></thead><tbody>@foreach($peserta as $p)<tr><td>{{ $p->urutan_antrian }}</td><td>{{ $p->no_mhs }}</td><td>{{ $p->nama_mahasiswa }}</td><td><span class="badge bg-info">{{ $p->status }}</span></td><td>{{ $p->nilai_station }}</td><td>@if(in_array($p->status, ['menunggu', 'sedang_dinilai']))@can('update osce-antrian')<a class="btn btn-sm btn-success" href="{{ route('osce-antrian.nilai', encrypt($p->id_peserta_station_osce)) }}">Beri Nilai</a>@endcan @else<span class="text-muted">Penilaian selesai</span>@endif</td></tr>@endforeach</tbody></table></div></div></div>
@endsection
