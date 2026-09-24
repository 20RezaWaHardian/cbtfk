<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Data Permission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form id="formAction"
        action="{{ $permission->id ? route('permissions.update', $permission->id) : route('permissions.store') }}"
        method="POST">
        @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="permissionName">Permission Name</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="permissionName"
                            value="{{ $permission->name }}" name="name" placeholder="Permission Name">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="guardName">Guard Name</label>
                        <input type="text" class="form-control mb-2 mr-sm-2" id="guardName"
                            value="{{ $permission->guard_name }}" name="guard_name" placeholder="Guard Name">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="guardName">Main Permission</label>
                        <select class="form-control" data-toggle="select" id="main_permission" name="main_permission">
                            <option disabled selected>Pilih...</option>
                            @foreach ($main_permission as $item)
                                <option {{ $item->id == $permission->main_permission ? 'selected' : '' }}
                                    value="{{ $item->id }}"> {{ $item->name }}</option>
                            @endforeach
                        </select>
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
