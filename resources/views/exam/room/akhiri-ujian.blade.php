<div class="col-md-8 col-sm-12">
    {{-- NULL --}}
</div>
<div class="col-md-4 col-sm-12 ">
    <div class="text-end">
        <button class="btn btn-danger" id="akhiriUjian" style="display: none;">Akhiri Ujian</button>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
     // Akhiri Ujian
     akhiriUjian.addEventListener("click", function() {
        var ujianId = '{{ encrypt($ujian->id_ujian) }}';
        var pesertaId = '{{ encrypt($peserta->id_peserta_ujian) }}';

        event.preventDefault();
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Anda yakin ingin mengakhiri ujian?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Akhiri Ujian'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/exam24/finish/' + ujianId + '/participant/' + pesertaId,
                    type: 'GET',
                    success: function(response) {
                        Swal.fire({
                            title: 'Success',
                            text: response.message,
                            icon: 'success',
                        }).then(() => {
                            window.location.href = response.redirect;
                        });
                    },
                    error: function(response) {

                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });

    });
</script>
