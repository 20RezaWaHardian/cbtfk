<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">{{ $instrumen->exists ? 'Edit' : 'Tambah' }} Instrumen Penilaian</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
    </div>
    <form id="formInstrumen" method="POST" action="{{ $instrumen->exists ? route('data-master.komponen-nilai-osce.updateInstrumen', encrypt($instrumen->id)) : route('data-master.komponen-nilai-osce.storeInstrumen') }}">
        @csrf
        @if($instrumen->exists) @method('PUT') @endif
        <input type="hidden" name="id_komponen_nilai_osce" value="{{ encrypt($komponen->id_komponen_nilai_osce) }}">
        <div class="modal-body">
            <p>Komponen: <strong>{{ $komponen->nama_komponen }}</strong></p>
            <div class="alert alert-danger d-none" role="alert" id="form-error"></div>
            <div class="mb-3">
                <label for="nilai" class="form-label">Nilai *</label>
                <select id="nilai" name="nilai" class="form-control" required>
                    <option value="">Pilih Nilai</option>
                    @for($i = 0; $i <= 3; $i++)
                        <option value="{{ $i }}" @selected((string) $instrumen->nilai === (string) $i)>{{ $i }}</option>
                    @endfor
                </select>
                <div class="text-danger field-error" data-error="nilai"></div>
            </div>
            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan Penilaian *</label>
                <textarea id="keterangan" name="keterangan" class="form-control" rows="5" required>{{ $instrumen->keterangan }}</textarea>
                <div class="text-danger field-error" data-error="keterangan"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Instrumen</button>
        </div>
    </form>
</div>
