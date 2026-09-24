<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\DataTables\NavigationDataTable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Role;
use App\Models\Navigation;
use App\Models\Permission;

class NavigationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:read konfigurasi/menus');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(NavigationDataTable $dataTable)
    {
        if (Gate::allows('read konfigurasi/menus')) {
            return $dataTable->render('konfigurasi.navigations.index');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menus = Navigation::whereNull('main_menu')->get();
        $subMenus = Navigation::whereNotNull('main_menu')->get();
        return view('konfigurasi.navigations.menu-action', ['navigation' => new Navigation(), 'menus' => $menus, 'subMenus' => $subMenus]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $navigasi = Navigation::create([
                'name' => $request->name,
                'url' => $request->data_url,
                'main_menu' => $request->main_menu,
                'sub_menu' => $request->sub_menu,
                'icon' => $request->icon,
            ]);

            if ($request->main_menu == null) {
                $newPermission = Permission::create(['name' => 'read ' . $navigasi->url]);
                $role = Role::findByName('developer');
                $role->givePermissionTo($newPermission->name);
            } else {

                $getMenus = Navigation::where('id', $request->main_menu)->first();
                $getUrl = 'read ' . $getMenus->url;

                $main_permission = Permission::where('name', $getUrl)->first();

                $default_permission_value = [
                    'main_permission' => $main_permission->id,
                    'guard_name' => 'web',
                    'sort' => 0,
                ];

                $readPermission = Permission::create(array_merge(
                    ['name' => 'read ' . $request->data_url],
                    $default_permission_value
                ));

                $role = Role::findByName('developer');
                $role->givePermissionTo($readPermission->name);

                $createPermission = Permission::create(array_merge(
                    ['name' => 'create ' . $request->data_url],
                    $default_permission_value
                ));

                $role = Role::findByName('developer');
                $role->givePermissionTo($createPermission->name);

                $updatePermission = Permission::create(array_merge(
                    ['name' => 'update ' . $request->data_url],
                    $default_permission_value
                ));

                $role = Role::findByName('developer');
                $role->givePermissionTo($updatePermission->name);

                $deletePermission = Permission::create(array_merge(
                    ['name' => 'delete ' . $request->data_url],
                    $default_permission_value
                ));

                $role = Role::findByName('developer');
                $role->givePermissionTo($deletePermission->name);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Create Data Success');
        } catch (\Exception $e) {
            DB::rollback();
            // dd($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
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
        $navigation = Navigation::Find($id);
        $menus = Navigation::Where('main_menu', Null)->orderBy('name')->get();
        $subMenus = Navigation::whereNotNull('main_menu')->get();
        return view('konfigurasi.navigations.menu-action', compact('navigation', 'menus', 'subMenus'));
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

        try {
            //Find Navigation
            $navigation = Navigation::findOrFail($id);

            if ($navigation->main_menu == null) {
                //Find Permission
                $permission = Permission::where('name', 'read ' . $navigation->url)->first();
                //Update Name Permission
                $permission->name = 'read ' . $request->data_url;
                $permission->save();
            } else {
                $data_permission = Permission::where('name', 'like', '%' . $navigation->url . '%')->get();
                foreach ($data_permission as $dt) {
                    if (stripos($dt->name, 'read') !== false) {
                        $dt->name = 'read ' . $request->data_url;
                    } elseif (stripos($dt->name, 'create') !== false) {
                        $dt->name = 'create ' . $request->data_url;
                    } elseif (stripos($dt->name, 'update') !== false) {
                        $dt->name = 'update ' . $request->data_url;
                    } elseif (stripos($dt->name, 'delete') !== false) {
                        $dt->name = 'delete ' . $request->data_url;
                    }
                    $dt->save();
                }
            }

            //Update Navigation
            $navigation->name = $request->name;
            $navigation->url = $request->data_url;
            $navigation->icon = $request->icon;
            $navigation->main_menu = $request->main_menu;
            $navigation->sub_menu = $request->sub_menu;
            $navigation->save();



            return redirect()->back()->with('success', 'Update Data Success');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Gate::allows('delete konfigurasi/menus')) {
            $menu = Navigation::find($id);

            if ($menu->main_menu === null) {
                $permissionNames = [
                    'read ' . $menu->url,
                ];
            } else {
                $permissionNames = [
                    'read ' . $menu->url,
                    'create ' . $menu->url,
                    'update ' . $menu->url,
                    'delete ' . $menu->url,
                ];
            }

            Permission::whereIn('name', $permissionNames)->delete();
            $menu->delete();

            return redirect()->back()->with('success', 'Delete Data Success');
        } else {
            abort(403, 'Anda Tidak Memiliki Akses');
        }
    }
}
