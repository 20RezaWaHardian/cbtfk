@extends('layouts.app')

@section('title', 'Bank Soal - Soal')

@push('style')
    <!-- CSS Libraries -->
    <link href="{{ asset('tambahan/vendor/datatables.net-dt/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('tambahan/vendor/datatables.net-responsive-dt/css/responsive.dataTables.min.css') }}"
        rel="stylesheet" />
@endpush

@section('contents')
    <div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-12">
                    <h4 class="fw-semibold mb-8">Daftar Kelas Blok</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-8">
                            <h5 class="card-title fw-semibold">Pilih Prodi:</h5>
                            <div class="form-group">
                                <select class="form-control" id="prodi" name="prodi">
                                    <option disabled selected>Pilih Prodi</option>
                                    @foreach ($prodi as $item)
                                        <option value="{{ $item->id_prodi }}">
                                            [ {{ $item->fakultas->nama_fakultas ?? '' }} ] {{ $item->nama_prodi ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-4">
                            <h5 class="card-title fw-semibold">Pilih Semester:</h5>
                            <div class="form-group">
                                <select class="form-control" id="semester" name="semester" disabled>
                                    <option disabled selected>Pilih Semester</option>
                                    @foreach ($semester as $sem)
                                        <option value="{{ $sem['id_semester'] }}">
                                            {{ App\Helpers\MyHelpers::semester($sem['id_semester'])}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Daftar Blok</h5>
                    <hr>
                    <div id="daftar-blok">
                        <!-- Blok data will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-9 fw-semibold">Detail Blok</h5>
                    <hr>
                    <div id="detail-blok">

                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@endsection

@push('scripts')
    <!-- js for this page only -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#prodi').change(function() {
                var prodiId = $(this).val();
                if (!prodiId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Silahkan pilih prodi terlebih dahulu!'
                    });
                    $('#semester').prop('disabled', true);
                    return;
                }
                // Clear any previous selections and data
                $('#semester').val('');
                $('#daftar-blok').empty();
                $('#detail-blok').empty();

                Swal.fire({
                    icon: 'info',
                    title: 'Pilih Semester',
                    text: 'Silahkan pilih semester untuk melanjutkan.'
                });

                $('#semester').prop('disabled', false);
            });

            $('#semester').change(function() {
                var semesterId = $(this).val();
                var prodiId = $('#prodi').val();
                if (!semesterId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Silahkan pilih semester terlebih dahulu!'
                    });
                    return;
                }
                $('#loading-indicator').show();
                $.ajax({
                    url: '/kelas/' + prodiId + '/get-blocks/' + semesterId,
                    method: 'GET',

                    success: function(response) {
                        $('#daftar-blok').html(response);
                        $('#loading-indicator').hide();
                    },
                    error: function(xhr, status, error) {
                        $('#loading-indicator').hide();
                        console.error(error);
                    }
                });
            });


            $(document).on('click', '.block-item', function() {
                var blockId = $(this).data('block-id');
                $('#loading-indicator').show();
                $.ajax({
                    url: '/kelas/get-block-details/' + blockId,
                    method: 'GET',
                    success: function(response) {

                        $('#detail-blok').html(response);
                        $('#loading-indicator').hide();
                    },
                    error: function(xhr, status, error) {
                        $('#loading-indicator').hide();
                        console.error(error);
                    }
                });
            });
        });
    </script>
@endpush
