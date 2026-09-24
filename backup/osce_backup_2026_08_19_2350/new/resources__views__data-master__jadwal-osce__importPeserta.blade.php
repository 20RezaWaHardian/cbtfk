<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">
            Import Peserta OSCE
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form 
        action="{{ route('data-master.simpanImportPeserta') }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="modal-body">
            <div class="row">
                <input type="hidden" name="id_jadwal_osce" value="{{$id_jadwal_osce}}">
                <div class="col-md-12">
                    <div class="alert alert-primary" role="alert">Format Import Peserta (Excel): kolom B=NIM, kolom C=ID/Nama Station, kolom D=Urutan Antrian.
                        <a href="{{ route('data-master.downloadFormatPeserta') }}"
                            class="alert-link import-data" style="color:blue">Download</a>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="nama_jenis_osce" class="form-label">Import File<span class="text-danger">*</span></label>
                        <input type="file" name="file_import_peserta" class="form-control" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
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