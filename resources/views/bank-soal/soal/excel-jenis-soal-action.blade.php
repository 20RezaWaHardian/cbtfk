<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Import Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ route('bank-soal.soal.storeExcelJenisSoal', $kategori_soal->id_kategori_soal) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-primary" role="alert">Format Import Soal (Excel) :
                        <a href="{{ route('bank-soal.paket-soal.downloadFormatSoal') }}"
                            class="alert-link import-data" style="color:blue">Download</a>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="sub_kategori_soal_id">Sub Kategori Soal</label>
                        <select class="form-control mb-2" id="sub_kategori_soal_id" name="sub_kategori_soal_id" required>
                            <option value="" disabled selected>Pilih Sub Kategori Soal...</option>
                            @foreach ($kategori_soal->sub_kategori_soal as $subKategori)
                                <option value="{{ $subKategori->id_sub_kategori_soal }}">
                                    {{ $subKategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">

                            <label for="file" class="form-label">Pilih file untuk diimpor</label>
                            <input class="form-control" type="file" name="file" id="file" required>


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
