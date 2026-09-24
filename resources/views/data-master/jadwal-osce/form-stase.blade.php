<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            Form Stase
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form 
        action="{{ route('data-master.simpanStase', $id_jadwal_osce)}}"
        method="POST">
        @csrf
        
        <div class="modal-body">
            <div class="row">
                
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="stase" class="form-label">Stase</label>
                        <select class="form-control" name="stase" id="stase">
                            <option>Pilih Stase</option>
                            @foreach($stase as $s)
                                <option value="{{$s->id_jenis_osce}}">{{$s->nama_jenis_osce}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="id_pegawai" class="form-label">Penguji Stase</label>
                        <select class="form-control" name="id_pegawai" id="id_pegawai">
                            
                        </select>
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