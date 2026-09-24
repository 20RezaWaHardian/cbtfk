<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Form Input Pertanyaan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ $pertanyaan->id_pertanyaan_kuesioner ? route('kuesioner.update-pertanyaan-kue', $pertanyaan->id_pertanyaan_kuesioner) : route('kuesioner.store-pertanyaan-kue') }}"
        method="POST">
        @csrf
        @isset($pertanyaan->id_pertanyaan_kuesioner)
            @method('put')
        @endisset
        <div class="modal-body">
            <input type="hidden" value="{{ $kategori->id_kategori_kuesioner ?? $pertanyaan->kategori_kuesioner_id }}" name="kategori_kuesioner_id">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="pertanyaan">Pertanyaan</label>
                        <textarea class="form-control" id="pertanyaan" name="pertanyaan" rows="4" placeholder="Masukkan pertanyaan di sini" required>{{ old('pertanyaan') ?? $pertanyaan->pertanyaan ?? '' }}</textarea>
                        @error('pertanyaan')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
