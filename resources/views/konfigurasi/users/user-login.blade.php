<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Silahkan Pilih User </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>
    <div class="modal-footer bg-whitesmoke br">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
    </div>
</div>



<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{ $dataTable->scripts() }}
<script>
    $('#login-table').on('click', '.action', function() {
        let data = $(this).data()
        let id = data.id
        let jenis = data.jenis
        // console.log(jenis)

        if (jenis == 'login-as') {
            Swal.fire({
                title: 'Apakah Anda Yakin ?',
                text: "Meninggalkan Halaman Ini!",
                icon: 'info',
                showCancelButton: true,
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fa-solid fa-right-to-bracket"></i> Login',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'GET',
                        url: `/konfigurasi/users/` + id + `/login`,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            $('#modalLogin').modal('hide')
                            Swal.fire(
                                'Login!',
                                'Anda Berhasil Login.',
                                'success'
                            )
                            window.location.href = '/dashboard';
                        },
                        error: function(res) {
                            $('#modalLogin').modal('hide')
                            Swal.fire(
                                'Login!',
                                'Anda Tidak Dapat Login.',
                                'error'
                            )
                        }
                    })

                }
            })
            return
        }
    });
</script>
<script>
    $(document).ready(function() {
        // Set the width of the table (replace '#my-table' with the correct ID selector)
        $('#login-table').css('width', '750px');
    });
</script>
