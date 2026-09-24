@extends('layouts.app')

@section('title', 'Bank Soal - Soal')

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Form Daftar Kategori Soal</h4>
                </div>
                <div class="col-3 text-end">
                    <a href="{{ route('bank-soal.soal.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="formAction"
                        action="{{ $kategori_soal->id_kategori_soal ? route('bank-soal.soal.update', $kategori_soal->id_kategori_soal) : route('bank-soal.soal.store') }}"
                        method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="kategoriName" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kategori') is-invalid @enderror" id="kategoriName"
                                value="{{ old('nama_kategori', $kategori_soal->nama_kategori) }}" name="nama_kategori" required autofocus>
                            @error('nama_kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-control" id="semester" name="semester">
                                <option value="">-- Pilih Semester --</option>
                                @foreach ($semester as $sem)
                                    <option value="{{ $sem['id_semester'] }}" {{ $sem['aktif'] ? 'selected' : '' }}>
                                        {{ $sem['nama_semester'] }}{{ $sem['aktif'] ? ' (Aktif)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="select-block" class="form-label">Blok/Kelas Kedokteran</label>
                            <select id="select-block" name="id_kelas" class="form-select @error('id_kelas') is-invalid @enderror" data-selected="{{ old('id_kelas', $kategori_soal->id_kelas) }}">
                                <option value="">-- Pilih Blok --</option>
                            </select>
                            @error('id_kelas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function loadBlok() {
                const selected = $('#select-block').data('selected');
                const semesterId = $('#semester').val();
                const selectBlock = $('#select-block');

                selectBlock.find('option:not(:first)').remove();

                if (!semesterId) {
                    return;
                }

                $.ajax({
                    url: '{{ url('/bank-soal/soal/get-blok/kelas') }}',
                    data: {
                        id_semester: semesterId
                    },
                    method: 'GET',
                    success: function(response) {
                        response.kelas.forEach(function(item) {
                            const label = item.nama + ' - ' + item.kode;
                            const isSelected = selected == item.id ? 'selected' : '';
                            selectBlock.append('<option value="' + item.id + '" ' + isSelected + '>' + label + '</option>');
                        });
                    }
                });
            }

            $('#semester').on('change', loadBlok);
            loadBlok();
        });
    </script>
@endpush