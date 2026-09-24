@extends('layouts.app')
@section('title', 'OSCE - Data Komponen')
@section('contents')
<div class="card"><div class="card-body">
    <p class="text-muted">Langkah 1: Data Komponen / Langkah 2: Instrumen Penilaian</p>
    <h4>{{ $komponen->exists ? 'Edit' : 'Tambah' }} Komponen Nilai</h4>
    <p>Jenis OSCE: {{ $jenis->nama_jenis_osce }}</p>
    <p>Isi data komponen terlebih dahulu. Instrumen dikelola pada halaman terpisah setelah komponen tersimpan.</p>
    <form method="POST" action="{{ $komponen->exists ? route('data-master.komponen-nilai-osce.update', encrypt($komponen->id_komponen_nilai_osce)) : route('data-master.komponen-nilai-osce.store') }}">
        @csrf
        @if($komponen->exists) @method('PUT') @endif
        <input type="hidden" name="id_jenis_osce" value="{{ $id_jenis_osce }}">
        <div class="mb-3">
            <label for="nama_komponen" class="form-label">Nama Komponen *</label>
            <textarea id="nama_komponen" name="nama_komponen" class="form-control" rows="2" required maxlength="255">{{ old('nama_komponen', $komponen->nama_komponen) }}</textarea>
            @error('nama_komponen') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="mb-3">
            <label for="detail_komponen" class="form-label">Deskripsi Komponen *</label>
            <textarea id="detail_komponen" name="detail_komponen" class="form-control" rows="5" required>{{ old('detail_komponen', $komponen->detail_komponen) }}</textarea>
            @error('detail_komponen') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <div class="mb-4">
            <label for="bobot_nilai" class="form-label">Bobot Penilaian *</label>
            <select id="bobot_nilai" name="bobot_nilai" class="form-control" required>
                <option value="">Pilih Bobot</option>
                @for($i = 0; $i <= 3; $i++)
                    <option value="{{ $i }}" @selected((string) old('bobot_nilai', $komponen->bobot_nilai) === (string) $i)>{{ $i }}</option>
                @endfor
            </select>
            @error('bobot_nilai') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
        <a href="{{ route('data-master.komponen-nilai-osce.index', $id_jenis_osce) }}" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $komponen->exists ? 'Simpan Perubahan Komponen' : 'Simpan & Lanjut ke Instrumen' }}</button>
    </form>
</div></div>
@endsection
