<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\DataTables\PermissionDataTable;
use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use App\Models\Role;


class PermissionController extends Controller
{
    public function __construct(){
        $this->middleware('can:read konfigurasi/permissions');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PermissionDataTable $dataTable)
    {
        if(Gate::allows('read konfigurasi/permissions')){
            return $dataTable->render('konfigurasi.permissions.index');
        }else{
            abort(403,'Anda Tidak Memiliki Akses');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $main_permission = Permission::where('main_permission', Null)->get();
        return view('konfigurasi.permissions.permission-action', ['permission' => new Permission(),'main_permission' => $main_permission]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionRequest $request)
    {
        try{
            $newPermission = Permission::create($request->all());
            $role = Role::findByName('developer');
            $role->givePermissionTo($newPermission->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $permission = Permission::Find($id);
        $main_permission = Permission::where('main_permission', Null)->get();
        return view('konfigurasi.permissions.permission-action',compact('permission','main_permission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        try{
            $permission = Permission::findOrFail($id);

            $permission->name = $request->name;
            $permission->guard_name = $request->guard_name;
            $permission->main_permission = $request->main_permission;
            $permission->save();

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $permission = Permission::find($id);
        $permission->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Delete Data Success'
        ]);
    }
}
