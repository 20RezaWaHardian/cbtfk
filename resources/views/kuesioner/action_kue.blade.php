<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Data Paket Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ $kuesioner->id_kuesioner ? route('kuesioner.update', $kuesioner->id_kuesioner) : route('kuesioner.store') }}"
        method="POST">
        @csrf
        @isset($kuesioner->id_kuesioner)
            @method('put')
        @endisset
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="judul_kuesioner">Judul Kuesioner</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="judul_kuesioner"
                            value="{{ $kuesioner->judul_kuesioner }}" name="judul_kuesioner">
                    </div>
                </div>
                @if(isset($kuesioner->id_kuesioner))
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="judul_kuesioner">Status Kuesioner</label>
                        <select name="status" id="" class="form-control">
                            <option value="1" {{$kuesioner->status == 1 ? 'selected' : ''}}>Aktif</option>
                            <option value="2" {{$kuesioner->status == 2 ? 'selected' : ''}}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
