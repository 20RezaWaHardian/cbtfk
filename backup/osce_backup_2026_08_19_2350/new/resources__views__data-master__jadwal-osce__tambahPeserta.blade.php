<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            {{ $peserta != null ? 'Edit' : 'Tambah' }} Peserta OSCE
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form 
        action="{{ $peserta  != null ? route('data-master.jenis-osce.update', $peserta) : route('data-master.storePesertaOsce') }}"
        method="POST">
        @csrf
        @if($peserta != null)
            @method('PUT')
        @endif
        
        <div class="modal-body">
            <div class="row">
                <input type="hidden" name="id_jadwal_osce" value="{{$id_jadwal_osce}}">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="nama_jenis_osce" class="form-label">Mahasiswa<span class="text-danger">*</span></label>
                        <select name="id_mhs_pt" class="form-control" id="id_mhs_pt">
                            
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="form-group">
                        <label class="form-label">Station Awal<span class="text-danger">*</span></label>
                        <select name="id_jenis_osce" class="form-control" required>
                            <option value="">Pilih Station</option>
                            @foreach($station as $s)
                                <option value="{{ $s->id_jenis_osce }}">{{ $s->nama_jenis_osce }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <div class="form-group">
                        <label class="form-label">Urutan Antrian</label>
                        <input type="number" name="urutan_antrian" class="form-control" min="1" placeholder="Kosongkan untuk otomatis">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>