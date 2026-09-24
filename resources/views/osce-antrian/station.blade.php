@extends('layouts.app')
@section('title','Station OSCE Antrian')
@section('contents')
<div class="card bg-info-subtle shadow-none mb-4"><div class="card-body"><h4 class="fw-semibold mb-0">Station: {{ $jadwal->keterangan }}</h4><a href="{{ route('osce-antrian.index') }}">Kembali</a></div></div>
<div class="row">@foreach($station as $s)<div class="col-md-4"><div class="card"><div class="card-body"><h5>{{ $s->nama_jenis_osce }}</h5><p>Antrian aktif: <b>{{ $s->jumlah_antrian }}</b></p><a class="btn btn-primary" href="{{ route('osce-antrian.peserta',[encrypt($jadwal->id_jadwal_osce), encrypt($s->id_jenis_osce)]) }}">Buka Antrian</a></div></div></div>@endforeach</div>
@endsection
