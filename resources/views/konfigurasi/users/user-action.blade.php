<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Data User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction" action="{{ $user->id ? route('users.update', $user->id) : route('users.store') }}"
        method="POST">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="userName">NIP/NIK</label>
                        @if (empty($user->id))
                            <input type="text" class="form-control mb-2 mr-sm-2" id="userName"
                                value="{{ $user->username }}" name="username" placeholder="NIP/NIK">
                        @else
                            <input type="text" class="form-control mb-2 mr-sm-2" id="userName"
                                value="{{ $user->username }}" name="username" placeholder="NIP/NIK" readonly>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nama</label>
                        @if (empty($user->id))
                            <input type="text" class="form-control mb-2 mr-sm-2" id="name"
                                value="{{ $user->name }}" name="name" placeholder="Nama">
                        @else
                            <input type="text" class="form-control mb-2 mr-sm-2" id="name"
                                value="{{ $user->name }}" name="name" placeholder="Nama" readonly>
                        @endif
                    </div>
                </div>
                @if (empty($user->id))
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Email</label>
                            <input type="text" class="form-control mb-2 mr-sm-2" id="email"
                                value="{{ $user->email }}" name="email" placeholder="E-Mail">
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Role User</h4>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    @foreach ($getRole as $role)
                                        <div class="col-4 mb-3">
                                            <div class="section-title mt-0">
                                                <input type="checkbox" name="usertypes[]" value="{{ $role->id }}"
                                                    id="{{ $role->id }}"> {{ $role->name }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
            </div>

            @if ($user->id)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Role User</h4>
                            </div>


                            <div class="card-body">
                                <div class="row">
                                    @foreach ($getRole as $role)
                                        <div class="col-4 mb-3">
                                            <div class="section-title mt-0">
                                                <input {{ $user->hasRole($role->id) ? 'checked' : '' }} type="checkbox"
                                                    name="usertypes[]" value="{{ $role->name }}"
                                                    id="{{ $role->id }}"> {{ $role->name }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @endif

        </div>
        <div class="modal-footer bg-whitesmoke br">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        $("#level_akun").on("change", function() {
            if ($(this).val() == 0 || $(this).val() == 2) {
                $("#unit_kerja").css('display', 'none');
                $("input[name='fakultas_id']:checked").prop("checked", false);
            } else {
                $("#unit_kerja").css('display', '');
            }
        });
    });
</script>
