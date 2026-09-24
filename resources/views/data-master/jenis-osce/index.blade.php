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
                @include('components.alert')
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">Daftar Stase OSCE</h4>
                </div>
                <div class="col-3">
                    @if (auth()->user()->can('create bank-soal/soal'))
                        <button type="button" class="btn btn-sm btn-primary tambah-data" style="float:right">Tambah
                            Stase</button>
                        {{-- <a href="{{route('bank-soal.soal.create')}}" class="btn btn-sm btn-primary">Tambah Jenis</a> --}}
                    @endif
                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@push('modal')
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAction">
        <div class="modal-dialog modal-md" role="document">

        </div>
    </div>
@endpush

@push('scripts')
    <!-- js for this page only -->
    <script src="{{ asset('tambahan/vendor/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('tambahan/vendor/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{ $dataTable->scripts() }}


    <script>
        $(document).on('submit', '#formAction', function(e) {
            e.preventDefault();
            submitForm(this);
        });

        $('.tambah-data').on('click', function() {
            $('#loading-indicator').show();
            $('#modalAction').find('.loading-overlay').show();
            
            $.ajax({
                method: 'GET',
                url: `{{ url('data-master/jenis-osce/create') }}`,
                success: function(res) {
                    $('#loading-indicator').hide();
                    $('#modalAction').find('.loading-overlay').hide();
                    $('#modalAction').find('.modal-dialog').html(res);
                    $('#modalAction').modal('show');
                    
                    // Tidak perlu panggil initForm() lagi karena pakai event delegation
                },
                error: function(xhr, status, error) {
                    $('#loading-indicator').hide();
                    $('#modalAction').find('.loading-overlay').hide();
                    console.error('Error loading form:', error);
                    alert('Terjadi kesalahan saat memuat form');
                }
            });
        });

        function submitForm(_form) {
            const formData = new FormData(_form);
            const url = $(_form).attr('action');
            
            console.log('Submitting to:', url); // Debug log
            
            // Clear previous errors
            $(_form).find('.text-danger.text-small').remove();
            $(_form).find('.is-invalid').removeClass('is-invalid');
            
            // Show loading state pada button
            const submitBtn = $(_form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

            $.ajax({
                method: 'POST',
                url: url,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    console.log('Success response:', res); // Debug log
                    
                    $('#modalAction').modal('hide');
                    
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    // Show success message
                    if (res.success) {
                        showToast('success', res.message || 'Data berhasil disimpan');
                    }
                    
                    // Reload DataTable
                    reloadDataTable();
                },
                error: function(res) {
                    console.log('Error response:', res); // Debug log
                    
                    // Reset button state
                    submitBtn.prop('disabled', false).html(originalText);
                    
                    handleFormErrors(_form, res);
                }
            });
        }

        $('#jenisosce-table').on('click', '.action', function() {
            let data = $(this).data()
            let id = data.id
            let jenis = data.jenis

            if (jenis == 'delete') {

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: `/data-master/jenis-osce/` + id,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#modalAction').find('.modal-dialog').html(res)
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                )
                                $('#jenisosce-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {

                                var errorMessage = 'An error occurred while deleting.';
                                if (xhr.responseJSON.message) {
                                    errorMessage += '<br>' + xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Failed!',
                                    errorMessage,
                                    'error'
                                );
                                $('#jenisosce-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }


            if (jenis == 'edit') {
                $.ajax({
                    method: 'get',
                    url: `/data-master/jenis-osce/` + id + `/edit`,
                    success: function(res) {
                        $('#modalAction').find('.modal-dialog').html(res)
                        $('#modalAction').modal('show')
                        $('#kategorisoal-table').DataTable().ajax.reload();
                    }
                })

            }

            if(jenis == 'create-komponen')
            {
                {{-- $('.tambah-data').on('click', function() { --}}
                    $('#loading-indicator').show();
                    $('#modalAction').find('.loading-overlay').show();
                    
                    $.ajax({
                        method: 'GET',
                        url: `/data-master/komponen-nilai-osce/create/`+id,
                        success: function(res) {
                            $('#loading-indicator').hide();
                            $('#modalAction').find('.loading-overlay').hide();
                            $('#modalAction').find('.modal-dialog').html(res);
                            $('#modalAction').modal('show');
                            
                            // Tidak perlu panggil initForm() lagi karena pakai event delegation
                        },
                        error: function(xhr, status, error) {
                            $('#loading-indicator').hide();
                            $('#modalAction').find('.loading-overlay').hide();
                            console.error('Error loading form:', error);
                            alert('Terjadi kesalahan saat memuat form');
                        }
                    });
                {{-- }); --}}
            }

            if (jenis == 'delete-komponen') {

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Delete'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'DELETE',
                            url: `/data-master/komponen-nilai-osce/` + id,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#modalAction').find('.modal-dialog').html(res)
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                )
                                $('#jenisosce-table').DataTable().ajax.reload();
                            },
                            error: function(xhr, status, error) {

                                var errorMessage = 'An error occurred while deleting.';
                                if (xhr.responseJSON.message) {
                                    errorMessage += '<br>' + xhr.responseJSON.message;
                                }
                                Swal.fire(
                                    'Failed!',
                                    errorMessage,
                                    'error'
                                );
                                $('#jenisosce-table').DataTable().ajax.reload();
                            }
                        })

                    }
                })
                return
            }

        })
    </script>
@endpush
