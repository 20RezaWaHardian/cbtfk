<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Data Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction" action="{{ $role->id ? route('roles.update', $role->id) : route('roles.store') }}"
        method="POST">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="roleName">Role Name</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="roleName"
                            value="{{ $role->name }}" name="name" placeholder="Role Name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="guardName">Guard Name</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="guardName"
                            value="{{ $role->guard_name }}" name="guard_name" placeholder="Guard Name">
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
