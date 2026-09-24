<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            {{ $komponen->id_komponen_nilai_osce ? 'Edit' : 'Tambah' }} Data Komponen Nilai OSCE
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form 
        action="{{ isset($komponen->id_komponen_nilai_osce) ? route('data-master.komponen-nilai-osce.update', $komponen->id_komponen_nilai_osce) : route('data-master.komponen-nilai-osce.store') }}"
        method="POST">
        @csrf
        @if($komponen->id_komponen_nilai_osce)
            @method('PUT')
        @endif
        
        <div class="modal-body">
            <div class="row">
                <input type="hidden" name="id_jenis_osce" value="{{$id_jenis_osce}}">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="nama_komponen" class="form-label">Nama Komponen Nilai <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" 
                            id="nama_komponen"
                            value="{{ $komponen->nama_komponen }}" 
                            name="nama_komponen"
                            placeholder="Masukkan Komponen Nilai">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="bobot_nilai" class="form-label">Bobot Komponen Nilai <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" 
                            id="bobot_nilai"
                            value="{{ $komponen->bobot_nilai }}" 
                            name="bobot_nilai">
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