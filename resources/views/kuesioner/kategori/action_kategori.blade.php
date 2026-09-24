<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Data Paket Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ $kategori->id_kategori_kuesioner ? route('kuesioner.update-kategori-kue', $kategori->id_kategori_kuesioner) : route('kuesioner.store-kategori-kue') }}"
        method="POST">
        @csrf
        @isset($kategori->id_kategori_kuesioner)
            @method('put')
        @endisset
        <div class="modal-body">
            <input type="hidden" value="{{$kuesioner->id_kuesioner ?? null }}" name="kuesioner_id">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="nama_kategori"> Kategori Pertanyaan Kuesioner</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="nama_kategori"
                            value="{{ $kategori->nama_kategori }}" name="nama_kategori">
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
