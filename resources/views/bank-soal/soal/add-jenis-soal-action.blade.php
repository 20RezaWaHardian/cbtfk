<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Jenis Soal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ $soal->id_soal ? route('bank-soal.soal.updateJenisSoal', $soal->id_soal) : route('bank-soal.soal.storeJenisSoal', $kategori_soal->id_kategori_soal) }}"
        method="POST">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="form-group">
                        <label for="subKategoriSoal">Sub Kategori Soal</label>
                        <select class="form-control" id="sub_kategori_soal_id" name="sub_kategori_soal_id">
                            <option value="">Tanpa Sub Kategori</option>
                            @foreach ($kategori_soal->sub_kategori_soal as $subKategori)
                                <option value="{{ $subKategori->id_sub_kategori_soal }}"
                                    {{ request('sub_kategori_soal_id') == $subKategori->id_sub_kategori_soal ? 'selected' : '' }}>
                                    {{ $subKategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="jenisSoal">Jenis Soal</label>
                        <select class="form-control" id="jenis_soal" name="jenis_soal">
                            <option disabled selected>Pilih Jenis Soal...</option>
                            <option value="pilgan" {{ $soal->jenis_soal == 'pilgan' ? 'selected' : '' }}>Pilihan Ganda
                            </option>
                            <option value="essay" {{ $soal->jenis_soal == 'essay' ? 'selected' : '' }}>Essay</option>
                        </select>
                    </div>
                </div>
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        <label for="poin">Poin</label>
                        <input type="number" class="form-control" min=1 id="poin" name="poin"
                            value="{{ $soal->poin ?? 1 }}" />
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
