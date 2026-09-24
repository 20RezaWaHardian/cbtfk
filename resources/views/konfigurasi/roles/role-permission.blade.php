<style>
    /* Important part */
    .modal-dialog {
        overflow-y: initial !important
    }

    .modal-body {
        height: 80vh;
        overflow-y: auto;
    }
</style>
<div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Set Persmission</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <form action="{{ route('permisssion.sync', $role->id) }}" method="POST">
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
            @if ($role->id)
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Permissions for {{ $role->name }} </h4>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    @foreach (setPermissions() as $permission)
                                        <div class="col-12 mb-2">
                                            <div class="section-title mt-0 mb-2">
                                                <h6><input type="checkbox"
                                                        {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                                        name="permissions[]" value="{{ $permission->id }}"
                                                        id="permissionKe-{{ $permission->id }}">
                                                    {{ Str::upper($permission->name) }} </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($permission->subPermissions as $subpermission)
                                                        <div class="col-6 mb-2">
                                                            <div class="custom-control custom-checkbox">
                                                                <input
                                                                    {{ in_array($subpermission->id, $rolePermissions) ? 'checked' : '' }}
                                                                    type="checkbox" name="permissions[]"
                                                                    class="custom-control-input"
                                                                    value="{{ $subpermission->id }}"
                                                                    id="subpermissionKe-{{ $subpermission->id }}">
                                                                <label class="custom-control-label"
                                                                    for="subpermissionKe-{{ $subpermission->id }}">{{ $subpermission->name }}</label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
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
