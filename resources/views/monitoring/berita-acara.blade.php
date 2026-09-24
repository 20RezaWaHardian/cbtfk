@extends('layouts.app')
@section('title', 'Berita Acara')
@push('style')
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
@endpush
@section('contents')
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-1">Berita Acara Peserta</h5>
            <p class="mb-0"> Silahkan isi berita acara peserta</p>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <a href="{{ route('ujian.monitoring.index',encrypt($peserta->ujian_id))  }}" class="btn btn-sm btn-primary">Kembali</a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <div style="overflow: hidden; border-radius: 15px;">
                <img src="{{ asset('regis_peserta_ujian/' . $peserta->face_register) }}" alt="Foto Peserta" style="width: 100%; height: auto; object-fit: cover; border-radius: 15px;">
            </div>
            <h6 style="text-align: center;" class="mt-2">Face Register</h6>
        </div>
        <div class="col-sm-12 col-xl-2" style="margin-bottom: 20px;">
            <div style="overflow: hidden; border-radius: 15px;">
                <img src="{{ asset('regis_peserta_ujian/' . $peserta->face_register) }}" alt="Foto Peserta" style="width: 100%; height: auto; object-fit: cover; border-radius: 15px;">
            </div>
            <h6 style="text-align: center;" class="mt-2">Foto Peserta</h6>
        </div>
        <div class="col-sm-12 col-xl-8 table-responsive">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>NAMA</th>
                        <td>{{ $peserta->mhs_pt->mahasiswa->nama_mahasiswa }}</td>
                    </tr>

                    <tr>
                        <th>NIM</th>
                        <td>{{ $peserta->mhs_pt->no_mhs }}</td>
                    </tr>

                    <tr>
                        <th>UJIAN</th>
                        <td>{{ $peserta->ujian->nama_ujian }}</td>
                    </tr>
                    <tr>
                        <th>PAKET SOAL</th>
                        <td>{{ $peserta->ujian->paket_soal->judul }}</td>
                    </tr>
                    <tr>
                        <th>IP ADDRESS</th>
                        <td>{{ $peserta->ip_address }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-sm-12 col-xl-12 table responsive">
            <form id="beritaAcaraForm" action="{{ route('monitoring.updateBeritaAcara',$peserta->id_peserta_ujian) }}" method="POST">
                @csrf
                @method('PATCH')
                <table class="table table-bordered ">
                    <tbody>
                        <tr>
                            <th>Tidak Hadir</th>
                            <td class="text-center"><input type="checkbox" class="ceklistBeritaAcara" name="kehadiran"  {{ $peserta->kehadiran == 1 ? 'checked' : '' }} onclick="checkCheckboxes()"></td>
                        </tr>
                        <tr>
                            <th>Foto Face Register Tidak Sama Dengan Foto Siakad</th>
                            <td class="text-center"><input type="checkbox" class="ceklistBeritaAcara" name="kesamaan_foto" {{ $peserta->kesamaan_foto == 1 ? 'checked' : '' }} onclick="checkCheckboxes()"></td>
                        </tr>
                        <tr>
                            <th>Menggunakan Alat Yang Dilarang/Menyalin dan Merekam Soal Ujian Dengan Media Apapun</th>
                            <td class="text-center"><input type="checkbox" class="ceklistBeritaAcara"  name="alat_bantu"  {{ $peserta->alat_bantu == 1 ? 'checked' : '' }} onclick="checkCheckboxes()"></td>
                        </tr>
                        <tr>
                            <th>Menyontek/Membawa Catatan</th>
                            <td class="text-center"><input type="checkbox" class="ceklistBeritaAcara" name="menyontek"  {{ $peserta->menyontek == 1 ? 'checked' : '' }} onclick="checkCheckboxes()"></td>
                        </tr>

                        <tr>
                            <th>Berbicara Dengan Peserta Lain</th>
                            <td class="text-center"><input type="checkbox" class="ceklistBeritaAcara" name="berbicara"  {{ $peserta->berbicara==1 ? 'checked' : '' }} onclick="checkCheckboxes()"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <button  id="saveButton" class="btn btn-primary" disabled>Perbaruhui Berita Acara</button>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            checkCheckboxes();
        });
        function checkCheckboxes() {
            const checkboxes = document.querySelectorAll('.ceklistBeritaAcara');
            const saveButton = document.getElementById('saveButton');
            saveButton.disabled = !Array.from(checkboxes).some(checkbox => checkbox.checked);
        }

        document.getElementById('beritaAcaraForm').addEventListener('submit', function(event) {
        var checkboxes = document.querySelectorAll('.ceklistBeritaAcara');
        var anyChecked = false;

        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                anyChecked = true;
            }
        });

        if (!anyChecked) {
            event.preventDefault();
            alert('Harap centang setidaknya satu checkbox sebelum menyimpan berita acara.');
        }
    });
    </script>
@endpush
