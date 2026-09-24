<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\RoleRequest;
use App\DataTables\RoleDataTable;
use App\Models\Role;
use App\Models\Navigation;
use App\Models\User;
use DB;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read konfigurasi/roles');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(RoleDataTable $dataTable)
    {
        return $dataTable->render('konfigurasi.roles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('konfigurasi.roles.role-action', ['role' => new Role()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RoleRequest $request)
    {
        Role::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Create Data Success'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::Find($id);

        return view('konfigurasi.roles.role-action', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $role->name = $request->name;
        $role->guard_name = $request->guard_name;
        $role->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Update Data Success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete konfigurasi/roles');

        $role = Role::find($id);
        $role->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Delete Data Success'
        ]);
    }

    public function setPermission($id)
    {
        $role = Role::Find($id);
        $rolePermissions = DB::table('role_has_permissions as a')->where('role_id', $id)->pluck('permission_id')->toArray();
        return view('konfigurasi.roles.role-permission', compact(['role', 'rolePermissions']));
    }

    public function sycPermission(Request $request, $id)
    {

        $role = Role::find($id);
        $permissionIds = array_map('intval', $request->permissions);

        // Sinkronisasi permission dengan role
        $role->syncPermissions($permissionIds);
        // $role->syncPermissions($request->permissions);

        return redirect()->back()->with('success', 'Berhasil');
    }
}
