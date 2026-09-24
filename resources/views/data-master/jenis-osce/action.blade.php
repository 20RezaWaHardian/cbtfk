<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            {{ $jenis_osce->id_jenis_osce ? 'Edit' : 'Tambah' }} Data Stase OSCE
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form 
        action="{{ isset($jenis_osce->id_jenis_osce) ? route('data-master.jenis-osce.update', $jenis_osce->id_jenis_osce) : route('data-master.jenis-osce.store') }}"
        method="POST">
        @csrf
        @if($jenis_osce->id_jenis_osce)
            @method('PUT')
        @endif
        
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="nama_jenis_osce" class="form-label">Nama Stase OSCE <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" 
                            id="nama_jenis_osce"
                            value="{{ $jenis_osce->nama_jenis_osce }}" 
                            name="nama_jenis_osce"
                            placeholder="Masukkan nama jenis OSCE">
                    </div>
                </div>
                
                @if($jenis_osce->id_jenis_osce)
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" name="status" id="status">
                            <option value="1" {{ $jenis_osce->status == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="2" {{ $jenis_osce->status == 2 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>