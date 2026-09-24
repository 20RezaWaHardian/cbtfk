<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Jenis Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction" action="{{ route('bank-soal.soal.storeJenisSoalWithoutKategori') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="jenisSoal">Jenis Soal</label>
                        <select class="form-control" id="jenis_soal" name="jenis_soal">
                            <option disabled selected>Pilih Jenis Soal...</option>
                            <option value="pilgan">Pilihan Ganda
                            </option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>
                </div>
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        <label for="poin">Poin</label>
                        <input type="number" class="form-control" min=1 id="poin" name="poin" value=1 />
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
