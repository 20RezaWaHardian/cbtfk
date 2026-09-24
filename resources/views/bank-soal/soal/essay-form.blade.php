@extends('layouts.app')

@section('title', 'Form Soal Essay')

@push('style')
    <!-- CSS Libraries -->
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('summernote/summernote-lite.css') }}" rel="stylesheet">
@endpush

@section('contents')
    @include('bank-soal.soal.review-form')

    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-8">
                    <h4 class="fw-semibold mb-8">Tambah Soal Essay</h4>
                    <p>Silahkan isi form dibawah untuk menambahkan/merubah soal. </p>
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form
                action="{{ $soal->pertanyaan ? route('bank-soal.soal.updateSoalEssay', $soal->id_soal) : route('bank-soal.soal.storeSoalEssay', $soal->id_soal) }}"
                method="POST">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="pertanyaan" class="mb-2"><b>Pertanyaan</b></label>
                                    <textarea class="summernote" name="pertanyaan" style="display: none;">{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                                    @error('pertanyaan')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="kunci" class="mb-2 mt-2">
                                        <b>Kunci</b>
                                    </label>
                                    <textarea class="summernote" name="kunci" style="display: none;">{{ old('kunci', $soal->kunci) }}</textarea> @error('kunci')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="col-md-2">
                                <div class="form-group">
                                    <label for="poin">Poin</label>
                                    <input type="number" class="form-control" min=1 id="poin" name="poin" value={{ old('poin', $soal->poin) }} />
                                </div>
                            </div> --}}

                        </div>
                    </div>
                    <div class="card-footer bg-whitesmoke br">
                        <a href="{{ route('bank-soal.soal.showSoalByKategori', $soal->kategori_soal_id) }}"
                            class="btn btn-secondary">Kategori Soal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('modal')
@endpush

@push('scripts')
    <script src="{{ asset('summernote/summernote-lite.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                tabsize: 2,
                height: 100
            });
        });
    </script>
@endpush
