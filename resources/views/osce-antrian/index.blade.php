@extends('layouts.app')
@section('title','OSCE Antrian')
@section('contents')
<div class="card bg-info-subtle shadow-none mb-4"><div class="card-body"><h4 class="fw-semibold mb-0">OSCE Antrian Station</h4><small>Menu baru. Flow lama tidak berubah.</small></div></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>#</th><th>Keterangan</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@foreach($data as $p)<tr><td>{{ $loop->iteration }}</td><td>{{ $p->keterangan }}</td><td>{{ $p->tanggal_ujian }}</td><td>{{ $p->waktu_mulai }} - {{ $p->waktu_selesai }}</td><td><span class="badge bg-success">Aktif</span></td><td><a class="btn btn-sm btn-info" href="{{ route('osce-antrian.station', encrypt($p->id_jadwal_osce)) }}">Station</a></td></tr>@endforeach</tbody></table></div></div></div>
@endsection
